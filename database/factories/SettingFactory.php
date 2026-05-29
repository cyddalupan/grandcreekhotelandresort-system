<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
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
        ];
    }
}
