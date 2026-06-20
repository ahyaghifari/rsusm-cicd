<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Chatbot\Panel;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotPanelTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function fakeN8n(string $output = 'Halo, ada yang bisa saya bantu?'): void
    {
        Http::fake([
            '*' => Http::response(['output' => $output], 200),
        ]);
    }

    private function makeRS(array $attrs = []): RumahSakit
    {
        return RumahSakit::factory()->create(array_merge([
            'no_emergency' => '0511-5910889',
            'no_hotline'   => '0821-5342-4447',
            'aktif'        => true,
        ], $attrs));
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    public function test_renders_branch_selection_by_default(): void
    {
        $this->makeRS();
        Livewire::test(Panel::class)->assertSee('Pilih cabang RSU Syifa Medika');
    }

    public function test_shows_emergency_number_in_footer(): void
    {
        $rs = $this->makeRS(['no_emergency' => '0511-5910889']);

        Livewire::test(Panel::class)->assertSee('0511-5910889');
    }

    // ── Branch Selection ──────────────────────────────────────────────────────

    public function test_select_branch_marks_branch_selected(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->assertSet('branchSelected', true)
            ->assertSet('activeBranchSlug', $rs->slug);
    }

    public function test_select_invalid_branch_does_nothing(): void
    {
        Livewire::test(Panel::class)
            ->call('selectBranch', 'slug-tidak-ada')
            ->assertSet('branchSelected', false);
    }

    public function test_select_branch_adds_welcome_bot_message(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS(['nama' => 'RS Test']);

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug);

        $messages = $component->get('messages');
        $this->assertNotEmpty($messages);
        $this->assertEquals('bot', $messages[0]['type']);
        $this->assertStringContainsString('RS Test', $messages[0]['text']);
    }

    // ── Change Branch ─────────────────────────────────────────────────────────

    public function test_change_branch_resets_all_state(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('changeBranch')
            ->assertSet('branchSelected', false)
            ->assertSet('activeBranchSlug', null)
            ->assertSet('sessionKey', '')
            ->assertSet('messages', []);
    }

    // ── sendMessage dengan text parameter ─────────────────────────────────────

    public function test_send_message_with_text_param_adds_user_bubble(): void
    {
        $this->fakeN8n('Terima kasih pertanyaannya.');
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('sendMessage', 'Apa jadwal dokter?');

        $messages = $component->get('messages');
        $userMessages = array_filter($messages, fn ($m) => $m['type'] === 'user');
        $this->assertNotEmpty($userMessages);
        $this->assertStringContainsString('Apa jadwal dokter?', array_values($userMessages)[0]['text']);
    }

    public function test_send_message_with_text_param_adds_bot_bubble(): void
    {
        $this->fakeN8n('Jadwal dokter tersedia Senin-Sabtu.');
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('sendMessage', 'Jadwal dokter?');

        $messages = $component->get('messages');
        $botMessages = array_filter($messages, fn ($m) => $m['type'] === 'bot');
        $lastBot = array_values($botMessages)[count($botMessages) - 1];
        $this->assertEquals('Jadwal dokter tersedia Senin-Sabtu.', $lastBot['text']);
    }

    public function test_send_empty_message_does_nothing(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug);

        $beforeCount = count($component->get('messages'));

        $component->call('sendMessage', '   ');

        $this->assertCount($beforeCount, $component->get('messages'));
    }

    public function test_send_message_without_branch_does_nothing(): void
    {
        Livewire::test(Panel::class)
            ->call('sendMessage', 'Halo')
            ->assertSet('messages', []);
    }

    // ── Session Key ───────────────────────────────────────────────────────────

    public function test_session_key_generated_on_first_message(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug);

        $this->assertEquals('', $component->get('sessionKey'));

        $component->call('sendMessage', 'Halo');

        $this->assertNotEmpty($component->get('sessionKey'));
    }

    public function test_session_key_persists_across_messages(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('sendMessage', 'Pesan 1');

        $key1 = $component->get('sessionKey');

        $component->call('sendMessage', 'Pesan 2');
        $key2 = $component->get('sessionKey');

        $this->assertEquals($key1, $key2);
        $this->assertNotEmpty($key1);
    }

    public function test_session_key_reset_on_change_branch(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        $component = Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('sendMessage', 'Halo');

        $this->assertNotEmpty($component->get('sessionKey'));

        $component->call('changeBranch');

        $this->assertEquals('', $component->get('sessionKey'));
    }

    // ── Session Persistence (mount restore) ───────────────────────────────────

    public function test_mount_restores_branch_from_session(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        // Simulasi state yang disimpan ke session
        Session::put('chatbot_state', [
            'sessionKey'       => 'abc-123',
            'activeBranchSlug' => $rs->slug,
            'branchSelected'   => true,
            'messages'         => [
                ['type' => 'bot', 'text' => 'Halo!', 'time' => '10:00'],
            ],
        ]);

        $component = Livewire::test(Panel::class);

        $this->assertTrue($component->get('branchSelected'));
        $this->assertEquals($rs->slug, $component->get('activeBranchSlug'));
        $this->assertEquals('abc-123', $component->get('sessionKey'));
        $this->assertCount(1, $component->get('messages'));
    }

    public function test_mount_handles_deleted_branch_gracefully(): void
    {
        // Branch tidak ada di DB → reset state
        Session::put('chatbot_state', [
            'sessionKey'       => 'abc-123',
            'activeBranchSlug' => 'slug-tidak-ada',
            'branchSelected'   => true,
            'messages'         => [['type' => 'bot', 'text' => 'Halo!', 'time' => '10:00']],
        ]);

        $component = Livewire::test(Panel::class);

        $this->assertFalse($component->get('branchSelected'));
        $this->assertNull($component->get('activeBranchSlug'));
        $this->assertEmpty($component->get('messages'));
    }

    public function test_save_state_writes_to_session_after_select_branch(): void
    {
        $this->fakeN8n();
        $rs = $this->makeRS();

        Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug);

        $saved = Session::get('chatbot_state');
        $this->assertNotNull($saved);
        $this->assertTrue($saved['branchSelected']);
        $this->assertEquals($rs->slug, $saved['activeBranchSlug']);
    }

    public function test_save_state_writes_to_session_after_send_message(): void
    {
        $this->fakeN8n('OK');
        $rs = $this->makeRS();

        Livewire::test(Panel::class)
            ->call('selectBranch', $rs->slug)
            ->call('sendMessage', 'Test');

        $saved = Session::get('chatbot_state');
        $this->assertNotNull($saved);
        $this->assertNotEmpty($saved['sessionKey']);

        $userMsgs = array_filter($saved['messages'], fn ($m) => $m['type'] === 'user');
        $this->assertNotEmpty($userMsgs);
    }

    public function test_messages_capped_at_50_in_session(): void
    {
        $rs = $this->makeRS();

        // Buat 60 messages langsung di session
        $messages = [];
        for ($i = 1; $i <= 60; $i++) {
            $messages[] = ['type' => 'user', 'text' => "Pesan $i", 'time' => '10:00'];
        }

        Session::put('chatbot_state', [
            'sessionKey'       => 'key',
            'activeBranchSlug' => $rs->slug,
            'branchSelected'   => true,
            'messages'         => $messages,
        ]);

        $component = Livewire::test(Panel::class);

        // mount() memotong ke MAX_MESSAGES (50) terakhir
        $this->assertCount(50, $component->get('messages'));
    }
}
