<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
