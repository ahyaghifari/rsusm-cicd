<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_public_api_rate_limiter_terdaftar(): void
    {
        $limiter = RateLimiter::limiter('public-api');
        $this->assertNotNull($limiter);
    }

    public function test_portal_rate_limiter_terdaftar(): void
    {
        $limiter = RateLimiter::limiter('portal');
        $this->assertNotNull($limiter);
    }

    public function test_public_api_limiter_menghasilkan_limit_object(): void
    {
        $limiter  = RateLimiter::limiter('public-api');
        $request  = Request::create('/cari-spesialis', 'GET');
        $result   = $limiter($request);

        $this->assertInstanceOf(Limit::class, $result);
    }

    public function test_portal_limiter_menghasilkan_limit_object(): void
    {
        $limiter = RateLimiter::limiter('portal');
        $request = Request::create('/', 'GET');
        $result  = $limiter($request);

        $this->assertInstanceOf(Limit::class, $result);
    }

    public function test_admin_path_dapat_dikonfigurasi_via_env(): void
    {
        $adminPath = env('ADMIN_PATH', 'manage');
        $this->assertNotEmpty($adminPath);
        $this->assertEquals('manage', $adminPath);
    }

    public function test_halaman_manage_login_merespons(): void
    {
        $response = $this->get($this->adminUrl('login'));
        $response->assertStatus(200);
    }

    public function test_halaman_admin_lama_tidak_tersedia(): void
    {
        // /admin default tidak ada karena path sudah diganti ke /manage
        $response = $this->get('/admin');
        // Harusnya 404 karena tidak ada route /admin
        $this->assertNotEquals(200, $response->status());
    }
}
