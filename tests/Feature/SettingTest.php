<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email_verified_at' => now()]);
    }

    // ── Authentication gates ──

    public function test_unauthenticated_users_are_redirected_to_login_for_index(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_for_update(): void
    {
        $this->post(route('settings.update'), [
            'hotel_name' => 'Test',
            'currency' => 'PHP',
            'low_stock_threshold' => 10,
            'bill_alert_days' => 7,
        ])->assertRedirect(route('login'));
    }

    // ── Index page ──

    public function test_settings_index_loads_successfully(): void
    {
        Setting::factory()->create([
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
        ]);

        $response = $this->actingAs($this->user)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Grand Creek Hotel & Resort');
        $response->assertSee('PHP');
    }

    public function test_settings_index_shows_default_values_when_no_settings_exist(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Grand Creek Hotel & Resort');
        $response->assertSee('PHP');
    }

    // ── Updating settings ──

    public function test_settings_can_be_updated(): void
    {
        Setting::factory()->create([
            'hotel_name' => 'Old Name',
            'currency' => 'USD',
            'low_stock_threshold' => 5,
            'bill_alert_days' => 3,
        ]);

        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek Hotel & Resort Updated',
            'currency' => 'PHP',
            'low_stock_threshold' => 20,
            'bill_alert_days' => 7,
            'notifications_low_stock' => true,
            'notifications_bill_due' => true,
            'notifications_overdue_bill' => false,
            'notifications_purchase_approval' => true,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'hotel_name' => 'Grand Creek Hotel & Resort Updated',
            'currency' => 'PHP',
            'low_stock_threshold' => 20,
            'bill_alert_days' => 7,
        ]);
    }

    public function test_settings_creates_new_when_none_exist(): void
    {
        $this->assertDatabaseCount('settings', 0);

        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek Hotel & Resort',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
            'notifications_low_stock' => true,
            'notifications_bill_due' => true,
            'notifications_overdue_bill' => true,
            'notifications_purchase_approval' => true,
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseHas('settings', [
            'hotel_name' => 'Grand Creek Hotel & Resort',
        ]);
    }

    public function test_settings_update_persists_notification_toggles(): void
    {
        Setting::factory()->create();

        $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => 15,
            'bill_alert_days' => 5,
            'notifications_low_stock' => false,
            'notifications_bill_due' => true,
            'notifications_overdue_bill' => false,
            'notifications_purchase_approval' => true,
        ]);

        $setting = Setting::first();

        $this->assertFalse($setting->notifications['low_stock']);
        $this->assertTrue($setting->notifications['bill_due']);
        $this->assertFalse($setting->notifications['overdue_bill']);
        $this->assertTrue($setting->notifications['purchase_approval']);
    }

    public function test_settings_update_defaults_notifications_to_false_when_unchecked(): void
    {
        Setting::factory()->create();

        $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => 15,
            'bill_alert_days' => 5,
            // All notifications intentionally omitted (unchecked)
        ]);

        $setting = Setting::first();

        $this->assertFalse($setting->notifications['low_stock']);
        $this->assertFalse($setting->notifications['bill_due']);
        $this->assertFalse($setting->notifications['overdue_bill']);
        $this->assertFalse($setting->notifications['purchase_approval']);
    }

    // ── Validation ──

    public function test_settings_validation_requires_hotel_name(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => '',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
        ]);

        $response->assertSessionHasErrors('hotel_name');
    }

    public function test_settings_validation_requires_currency(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => '',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 7,
        ]);

        $response->assertSessionHasErrors('currency');
    }

    public function test_settings_validation_requires_low_stock_threshold(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => '',
            'bill_alert_days' => 7,
        ]);

        $response->assertSessionHasErrors('low_stock_threshold');
    }

    public function test_settings_validation_rejects_negative_threshold(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => -5,
            'bill_alert_days' => 7,
        ]);

        $response->assertSessionHasErrors('low_stock_threshold');
    }

    public function test_settings_validation_requires_bill_alert_days(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => '',
        ]);

        $response->assertSessionHasErrors('bill_alert_days');
    }

    public function test_settings_validation_rejects_zero_bill_alert_days(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => 30,
            'bill_alert_days' => 0,
        ]);

        $response->assertSessionHasErrors('bill_alert_days');
    }

    // ── Success message ──

    public function test_settings_update_shows_success_message(): void
    {
        $this->actingAs($this->user)->post(route('settings.update'), [
            'hotel_name' => 'Grand Creek',
            'currency' => 'PHP',
            'low_stock_threshold' => 20,
            'bill_alert_days' => 5,
            'notifications_low_stock' => true,
            'notifications_bill_due' => true,
            'notifications_overdue_bill' => true,
            'notifications_purchase_approval' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('settings.index'));
        $response->assertSee('Settings updated successfully.');
    }
}
