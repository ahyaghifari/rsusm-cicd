// ── Sound effect notifikasi Tanya Dokter ───────────────────────────────────
//
// playNotificationSound(): pesan baru dari dokter ke pasien — diputar di mana pun
// pasien berada (halaman chat itu sendiri, halaman lain di website yang sama,
// atau tab tidak aktif). Dipanggil oleh globalKonsultasiListener di
// layouts/rumah_sakit.blade.php
//
// playAntrianSound(): sesi konsultasi baru masuk ke antrean dokter — diputar di
// KonsultasiDashboard. Dipanggil oleh listener Echo di
// filament/dokter/pages/konsultasi-dashboard.blade.php

let _notifAudio   = null;
let _antrianAudio = null;

function playNotificationSound() {
    try {
        if (!_notifAudio) {
            _notifAudio = new Audio('/audio/newmessage.mp3');
        }
        _notifAudio.currentTime = 0;
        _notifAudio.play().catch((e) => console.warn('[notif] Gagal memutar suara:', e));
    } catch (e) {
        console.warn('[notif] Gagal memutar suara:', e);
    }
}

function playAntrianSound() {
    try {
        if (!_antrianAudio) {
            _antrianAudio = new Audio('/audio/newantrian.mp3');
        }
        _antrianAudio.currentTime = 0;
        _antrianAudio.play().catch((e) => console.warn('[notif] Gagal memutar suara:', e));
    } catch (e) {
        console.warn('[notif] Gagal memutar suara:', e);
    }
}

// Unlock autoplay audio pada interaksi pertama (kebijakan browser)
['click', 'keydown', 'touchstart'].forEach((evt) => {
    document.addEventListener(evt, () => {
        if (!_notifAudio)   _notifAudio   = new Audio('/audio/newmessage.mp3');
        if (!_antrianAudio) _antrianAudio = new Audio('/audio/newantrian.mp3');
    }, { once: true, passive: true });
});
