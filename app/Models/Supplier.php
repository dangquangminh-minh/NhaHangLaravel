<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone'];

    protected function casts(): array
    {
        return [];
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }
}
