# Dokumentasi Reverb — Fitur Chat Konsultasi Real-Time

Folder ini berisi catatan setiap perubahan/penambahan terkait **Laravel Reverb** (server WebSocket) untuk fitur "Tanya Dokter" — sekaligus sebagai bahan belajar karena ini pertama kalinya kita memakai Reverb, Pusher protocol, dan Laravel Echo di project ini.

Setiap kali ada perubahan pada sistem broadcasting/realtime, dokumen baru akan ditambahkan di sini secara berurutan — supaya kamu bisa mengikuti perkembangan & memahami konsepnya sambil jalan.

## Daftar Dokumen

| Dokumen | Isi |
|---|---|
| [00-konsep-dasar.md](00-konsep-dasar.md) | Penjelasan dasar: apa itu Reverb, Pusher protocol, Echo, dan bagaimana semuanya saling terhubung |
| [01-setup-instalasi.md](01-setup-instalasi.md) | Catatan instalasi & konfigurasi awal Reverb (Fase 0), termasuk hasil uji coba end-to-end |
| [02-events-channel-livewire.md](02-events-channel-livewire.md) | Penggunaan Reverb pertama yang sungguhan (Fase 2): event broadcast (`ShouldBroadcastNow`, `broadcastOn`/`broadcastAs`/`broadcastWith`), channel publik berbasis token, listener `#[On('echo:...')]` di Livewire dengan placeholder dinamis, timer real-time, dan hasil uji end-to-end |
| [03-private-channel-dan-dashboard-dokter.md](03-private-channel-dan-dashboard-dokter.md) | **Private channel pertama** di project ini (Fase 3): kenapa channel publik tidak cukup untuk notifikasi dokter, otorisasi via `Broadcast::channel()` & `routes/channels.php`, `PrivateChannel` di `broadcastOn()`, awalan `echo-private:` di Livewire, panel Filament terpisah untuk dokter, dan jebakan placeholder dinamis yang tidak boleh `null` |
| [04-window-echo-hilang-di-panel-filament.md](04-window-echo-hilang-di-panel-filament.md) | **Bug "harus refresh terus" (bagian 1)**: kenapa `window.Echo` tidak pernah ada di halaman panel Filament (`/dokter`), bagaimana sistem aset Filament terisolasi dari Vite aplikasi, dan perbaikannya lewat *render hook* `@vite(['resources/js/app.js'])` di `DokterPanelProvider` |
| [05-mismatch-namespace-echo-broadcastas.md](05-mismatch-namespace-echo-broadcastas.md) | **Bug "harus refresh terus" (bagian 2 — akar masalah sesungguhnya)**: kenapa listener Livewire tetap diam meski `window.Echo` sudah tersambung — `EventFormatter` Echo & *namespace* default `App.Events` yang tidak cocok dengan `broadcastAs()` nama pendek, teknik diagnosis `bind_global` untuk menangkap event mentah, dan perbaikannya lewat `namespace: ''` di `echo.js` — sudah diverifikasi end-to-end |
| [06-race-condition-subscribe-channel-dinamis.md](06-race-condition-subscribe-channel-dinamis.md) | **Bug halus baru setelah dua bug sebelumnya beres**: kenapa hanya *pesan pertama* dari pasien yang tidak muncul live di dashboard dokter (harus reload sekali) sementara pesan berikutnya & arah sebaliknya selalu lancar — *race condition* antara channel dinamis `{sesiAktifToken}` yang baru disubscribe vs. pesan pertama yang langsung disiarkan, dan perbaikannya lewat `wire:poll.visible.5s` sebagai jaring pengaman "WebSocket + polling" |
| [07-push-notification-pasien.md](07-push-notification-pasien.md) | **Push notification tiga lapisan untuk pasien**: in-app toast (Alpine + Echo) saat pasien di halaman lain, browser Notification API saat tab tidak aktif, dan Web Push (service worker + VAPID + `minishlink/web-push`) saat browser ditutup — cara kerja Push Service, siklus hidup subscription, job queue, dan cara menguji |
| [08-production-reverb-queue-setup.md](08-production-reverb-queue-setup.md) | **Setup production Reverb & Queue**: konfigurasi Supervisor untuk `reverb:start` dan `queue:work` sebagai daemon, proxy Nginx untuk WebSocket, `.env` production, dan cara restart worker setelah deploy |

## Konteks

Dokumentasi ini adalah bagian dari rencana besar fitur **"Tanya Dokter — Konsultasi Chat Real-Time"**, lihat rencana lengkapnya di [issues/tanya-dokter-plan.md](../issues/tanya-dokter-plan.md). Reverb dipilih sebagai mesin realtime karena merupakan solusi WebSocket *first-party* Laravel 12 — gratis, self-hosted, dan terintegrasi langsung tanpa perlu layanan pihak ketiga berbayar (seperti Pusher).
