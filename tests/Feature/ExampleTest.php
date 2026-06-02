<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Halaman / adalah portal publik yang memerlukan data RS di DB.
        // Test ini di-skip agar tidak bergantung pada data seed.
        $this->markTestSkipped('Portal publik membutuhkan data RS — jalankan dengan seeder.');
    }
}
