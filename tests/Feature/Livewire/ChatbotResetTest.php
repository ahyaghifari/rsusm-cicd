<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Chatbot\Panel;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotResetTest extends TestCase
{
    use RefreshDatabase;

    private function makeRS(): RumahSakit
    {
        return RumahSakit::factory()->create([
            'no_emergency' => '0511-123456',
            'aktif'        => true,
        ]);
    }

    private function fakeN8n(): void
    {
        Http::fake(['*' => Http::response(['output' => 'OK'], 200)]);
    }

    public function test_reset_tidak_bisa_jika_kurang_dari_10_pesan(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug);

        $beforeCount = count($component->get('messages'));

        // Hanya 1 pesan (welcome) — belum cukup untuk reset
        $component->call('resetConversation');

        $this->assertCount($beforeCount, $component->get('messages'));
    }

    public function test_reset_berhasil_jika_lebih_dari_10_pesan(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        // Inject 12 pesan langsung via session
        \Illuminate\Support\Facades\Session::put('chatbot_state', [
            'sessionKey'       => 'test-key-123',
            'activeBranchSlug' => $rs->slug,
            'branchSelected'   => true,
            'messages'         => array_map(fn ($i) => [
                'type' => $i % 2 === 0 ? 'user' : 'bot',
                'text' => "Pesan $i",
                'time' => '10:00',
            ], range(1, 12)),
        ]);

        $component = Livewire::test(Panel::class);

        $this->assertCount(12, $component->get('messages'));
        $this->assertEquals('test-key-123', $component->get('sessionKey'));

        $component->call('resetConversation');

        $this->assertEmpty($component->get('messages'));
        $this->assertEquals('', $component->get('sessionKey'));
    }

    public function test_reset_juga_clear_session(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        \Illuminate\Support\Facades\Session::put('chatbot_state', [
            'sessionKey'       => 'abc',
            'activeBranchSlug' => $rs->slug,
            'branchSelected'   => true,
            'messages'         => array_fill(0, 11, ['type' => 'user', 'text' => 'x', 'time' => '10:00']),
        ]);

        Livewire::test(Panel::class)->call('resetConversation');

        $saved = \Illuminate\Support\Facades\Session::get('chatbot_state');
        $this->assertEmpty($saved['messages']);
        $this->assertEquals('', $saved['sessionKey']);
    }

    public function test_branch_masih_terpilih_setelah_reset(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        \Illuminate\Support\Facades\Session::put('chatbot_state', [
            'sessionKey'       => 'abc',
            'activeBranchSlug' => $rs->slug,
            'branchSelected'   => true,
            'messages'         => array_fill(0, 11, ['type' => 'user', 'text' => 'x', 'time' => '10:00']),
        ]);

        $component = Livewire::test(Panel::class)->call('resetConversation');

        // Branch tetap terpilih — hanya messages & sessionKey yang direset
        $this->assertTrue($component->get('branchSelected'));
        $this->assertEquals($rs->slug, $component->get('activeBranchSlug'));
    }
}
