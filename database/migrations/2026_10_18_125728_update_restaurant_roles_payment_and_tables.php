<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dining_tables', function (Blueprint $table): void {
            $table->unsignedSmallInteger('number')->primary();
        });
        DB::table('dining_tables')->insert(array_map(fn (int $number): array => ['number' => $number], range(1, 200)));
        Schema::table('reservations', function (Blueprint $table): void {
            $table->unsignedSmallInteger('table_number')->nullable()->index();
            $table->foreign('table_number')->references('number')->on('dining_tables')->restrictOnDelete();
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('payment_reported_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->boolean('refund_required')->default(false);
            $table->foreign('table_number')->references('number')->on('dining_tables')->restrictOnDelete();
        });
        foreach ([1 => ['user', 'Khách hàng'], 2 => ['admin', 'Quản lý vận hành'], 3 => ['owner', 'Chủ nhà hàng']] as $id => [$code,$name]) {
            DB::table('roles')->updateOrInsert(['id' => $id], ['code' => $code, 'name' => $name]);
        }
        foreach (['admin' => 'Quản lý vận hành', 'cashier' => 'Thu ngân', 'waiter' => 'Phục vụ', 'chef' => 'Đầu bếp', 'receptionist' => 'Lễ tân', 'delivery' => 'Nhân viên giao hàng', 'warehouse' => 'Quản lý kho'] as $code => $name) {
            DB::table('roles')->updateOrInsert(['code' => $code], ['name' => $name]);
        }
        foreach ([1 => 'Chờ xác nhận', 2 => 'Đang làm', 3 => 'Đang giao', 4 => 'Đã thanh toán', 5 => 'Đã hủy', 6 => 'Đã giao thành công'] as $id => $name) {
            DB::table('order_statuses')->updateOrInsert(['id' => $id], ['name' => $name]);
        }
        DB::table('orders')->where('is_paid', true)->update(['payment_verified_at' => DB::raw('updated_at')]);
        DB::table('orders')->where('payment_method', 'transfer')->where('order_status_id', 4)->update(['order_status_id' => 6]);
    }

    public function down(): void
    {
        DB::table('orders')->where('order_status_id', 6)->update(['order_status_id' => 4]);
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['table_number']);
            $table->dropConstrainedForeignId('payment_verified_by');
            $table->dropColumn(['payment_reported_at', 'payment_verified_at', 'refund_required']);
        });
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['table_number']);
            $table->dropColumn('table_number');
        });
        Schema::dropIfExists('dining_tables');
        DB::table('order_statuses')->where('id', 6)->delete();
    }
};
