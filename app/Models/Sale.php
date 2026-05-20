<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'receipt_number',
        'items',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount',
        'total',
        'payment_method',
        'tendered_amount',
        'change',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'items'            => 'array',
            'subtotal'         => 'decimal:2',
            'tax_percent'      => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'discount'         => 'decimal:2',
            'total'            => 'decimal:2',
            'tendered_amount'  => 'decimal:2',
            'change'           => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'POS-' . now()->format('ymd');
        $last = static::where('receipt_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();

        $num = $last ? (int) explode('-', $last->receipt_number)[2] + 1 : 1;
        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
