<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\Concerns\UsesMongoDatabase;
use Tests\TestCase;

class ContactAndPostTest extends TestCase
{
    use UsesMongoDatabase;

    public function test_user_can_submit_contact_form(): void
    {
        $response = $this->post('/contact', [
            'name'    => 'Trần Thị B',
            'email'   => 'tranthib@gmail.com',
            'phone'   => '0912345678',
            'message' => 'Cần tư vấn mẫu hoa cưới phong cách hiện đại.',
        ]);

        $response->assertRedirect('/contact');
        $this->assertDatabaseHas('contacts', ['email' => 'tranthib@gmail.com'], 'mongodb');

        // Ô "name" trên form lưu vào field full_name; mã CT001 và status pending do model tự gán
        $this->assertDatabaseHas('contacts', [
            'code'      => 'CT001',
            'full_name' => 'Trần Thị B',
            'phone'     => '0912345678',
            'message'   => 'Cần tư vấn mẫu hoa cưới phong cách hiện đại.',
            'status'    => 'pending',
        ], 'mongodb');
    }

    public function test_contact_success_message_is_shown_and_codes_increase(): void
    {
        $data = ['name' => 'Trần Thị B', 'email' => 'tranthib@gmail.com', 'phone' => '0912345678', 'message' => 'Nội dung'];

        $this->post('/contact', $data);
        $this->followingRedirects()->post('/contact', $data)
            ->assertSee('Gửi liên hệ thành công! Cửa hàng sẽ liên hệ lại sớm nhất.');

        $this->assertDatabaseHas('contacts', ['code' => 'CT002'], 'mongodb');
    }

    public function test_contact_page_shows_form_and_google_map(): void
    {
        $this->get('/contact')
            ->assertStatus(200)
            ->assertSee('Liên hệ với chúng tôi')
            ->assertSee('https://www.google.com/maps/embed', false);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from('/contact')->post('/contact', [])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['name', 'email', 'phone', 'message']);

        $this->assertDatabaseCount('contacts', 0, 'mongodb');
    }

    public function test_contact_form_rejects_invalid_email_and_phone(): void
    {
        $this->from('/contact')->post('/contact', [
            'name'    => 'Trần Thị B',
            'email'   => 'khong-phai-email',
            'phone'   => '12345',
            'message' => 'Nội dung',
        ])->assertSessionHasErrors(['email', 'phone']);

        $this->assertDatabaseCount('contacts', 0, 'mongodb');
    }

    public function test_guest_can_view_post_list_and_detail(): void
    {
        $post = Post::create([
            'title'        => 'Ý nghĩa hoa hồng',
            'summary'      => 'Tóm tắt ý nghĩa các màu hoa hồng.',
            'content'      => '<p>Nội dung <strong>bài viết</strong></p>',
            'published_at' => '2026-10-20',
        ]);

        $this->get('/posts')
            ->assertStatus(200)
            ->assertSee('Ý nghĩa hoa hồng')
            ->assertSee('20/10/2026');

        $this->get('/posts/' . $post->slug)
            ->assertStatus(200)
            ->assertSee('Ý nghĩa hoa hồng')
            ->assertSee('<strong>bài viết</strong>', false);
    }

    public function test_post_list_is_paginated_10_per_page(): void
    {
        foreach (range(1, 12) as $i) {
            Post::create([
                'title'        => "Bài viết số {$i}",
                'content'      => '<p>Nội dung</p>',
                'published_at' => now()->subDays($i),
            ]);
        }

        $this->get('/posts')
            ->assertStatus(200)
            ->assertSee('Bài viết số 1')
            ->assertSee('page=2', false);

        $this->get('/posts?page=2')
            ->assertStatus(200)
            ->assertSee('Bài viết số 11')
            ->assertSee('Bài viết số 12');
    }

    public function test_unknown_post_returns_404(): void
    {
        $this->get('/posts/khong-ton-tai')->assertStatus(404);
    }
}
