<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'reserved_at', 'guests', 'note', 'table_number'];

    protected function casts(): array
    {
        return ['reserved_at' => 'datetime', 'guests' => 'integer'];
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_number', 'number');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
