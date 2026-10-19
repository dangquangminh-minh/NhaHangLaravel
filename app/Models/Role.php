<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const EMPLOYEE_NAMES = ['admin' => 'Quản lý vận hành', 'cashier' => 'Thu ngân', 'waiter' => 'Phục vụ', 'chef' => 'Đầu bếp', 'receptionist' => 'Lễ tân', 'delivery' => 'Nhân viên giao hàng', 'warehouse' => 'Quản lý kho'];

    protected $fillable = ['code', 'name'];

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
