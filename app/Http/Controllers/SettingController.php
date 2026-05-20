<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getSettings();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'low_stock_threshold' => 'required|integer|min:0',
            'bill_alert_days' => 'required|integer|min:1',
            'notifications_low_stock' => 'boolean',
            'notifications_bill_due' => 'boolean',
            'notifications_overdue_bill' => 'boolean',
            'notifications_purchase_approval' => 'boolean',
        ]);

        $settings = Setting::first();

        if (!$settings) {
            $settings = new Setting();
        }

        $settings->hotel_name = $validated['hotel_name'];
        $settings->currency = $validated['currency'];
        $settings->low_stock_threshold = $validated['low_stock_threshold'];
        $settings->bill_alert_days = $validated['bill_alert_days'];
        $settings->notifications = [
            'low_stock' => $request->boolean('notifications_low_stock'),
            'bill_due' => $request->boolean('notifications_bill_due'),
            'overdue_bill' => $request->boolean('notifications_overdue_bill'),
            'purchase_approval' => $request->boolean('notifications_purchase_approval'),
        ];

        $settings->save();

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
