<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists() || Category::query()->exists() || Dish::query()->exists()) {
            $this->command?->warn('Database đã có dữ liệu. Bỏ qua seeder demo để không sửa hoặc ghi đè dữ liệu.');

            return;
        }
        DB::transaction(function (): void {
            $this->call(ReferenceDataSeeder::class);
            $owner = User::factory()->owner()->create(['name' => 'Chủ nhà hàng demo', 'email' => 'owner@example.test', 'phone' => '0900000001']);
            $admin = User::factory()->admin()->create(['name' => 'Quản trị viên demo', 'email' => 'admin@example.test', 'phone' => '0900000002']);
            $customer = User::factory()->create(['name' => 'Khách hàng demo', 'email' => 'user@example.test', 'phone' => '0900000003']);
            for ($i = 1; $i <= 12; $i++) {
                User::factory()->admin()->create(['name' => 'Nhân viên demo '.$i, 'email' => 'staff'.$i.'@example.test', 'role_id' => Role::query()->where('code', array_keys(Role::EMPLOYEE_NAMES)[($i - 1) % count(Role::EMPLOYEE_NAMES)])->sole()->id]);
            }
            $customers = collect([$customer]);
            for ($i = 1; $i <= 25; $i++) {
                $customers->push(User::factory()->create(['name' => 'Khách hàng mẫu '.$i, 'email' => 'customer'.$i.'@example.test']));
            }
            $names = ['Món Khai Vị', 'Món Chính', 'Tráng Miệng', 'Đồ Uống', 'Lẩu', 'Nướng', 'Hải Sản', 'Món Chay', 'Món Cơm', 'Mì - Bún', 'Món Gà', 'Món Bò', 'Món Cá', 'Ăn Nhẹ', 'Món Theo Mùa', 'Combo Gia Đình'];
            $categories = collect();
            foreach ($names as $name) {
                $categories->push(Category::factory()->create(['name' => $name, 'description' => 'Danh mục demo '.$name]));
            }
            $suppliers = collect();
            for ($i = 1; $i <= 16; $i++) {
                $suppliers->push(Supplier::factory()->create(['name' => 'Nhà cung cấp demo '.$i, 'address' => 'Địa chỉ mẫu '.$i.', TP. Hồ Chí Minh']));
            }
            $original = [
                ['Gỏi Cuốn Tôm Thịt', 25000, 'goicuon.jpg', 0], ['Cơm Chiên Dương Châu', 55000, 'comchien.jpg', 8],
                ['Lẩu Thái Hải Sản', 180000, 'lauthai.jpg', 4], ['Bò Lúc Lắc', 95000, 'boluclac.jpg', 1],
                ['Kem Dừa', 35000, 'kemdua.jpg', 2], ['Sinh Tố Bơ', 40000, 'sinhtobo.jpg', 3],
                ['Mì Xào Hải Sản', 65000, 'mixaohs.jpg', 9], ['Đậu Hũ Chiên Sả', 30000, 'dauhu.jpg', 7],
                ['Gà Nướng Mật Ong', 120000, 'ga_nuong_mat.jpg', 5], ['Tôm Rang Me', 90000, 'tomrangme.jpg', 6],
            ];
            $dishes = collect();
            for ($i = 0; $i < 48; $i++) {
                $sample = $original[$i % count($original)];
                $file = 'assets/images/'.$sample[2];
                if (! is_file(public_path($file))) {
                    $file = 'assets/images/menu-'.($i % 8 + 1).'.jpg';
                }
                $dish = Dish::factory()->create(['name' => $sample[0].($i >= 10 ? ' - phần demo '.(intdiv($i, 10) + 1) : ''), 'price' => (string) $sample[1], 'description' => 'Dữ liệu minh họa đồ án. Món được chế biến và phục vụ tại nhà hàng.', 'category_id' => $categories[$sample[3]]->id, 'supplier_id' => $suppliers[$i % 16]->id, 'image' => $file]);
                if ($i < 10) {
                    $dish->images()->create(['path' => $file]);
                }
                $dishes->push($dish);
            }
            $offers = collect();
            for ($i = 1; $i <= 16; $i++) {
                $offers->push(Voucher::factory()->create(['name' => 'Ưu đãi demo '.$i, 'discount_amount' => (string) (20000 * $i), 'points_cost' => 200 * $i, 'quantity' => 20 + $i, 'expires_at' => now()->addDays(90)]));
            }
            $statuses = [4, 4, 1, 2, 3, 5];
            for ($i = 0; $i < 36; $i++) {
                $user = $i < 12 ? $customer : $customers[($i - 11) % $customers->count()];
                $dish = $dishes[$i % $dishes->count()];
                $quantity = 2 + $i % 4;
                $amount = Money::decimal(Money::cents($dish->price) * $quantity);
                $status = $statuses[$i % 6];
                if ($status === 4 && $i % 2) {
                    $status = 6;
                }
                $paid = in_array($status, [4, 6], true) || ($i % 2 && in_array($status, [2, 3], true));
                $order = Order::factory()->create(['user_id' => $user->id, 'handled_by' => $status === 1 ? null : $admin->id, 'order_status_id' => $status, 'is_paid' => $paid, 'payment_reported_at' => $i % 2 && $paid ? now()->subDays($i % 12) : null, 'payment_verified_at' => $paid ? now()->subDays($i % 12) : null, 'payment_verified_by' => $paid ? $admin->id : null, 'subtotal' => $amount, 'discount' => '0.00', 'total' => $amount, 'payment_method' => $i % 2 ? 'transfer' : 'cash', 'created_at' => now()->subDays($i % 12), 'updated_at' => now()->subDays($i % 12), 'note' => 'Đơn minh họa, không phải giao dịch thật.']);
                $order->items()->create(['dish_id' => $dish->id, 'dish_name' => $dish->name, 'unit_price' => $dish->price, 'quantity' => $quantity]);
            }
            $offers[0]->decrement('quantity');
            $customer->redemptions()->create(['voucher_id' => $offers[0]->id, 'code' => 'DEMO20K', 'points_spent' => 200, 'discount_amount' => '20000.00', 'expires_at' => $offers[0]->expires_at]);
            Address::factory()->for($customer)->create(['recipient_name' => $customer->name, 'phone' => $customer->phone, 'address' => '123 Đường Mẫu, Phường Mẫu, TP. Hồ Chí Minh']);
            for ($i = 1; $i <= 18; $i++) {
                Reservation::factory()->for($customers[$i % $customers->count()])->create(['table_number' => $i, 'email' => $customers[$i % $customers->count()]->email, 'name' => 'Khách đặt bàn demo '.$i, 'reserved_at' => now()->addDays($i % 7 + 1), 'status' => $i % 3 === 0 ? 'confirmed' : 'pending']);
                Contact::factory()->create(['name' => 'Người gửi demo '.$i, 'subject' => 'Tư vấn đặt tiệc demo '.$i, 'message' => 'Nội dung liên hệ minh họa cho đồ án.']);
                Review::factory()->for($customer)->for($dishes[$i - 1])->create(['rating' => 3 + $i % 3, 'message' => 'Đánh giá minh họa. Món ăn và phục vụ tốt.']);
            }
        });
        $this->command?->info('Đã tạo dữ liệu DEMO. Mật khẩu demo: Demo@12345. Chưa import dữ liệu SQL Server.');
    }
}
