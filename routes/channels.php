<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Private channel notifikasi sesi konsultasi baru untuk dokter tertentu.
 * Hanya dokter pemilik akun atau admin/humas RS yang sama yang boleh subscribe —
 * lihat User::bisaMenanganiDokter() untuk aturan lengkapnya.
 */
Broadcast::channel('konsultasi.dokter.{dokterId}', function ($user, $dokterId) {
    return $user->bisaMenanganiDokter((int) $dokterId);
});
