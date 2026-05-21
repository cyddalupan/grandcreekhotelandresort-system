<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_name', 'currency', 'low_stock_threshold',
        'bill_alert_days', 'notifications',
    ];

    protected function casts(): array
    {
        return [
            'notifications' => 'array',
            'low_stock_threshold' => 'integer',
            'bill_alert_days' => 'integer',
        ];
    }

    public static function getSettings()
    {
        return static::first() ?? new static([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
            'notifications' => [
                'low_stock' => true,
                'bill_due' => true,
                'overdue_bill' => true,
                'purchase_approval' => true,
            ],
        ]);
    }
}
