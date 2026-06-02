<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    private array $expectedHeaders = [
        'X-Content-Type-Options'  => 'nosniff',
        'X-Frame-Options'         => 'SAMEORIGIN',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'X-XSS-Protection'        => '0',
        'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=()',
    ];

    public function test_security_headers_present_on_home(): void
    {
        $response = $this->get('/');

        foreach ($this->expectedHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    public function test_security_headers_present_on_admin_login(): void
    {
        $response = $this->get($this->adminUrl('login'));

        foreach ($this->expectedHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    public function test_x_frame_options_prevents_clickjacking(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_x_content_type_options_prevents_mime_sniffing(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_referrer_policy_limits_referrer_leakage(): void
    {
        $this->get('/')->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
