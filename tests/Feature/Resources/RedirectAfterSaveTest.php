<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\GambarRawatInapResource\Pages\CreateGambarRawatInap;
use App\Filament\Resources\GambarRawatInapResource\Pages\EditGambarRawatInap;
use App\Filament\Resources\HalamanResource\Pages\CreateHalaman;
use App\Filament\Resources\HalamanResource\Pages\EditHalaman;
use App\Filament\Resources\RawatInapResource\Pages\CreateRawatInap;
use App\Filament\Resources\RawatInapResource\Pages\EditRawatInap;
use App\Filament\Resources\DokterResource\Pages\CreateDokter;
use App\Filament\Resources\DokterResource\Pages\EditDokter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectAfterSaveTest extends TestCase
{
    use RefreshDatabase;

    private function getUrl(object $page): string
    {
        return (new \ReflectionMethod($page, 'getRedirectUrl'))
            ->invoke($page);
    }

    public function test_create_dokter_redirect_ke_index(): void
    {
        $page = new CreateDokter;
        $this->assertStringContainsString('dokters', $this->getUrl($page));
    }

    public function test_edit_dokter_redirect_ke_index(): void
    {
        $page = new EditDokter;
        $this->assertStringContainsString('dokters', $this->getUrl($page));
    }

    public function test_create_rawat_inap_redirect_ke_index(): void
    {
        $page = new CreateRawatInap;
        $this->assertStringContainsString('rawat-inap', $this->getUrl($page));
    }

    public function test_edit_rawat_inap_redirect_ke_index(): void
    {
        $page = new EditRawatInap;
        $this->assertStringContainsString('rawat-inap', $this->getUrl($page));
    }

    public function test_create_gambar_rawat_inap_redirect_ke_index(): void
    {
        $page = new CreateGambarRawatInap;
        $this->assertStringContainsString('gambar-rawat-inap', $this->getUrl($page));
    }

    public function test_edit_gambar_rawat_inap_redirect_ke_index(): void
    {
        $page = new EditGambarRawatInap;
        $this->assertStringContainsString('gambar-rawat-inap', $this->getUrl($page));
    }

    public function test_create_halaman_redirect_ke_index(): void
    {
        $page = new CreateHalaman;
        $this->assertStringContainsString('halamen', $this->getUrl($page));
    }

    public function test_edit_halaman_redirect_ke_index(): void
    {
        $page = new EditHalaman;
        $this->assertStringContainsString('halamen', $this->getUrl($page));
    }
}
