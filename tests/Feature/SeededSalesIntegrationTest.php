<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Seeding\SafeFlowerImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\UsesDungMongoDatabase;
use Tests\TestCase;

class SeededSalesIntegrationTest extends TestCase
{
    use UsesDungMongoDatabase;

    private function token(): string
    {
        $response = $this->get('/checkout')->assertOk();
        preg_match('/name="checkout_token" value="([^"]+)"/', $response->getContent(), $matches);
        $this->assertTrue(Str::isUuid($matches[1] ?? ''));

        return $matches[1];
    }

    private function delivery(string $token): array
    {
        return ['checkout_token' => $token, 'receiver_name' => 'Khách QA', 'receiver_phone' => '0901234567',
            'receiver_address' => 'Địa chỉ QA giả lập', 'payment_method' => 'COD'];
    }

    private function fingerprints(): array
    {
        $db = DB::connection('mongodb')->getDatabase();
        $hashes = [];
        foreach (['users', 'products', 'categories', 'posts', 'orders', 'carts'] as $name) {
            $rows = $db->selectCollection($name)->find([], ['sort' => ['_id' => 1]])->toArray();
            $hashes[$name] = hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
        }

        return $hashes;
    }

    public function test_seeded_products_work_with_existing_auth_cart_checkout_and_admin_without_overwriting_business_data(): void
    {
        $target = config('database.connections.mongodb.database');
        $passwords = ['User' => bin2hex(random_bytes(16)), 'Admin' => bin2hex(random_bytes(16))];
        $importer = app(SafeFlowerImporter::class);
        $importer->run($target, true, $passwords);
        $buyer = User::where('email', 'user.dung@example.test')->firstOrFail();
        $admin = User::where('email', 'admin.dung@example.test')->firstOrFail();
        $product = Product::where('code', 'PVN297')->firstOrFail();
        $stock = $product->stock;

        $this->get('/cart')->assertRedirect('/login');
        $this->post('/login', ['email' => $buyer->email, 'password' => $passwords['User']])->assertRedirect();
        $this->assertAuthenticatedAs($buyer);
        $this->get('/admin/orders')->assertForbidden();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect('/cart');
        $this->patch('/cart/items/'.$product->code, ['quantity' => 3])->assertRedirect('/cart');
        $this->get('/cart')->assertOk()->assertSee($product->name);
        $this->assertSame($stock, $product->fresh()->stock);

        $payload = $this->delivery($this->token());
        $this->post('/checkout', $payload + ['price' => 1, 'total' => 1])->assertRedirect('/orders/success');
        $order = Order::firstOrFail();
        $this->assertSame(3 * $product->price, $order->total_amount);
        $this->assertSame('Pending', $order->status);
        $this->assertSame($stock - 3, $product->fresh()->stock);
        $this->get('/orders/success')->assertOk()->assertSee($order->order_code);
        $this->get('/account/orders')->assertOk()->assertSee($order->order_code);
        $this->get('/account/orders/'.$order->id)->assertOk();

        $this->post('/checkout', $payload)->assertRedirect('/orders/success');
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertRedirect('/cart');
        $this->post('/checkout', $payload)->assertRedirect('/orders/success');
        $this->assertSame(1, Order::count());
        $this->assertSame(1, $buyer->cart->items[0]['quantity']);
        $this->assertSame($stock - 3, $product->fresh()->stock);

        $other = User::factory()->create();
        $this->actingAs($other)->get('/account/orders/'.$order->id)->assertNotFound();
        $this->get('/admin/orders')->assertForbidden();
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', ['email' => $admin->email, 'password' => $passwords['Admin']])->assertRedirect();
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/orders?q='.$order->order_code)->assertOk()->assertSee($order->order_code);
        $this->patch('/admin/orders/'.$order->id.'/status', ['status' => 'Completed'])->assertSessionHasErrors('status');
        $this->assertSame('Pending', $order->fresh()->status);
        foreach (['Processing', 'Shipped', 'Completed'] as $status) {
            $this->patch('/admin/orders/'.$order->id.'/status', ['status' => $status])->assertRedirect();
            $this->assertSame($status, $order->fresh()->status);
        }
        $this->patch('/admin/orders/'.$order->id.'/status', ['status' => 'Cancelled'])->assertSessionHasErrors('status');
        $this->assertSame($stock - 3, $product->fresh()->stock);

        $this->actingAs($buyer);
        $this->post('/checkout', $this->delivery($this->token()))->assertRedirect('/orders/success');
        $cancelled = Order::where('order_code', '!=', $order->order_code)->firstOrFail();
        $this->assertSame($stock - 4, $product->fresh()->stock);
        $this->actingAs($admin)->patch('/admin/orders/'.$cancelled->id.'/status', ['status' => 'Cancelled'])->assertRedirect();
        $this->patch('/admin/orders/'.$cancelled->id.'/status', ['status' => 'Cancelled'])->assertSessionHasErrors('status');
        $this->assertSame($stock - 3, $product->fresh()->stock);

        $this->actingAs($buyer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertRedirect('/cart');
        $before = $this->fingerprints();
        $reimport = $importer->run($target, true, $passwords);
        $after = $this->fingerprints();
        $this->assertSame($before, $after);
        $this->assertSame(80, $reimport['collections']['products']['skipped']);

        $evidence = ['database' => $target, 'method' => 'Laravel Feature HTTP requests + real MongoDB; no browser/catalog test',
            'counts' => ['products' => Product::count(), 'orders' => Order::count(), 'users' => User::count()],
            'stock' => ['initial' => $stock, 'after_completed_and_cancelled' => $product->fresh()->stock],
            'order_statuses' => Order::orderBy('status')->pluck('status')->all(),
            'before_reimport_sha256' => $before, 'after_reimport_sha256' => $after, 'unchanged' => $before === $after,
            'reimport' => $reimport];
        $directory = base_path('.runtime/dung-qa');
        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }
        file_put_contents($directory.'/business-readback.json', json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
    }

    public function test_untrusted_delivery_text_is_escaped_in_order_html(): void
    {
        $target = config('database.connections.mongodb.database');
        app(SafeFlowerImporter::class)->run($target, true);
        $buyer = User::factory()->create();
        $product = Product::where('code', 'PVN297')->firstOrFail();
        $this->actingAs($buyer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertRedirect('/cart');
        $payload = $this->delivery($this->token());
        $payload['receiver_name'] = '<script>alert(1)</script>';
        $this->post('/checkout', $payload)->assertRedirect('/orders/success');
        $this->get('/orders/success')->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
