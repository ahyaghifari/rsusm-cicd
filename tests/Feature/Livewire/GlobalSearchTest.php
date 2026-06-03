<?php

namespace Tests\Feature\Livewire;

use App\Livewire\GlobalSearch;
use App\Models\Dokter;
use App\Models\Faq;
use App\Models\Halaman;
use App\Models\Promo;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rs = RumahSakit::factory()->create(['aktif' => true]);
        app()->instance('currentRumahSakit', $this->rs);
    }

    // ── Rendering & state awal ────────────────────────────────────────────────

    public function test_renders_without_error(): void
    {
        Livewire::test(GlobalSearch::class)->assertOk();
    }

    public function test_initial_state_not_open(): void
    {
        Livewire::test(GlobalSearch::class)
            ->assertSet('isOpen', false)
            ->assertSet('query', '');
    }

    public function test_open_event_sets_is_open(): void
    {
        Livewire::test(GlobalSearch::class)
            ->dispatch('open-search')
            ->assertSet('isOpen', true)
            ->assertSet('query', '');
    }

    public function test_close_resets_state(): void
    {
        Livewire::test(GlobalSearch::class)
            ->dispatch('open-search')
            ->set('query', 'dokter')
            ->call('close')
            ->assertSet('isOpen', false)
            ->assertSet('query', '');
    }

    // ── Minimum query length ──────────────────────────────────────────────────

    public function test_query_1_char_searched_is_false(): void
    {
        $component = Livewire::test(GlobalSearch::class)->set('query', 'a');
        $this->assertFalse($component->viewData('searched'));
    }

    public function test_query_2_chars_searched_is_true(): void
    {
        $component = Livewire::test(GlobalSearch::class)->set('query', 'dr');
        $this->assertTrue($component->viewData('searched'));
    }

    public function test_query_1_char_results_all_empty(): void
    {
        $component = Livewire::test(GlobalSearch::class)->set('query', 'a');
        $results   = $component->viewData('results');

        foreach ($results as $group) {
            $this->assertTrue($group->isEmpty(), "Expected empty collection for group");
        }
    }

    // ── Dokter search ─────────────────────────────────────────────────────────

    public function test_cari_dokter_by_nama(): void
    {
        $spesialis = Spesialis::factory()->create(['rumah_sakit_id' => $this->rs->id]);
        Dokter::factory()->create([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $spesialis->id,
            'nama'           => 'dr. Ahmad Fauzi',
            'aktif'          => true,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Ahmad')
            ->viewData('results');

        $this->assertTrue($results['dokter']->isNotEmpty());
        $this->assertStringContainsString('Ahmad', $results['dokter']->first()->nama);
    }

    public function test_dokter_rs_lain_tidak_muncul(): void
    {
        $rs2       = RumahSakit::factory()->create();
        $spesialis = Spesialis::factory()->create(['rumah_sakit_id' => $rs2->id]);
        Dokter::factory()->create([
            'rumah_sakit_id' => $rs2->id,
            'spesialis_id'   => $spesialis->id,
            'nama'           => 'dr. Budi RS Lain',
            'aktif'          => true,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Budi')
            ->viewData('results');

        $this->assertTrue($results['dokter']->isEmpty());
    }

    public function test_dokter_nonaktif_tidak_muncul(): void
    {
        $spesialis = Spesialis::factory()->create(['rumah_sakit_id' => $this->rs->id]);
        Dokter::factory()->create([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $spesialis->id,
            'nama'           => 'dr. Nonaktif',
            'aktif'          => false,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Nonaktif')
            ->viewData('results');

        $this->assertTrue($results['dokter']->isEmpty());
    }

    // ── FAQ search ────────────────────────────────────────────────────────────

    public function test_cari_faq_by_judul(): void
    {
        Faq::create([
            'rumah_sakit_id' => $this->rs->id,
            'judul'          => 'Cara pendaftaran BPJS',
            'deskripsi'      => 'Bawa KTP dan kartu BPJS.',
            'aktif'          => true,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'BPJS')
            ->viewData('results');

        $this->assertTrue($results['faq']->isNotEmpty());
    }

    public function test_faq_nonaktif_tidak_muncul(): void
    {
        Faq::create([
            'rumah_sakit_id' => $this->rs->id,
            'judul'          => 'FAQ Nonaktif Tersembunyi',
            'deskripsi'      => 'Tidak tampil.',
            'aktif'          => false,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Nonaktif')
            ->viewData('results');

        $this->assertTrue($results['faq']->isEmpty());
    }

    // ── Halaman search ────────────────────────────────────────────────────────

    public function test_cari_halaman_by_judul(): void
    {
        Halaman::create([
            'rumah_sakit_id' => $this->rs->id,
            'slug'           => 'visi-misi',
            'judul'          => 'Visi dan Misi',
            'aktif'          => true,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Visi')
            ->viewData('results');

        $this->assertTrue($results['halaman']->isNotEmpty());
    }

    // ── Promo search ──────────────────────────────────────────────────────────

    public function test_cari_promo_by_judul(): void
    {
        Promo::create([
            'rumah_sakit_id' => $this->rs->id,
            'judul'          => 'Diskon Ramadan 2026',
            'slug'           => 'diskon-ramadan',
            'aktif'          => true,
            'popup'          => false,
        ]);

        $results = Livewire::test(GlobalSearch::class)
            ->set('query', 'Ramadan')
            ->viewData('results');

        $this->assertTrue($results['promo']->isNotEmpty());
    }

    // ── View data ─────────────────────────────────────────────────────────────

    public function test_rs_slug_dikirim_ke_view(): void
    {
        $slug = Livewire::test(GlobalSearch::class)->viewData('rsSlug');
        $this->assertEquals($this->rs->slug, $slug);
    }

    public function test_has_results_false_jika_kosong(): void
    {
        $hasResults = Livewire::test(GlobalSearch::class)
            ->set('query', 'xyzabcnotfound123')
            ->viewData('hasResults');

        $this->assertFalse($hasResults);
    }

    public function test_has_results_true_jika_ada_dokter(): void
    {
        $spesialis = Spesialis::factory()->create(['rumah_sakit_id' => $this->rs->id]);
        Dokter::factory()->create([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $spesialis->id,
            'nama'           => 'dr. Zainudin',
            'aktif'          => true,
        ]);

        $hasResults = Livewire::test(GlobalSearch::class)
            ->set('query', 'Zainudin')
            ->viewData('hasResults');

        $this->assertTrue($hasResults);
    }
}
