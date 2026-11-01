<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Traits\ApiResponse;
use PHPUnit\Framework\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_login_request_class_exists(): void
    {
        $this->assertTrue(class_exists(LoginRequest::class));
    }

    public function test_api_response_trait_exists(): void
    {
        $this->assertTrue(trait_exists(ApiResponse::class));
    }

    public function test_login_email_and_password_are_required(): void
    {
        $rules = (new LoginRequest)->rules();

        $this->assertContains('required', $rules['email']);
        $this->assertContains('required', $rules['password']);
        $this->assertStringNotContainsString('unique:users,email', implode('|', $rules['email']));
    }
}
