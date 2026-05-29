<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_creates_with_hotel_name()
    {
        $setting = Setting::factory()->create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
        ]);

        $this->assertEquals('Grand Creek Hotel & Resort', $setting->hotel_name);
    }

    public function test_setting_has_currency_and_threshold_defaults()
    {
        $setting = Setting::factory()->create();

        $this->assertEquals('PHP', $setting->currency);
        $this->assertEquals(30, $setting->low_stock_threshold);
        $this->assertEquals(7, $setting->bill_alert_days);
    }

    public function test_setting_has_notifications_as_array()
    {
        $setting = Setting::factory()->create();

        $this->assertIsArray($setting->notifications);
        $this->assertTrue($setting->notifications['low_stock']);
        $this->assertTrue($setting->notifications['bill_due']);
    }

    public function test_multiple_settings_can_exist()
    {
        Setting::factory()->create(['hotel_name' => 'Setting 1']);
        Setting::factory()->create(['hotel_name' => 'Setting 2']);

        $this->assertCount(2, Setting::all());
    }

    public function test_get_settings_returns_first_or_default()
    {
        $setting = Setting::factory()->create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
        ]);

        $result = Setting::getSettings();
        $this->assertEquals('Grand Creek Hotel & Resort', $result->hotel_name);
        $this->assertEquals('PHP', $result->currency);
    }

    public function test_settings_can_update_values()
    {
        $setting = Setting::factory()->create(['hotel_name' => 'Old Name']);
        $setting->update(['hotel_name' => 'New Resort Name']);
        $setting->refresh();

        $this->assertEquals('New Resort Name', $setting->hotel_name);
    }
}
