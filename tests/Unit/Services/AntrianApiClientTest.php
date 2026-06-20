<?php

namespace Tests\Unit\Services;

use App\Services\AntrianApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AntrianApiClientTest extends TestCase
{
    public function test_fetch_mengembalikan_null_jika_base_url_kosong(): void
    {
        $this->assertNull((new AntrianApiClient)->fetch(null, 5));
    }

    public function test_fetch_menyambung_url_dengan_pola_api_public_nomor(): void
    {
        Http::fake([
            'antrian.example.com/api/public/5' => Http::response([
                'id' => '12',
                'nama_poli' => 'Poli Umum',
                'nama_dokter' => 'dr. Test',
                'status' => 'BERLANGSUNG',
            ]),
        ]);

        $result = (new AntrianApiClient)->fetch('https://antrian.example.com', 5);

        $this->assertSame('12', $result['id']);
        $this->assertSame('Poli Umum', $result['nama_poli']);
        $this->assertSame('BERLANGSUNG', $result['status']);

        Http::assertSent(fn ($request) => $request->url() === 'https://antrian.example.com/api/public/5');
    }

    public function test_fetch_mengembalikan_null_jika_respons_gagal(): void
    {
        Http::fake([
            'antrian.example.com/*' => Http::response(null, 500),
        ]);

        $this->assertNull((new AntrianApiClient)->fetch('https://antrian.example.com', 5));
    }

    public function test_fetch_membersihkan_trailing_slash_pada_base_url(): void
    {
        Http::fake([
            'antrian.example.com/api/public/7' => Http::response(['id' => '1', 'status' => 'OK']),
        ]);

        (new AntrianApiClient)->fetch('https://antrian.example.com/', 7);

        Http::assertSent(fn ($request) => $request->url() === 'https://antrian.example.com/api/public/7');
    }
}
