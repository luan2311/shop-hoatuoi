<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Quản trị hệ thống']);
        $userRole  = Role::create(['name' => 'User',  'description' => 'Khách hàng']);

        // 2. Seed Admin user
        $admin = User::create([
            'name'     => 'Quản Trị Viên',
            'email'    => 'admin@shophoatuoi.com',
            'password' => Hash::make('Admin@123'),
        ]);
        $admin->roles()->attach($adminRole->id);

        // 3. Seed User thường
        $user = User::create([
            'name'     => 'Khách Hàng Demo',
            'email'    => 'user@shophoatuoi.com',
            'password' => Hash::make('User@123'),
        ]);
        $user->roles()->attach($userRole->id);

        // 4. Seed Categories (từ LOAIHANG trong SQL)
        $categories = [
            ['name' => 'Bó Hoa Tươi',          'slug' => 'bo-hoa-tuoi'],
            ['name' => 'Kệ Chúc Mừng',          'slug' => 'ke-chuc-mung'],
            ['name' => 'Giỏ Hoa Chúc Mừng',     'slug' => 'gio-hoa-chuc-mung'],
            ['name' => 'Hoa Cưới',               'slug' => 'hoa-cuoi'],
            ['name' => 'Hoa Sáp',                'slug' => 'hoa-sap'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 5. Seed sản phẩm mẫu (10 sp đại diện từ SQL)
        $boHoa = Category::where('slug', 'bo-hoa-tuoi')->first();
        $ke    = Category::where('slug', 'ke-chuc-mung')->first();

        $products = [
            [
                'code'        => 'PVN297',
                'name'        => 'Bó Hoa Hồng Pastel & Pingpong Thỏ Bông – Dễ Thương & Ngọt Ngào',
                'price'       => 650000,
                'image'       => 'kt2a5767-1748932933010.jpg',
                'description' => 'Bó hoa kết từ hồng pastel nhẹ nhàng, phối cùng baby trắng, cẩm chướng hồng phớt và lá bạc eucalyptus. Điểm nhấn là thỏ bông nhỏ xinh làm từ hoa pingpong.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 10,
                'slug'        => 'bo-hoa-hong-pastel-pingpong-tho-bong-pvn297',
                'category_id' => $boHoa->id,
            ],
            [
                'code'        => 'PVN295',
                'name'        => 'Bó Hoa Hướng Dương – Rực Rỡ & Lạc Quan Ngày Tốt Nghiệp',
                'price'       => 400000,
                'image'       => 'kt2a5725-1748932784453.jpg',
                'description' => 'Bó hoa kết từ hướng dương rực nắng, biểu tượng của niềm tin, sức sống và khát vọng vươn lên.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 12,
                'slug'        => 'bo-hoa-huong-duong-tot-nghiep-pvn295',
                'category_id' => $boHoa->id,
            ],
            [
                'code'        => 'PVN296',
                'name'        => 'Bó Hoa Hồng Đỏ – Sang Trọng & Nồng Nàn',
                'price'       => 800000,
                'image'       => 'kt2a5763-1748932797773.jpg',
                'description' => 'Bó hoa kết từ hoa hồng đỏ rực rỡ, kết hợp lá bạc eucalyptus thanh lịch.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 15,
                'slug'        => 'bo-hoa-hong-do-sang-trong-nong-nan-pvn296',
                'category_id' => $boHoa->id,
            ],
            [
                'code'        => 'PVN291',
                'name'        => 'Mộng Mơ Pastel – Bó Hoa Hồng & Đồng Tiền, Cát Tường Siêu To',
                'price'       => 1200000,
                'image'       => 'z6627827948270-77c577f191bca0887273fd45355e5fd5-1747906523997.jpg',
                'description' => 'Tone hồng pastel – trắng kem – xanh bạc hà. Hoa chính: Hồng pastel, hồng phấn, hoa đồng tiền, cát tường, lá bạc.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 12,
                'slug'        => 'mong-mo-pastel-bo-hoa-hong-dong-tien-cat-tuong-pvn291',
                'category_id' => $boHoa->id,
            ],
            [
                'code'        => 'PVN283',
                'name'        => 'Bó Tulip Trắng – Hồng Pastel Tinh Khôi & Lãng Mạn',
                'price'       => 1700000,
                'image'       => 'intagram11.jpg',
                'description' => 'Kết hợp tulip trắng và tulip hồng pastel, tượng trưng cho sự thuần khiết và tình cảm dịu dàng.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 12,
                'slug'        => 'bo-tulip-trang-hong-pastel-pvn283',
                'category_id' => $boHoa->id,
            ],
            [
                'code'        => 'H63',
                'name'        => 'Kệ Hoa Khai Trương/Chúc Mừng Mini SP000091',
                'price'       => 1100000,
                'image'       => '9f919d1b-c620-44eb-8a81-9ede8e0ece25-d158250d-fb63-4621-9dfa-3f935779ad36.jpg',
                'description' => 'Kệ hoa chúc mừng khai trương mini, sang trọng và đẹp mắt.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 10,
                'slug'        => 'ke-hoa-khai-truong-chuc-mung-mini-h63',
                'category_id' => $ke->id,
            ],
            [
                'code'        => 'H53',
                'name'        => 'Kệ Hoa Khai Trương/Chúc Mừng SP000102',
                'price'       => 2300000,
                'image'       => '1800-2-1722653563019.jpg',
                'description' => 'Kệ hoa khai trương sang trọng, phù hợp cho các dịp khai trương, chúc mừng lớn.',
                'status'      => 'Còn hàng',
                'brand'       => 'Blossom',
                'stock'       => 10,
                'slug'        => 'ke-hoa-khai-truong-chuc-mung-sp000102-h53',
                'category_id' => $ke->id,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // 6. Seed Posts mẫu
        Post::create([
            'title'        => 'Ý nghĩa của hoa hồng trong tình yêu và cuộc sống',
            'slug'         => 'y-nghia-hoa-hong-tinh-yeu-' . time(),
            'thumbnail'    => null,
            'content'      => 'Hoa hồng từ lâu đã là biểu tượng của tình yêu, vẻ đẹp và sự thanh lịch...',
            'published_at' => now()->toDateString(),
        ]);

        Post::create([
            'title'        => 'Cách chọn hoa phù hợp cho từng dịp',
            'slug'         => 'cach-chon-hoa-phu-hop-tung-dip-' . (time() + 1),
            'thumbnail'    => null,
            'content'      => 'Mỗi loài hoa mang một thông điệp riêng. Hướng dương tượng trưng cho sự lạc quan...',
            'published_at' => now()->toDateString(),
        ]);
    }
}
