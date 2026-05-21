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

    public function test_setting_has_address()
    {
        $setting = Setting::factory()->create([
            'address' => '123 Beach Road, Puerto Princesa',
        ]);

        $this->assertEquals('123 Beach Road, Puerto Princesa', $setting->address);
    }

    public function test_setting_has_contact_info()
    {
        $setting = Setting::factory()->create([
            'contact_email' => 'info@grandcreek.com',
            'contact_phone' => '+63 912 345 6789',
        ]);

        $this->assertEquals('info@grandcreek.com', $setting->contact_email);
        $this->assertEquals('+63 912 345 6789', $setting->contact_phone);
    }

    public function test_multiple_settings_can_exist()
    {
        Setting::factory()->create(['hotel_name' => 'Setting 1']);
        Setting::factory()->create(['hotel_name' => 'Setting 2']);

        $this->assertCount(2, Setting::all());
    }
}
