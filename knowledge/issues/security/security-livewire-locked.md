# Security: Livewire — Proteksi Property dengan `#[Locked]`

**Dibuat:** 2026-06-02  
**Status:** Done  
**Prioritas:** Medium  
**Label:** security

---

## Latar Belakang

Livewire 3 mengizinkan client (browser) untuk mengirim update property via request JSON. Jika sebuah property di-set di `mount()` dan tidak di-lock, user secara teknis bisa memanipulasi nilainya melalui Livewire wire request yang dimodifikasi.

Contoh di `app/Livewire/Dokter/Find.php`:

```php
class Find extends Component
{
    public int $rumah_sakit_id; // tidak ada #[Locked]
    
    public function mount()
    {
        $this->rumah_sakit_id = current_rumahsakit()->id; // diset dari server
    }
    
    public function render()
    {
        $dokter = Dokter::query()
            ->where('rumah_sakit_id', $this->rumah_sakit_id) // DIPAKAI DI QUERY
            ->...
    }
}
```

Jika `$rumah_sakit_id` bisa dimanipulasi, user bisa melihat dokter dari RS lain.

---

## Komponen yang Perlu Diperbaiki

Semua Livewire component yang memiliki property server-assigned yang dipakai sebagai filter query:

| Component | Property yang Perlu `#[Locked]` |
|---|---|
| `Livewire/Dokter/Find.php` | `$rumah_sakit_id` |
| `Livewire/Dokter/Show.php` | `$rumah_sakit_id` (jika ada) |
| `Livewire/Pages/JadwalPraktek.php` | `$rumah_sakit_id` |
| `Livewire/Pages/RawatJalan.php` | `$rumah_sakit_id` |
| `Livewire/Pages/PoliKlinikDetail.php` | `$rumah_sakit_id` |
| `Livewire/RumahSakit/Index.php` | `$rumah_sakit_id` |
| (semua component lain dengan pattern serupa) | |

---

## Perubahan yang Diperlukan

Tambahkan attribute `#[Locked]` pada semua property yang di-set di server dan tidak boleh diubah client:

```php
use Livewire\Attributes\Locked;

class Find extends Component
{
    #[Locked]
    public int $rumah_sakit_id;
    
    public string $search = '';   // ini boleh diubah user — tidak perlu Locked
    public string $spesialis = ''; // ini boleh diubah user — tidak perlu Locked
    
    public function mount()
    {
        $this->rumah_sakit_id = current_rumahsakit()->id;
    }
}
```

Livewire akan melempar `PropertyNotFoundException` jika client mencoba mengubah property yang di-lock — request ditolak secara otomatis.

---

## Catatan

- Hanya property yang di-set server-side dan dipakai sebagai filter query yang perlu di-lock
- Property yang memang diubah user (search, filter, tab aktif) **tidak** perlu di-lock
- Ini adalah defense-in-depth — query sudah di-scope lewat `RumahSakitMiddleware`, tapi `#[Locked]` menambah lapisan proteksi di level component

---

## File yang Diubah

Scan seluruh `app/Livewire/` untuk property yang diinisialisasi dari `current_rumahsakit()` atau nilai server-side lainnya.

---

## Acceptance Criteria

- [ ] Semua property `$rumah_sakit_id` di Livewire components memiliki `#[Locked]`
- [ ] Modifikasi manual via Livewire request menghasilkan error/reject, bukan data RS lain
- [ ] Property yang memang diubah user (search, filter) tetap berfungsi normal
