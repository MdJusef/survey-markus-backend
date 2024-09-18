<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'EMPLOYEE',
            'email' => 'emplopyee123@gmail.com',
            'password' => bcrypt('1234567rr'),
            'role_type' => 'EMPLOYEE',
            'email_verified_at' => now(),
            'otp' => 0,
        ]);

        // Send a login request with correct credentials
        $response = $this->postJson('/api/login', [
            'email' => 'emplopyee123@gmail.com',
            'password' => '1234567rr',
        ]);

        // Assert the response status and token presence
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'EMPLOYEE 12',
            'email' => 'emplopyee124@gmail.com',
            'password' => bcrypt('1234567rr'),
            'role_type' => 'EMPLOYEE',
            'email_verified_at' => now(),
            'otp' => 0,
        ]);

        // Send a login request with incorrect password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert the response status and error message
        $response->assertStatus(402);
        $response->assertJson(['message' => 'Your credential is wrong']);
    }

    public function test_login_fails_with_missing_fields()
    {
        // Send a login request without email and password
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        // Assert the response status and validation errors
        $response->assertStatus(400);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_email_format()
    {
        // Send a login request with invalid email format
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        // Assert the response status and validation errors
        $response->assertStatus(400);
        $response->assertJsonValidationErrors(['email']);
    }
}
