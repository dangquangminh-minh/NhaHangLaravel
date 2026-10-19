<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    use HasFactory;

    protected $primaryKey = 'number';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['number'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_number', 'number');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_number', 'number');
    }
}
