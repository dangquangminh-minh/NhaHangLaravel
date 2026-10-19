<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use App\Models\OrderStatus;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([1 => ['user', 'Khách hàng'], 2 => ['admin', 'Quản lý vận hành'], 3 => ['owner', 'Chủ nhà hàng']] as $id => [$code,$name]) {
            Role::query()->updateOrCreate(['id' => $id], ['code' => $code, 'name' => $name]);
        }
        foreach (Role::EMPLOYEE_NAMES as $code => $name) {
            Role::query()->updateOrCreate(['code' => $code], ['name' => $name]);
        }
        foreach ([1 => 'Chờ xác nhận', 2 => 'Đang làm', 3 => 'Đang giao', 4 => 'Đã thanh toán', 5 => 'Đã hủy', 6 => 'Đã giao thành công'] as $id => $name) {
            OrderStatus::query()->updateOrCreate(['id' => $id], ['name' => $name]);
        }
        DiningTable::query()->insertOrIgnore(array_map(fn (int $number): array => ['number' => $number], range(1, 200)));
    }
}
