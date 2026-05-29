<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Juan Dela Cruz', $user->name);
        $this->assertEquals('juan@example.com', $user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_password_is_hashed_when_created(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text-password',
        ]);

        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue(Hash::check('plain-text-password', $user->password));
    }

    public function test_user_is_verified_when_created_with_default_factory(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->email_verified_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_can_be_unverified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    public function test_user_has_fillable_attributes(): void
    {
        $user = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
    }
}
