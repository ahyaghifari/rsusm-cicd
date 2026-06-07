<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $rumahSakit = RumahSakit::where('slug', 'banjarbaru')->first();
        $rumahSakitId = $rumahSakit ? $rumahSakit->id : 1;

        $asuransi = [
            'BPJS KESEHATAN',
            'BPJS KETENAGAKERJAAN',
            'JASA RAHARJA',
            'APLN',
            'MANDIRI INHEALTH INDEMNITY',
            'MANDIRI INHEALTH MANAGED CARE',
            'MANDIRI INHEALTH I-PRO',
            'PRUDENTIAL LIFE INSURANCE',
            'PRUDENTIAL SHARIA INSURANCE',
            'AVRIST ASSURANCE',
            'BNI LIFE INSURANCE',
            'AXA SERVICES INDONESIA',
            'GARDA MEDIKA',
            'SUNDAY INSURANCE',
            'SINAR MAS MSIG',
            'PACIFIC CROSS',
            'ISOMEDIK',
            'LIPPO LIFE INSURANCE',
            'HALODOC',
            'RELIANCE INDONESIA',
            'SINAR MAS',
            'TUGU KRESNA PRATAMA',
            'ASTRA BUANA',
            'JIWA ADISARANA WANAARTHA',
            'AA INTERNATIONAL INDONESIA',
            'TAKAFUL',
            'FWD',
            'GLOBAL ASSISTANCE & HEALTHCARE',
            'HIGEA MEDIKA INSURA SOLUSI INDONESIA',
            'MULTI ARTHA GUNA (MAG)',
            'MANULIFE INDONESIA',
            'BRI LIFE',
            'DOC DOC HEALTHCARE INDONESIA',
            'ALLIANZ LIFE INDONESIA',
            'ALLIANZ LIFE SYARIAH INDONESIA',
            'YAYASAN KESEHATAN PERTAMINA',
            'ZURICH ASURANSI INDONESIA',
            'CHUBB LIFE INSURANCE',
            'FULLERTON HEALTH INDONESIA',
            'AJ CENTRAL ASIA RAYA (AJ CAR)',
            'PRIMA SARANA JASA',
            'MEDIKA PLAZA',
            'MEDILUM',
            'TELKOMEDIKA',
            'YKP BANK BJB',
            'MEDLINX ASIA TEKNOLOGI',
            'SYNTECH MITRA INTEGRASI',
            'PT ASURANSI CAKRAWALA PROTEKSI INDONESIA (ACPI)',
            'PT ASURANSI CENTRAL ASIA (ACA)',
            'CENTRAL ASIA RAYA (CAR)',
            'OWLEXA',
            'MEDILINK DIGITAL MEDIKA',
            'ADMEDIKA',
            'MEDITAP',
            'HEALTHMETRICS',
        ];

        $perusahaan = [
            'PT. TRAKINDO',
            'PT. INDOFOOD CBF SUKSES MAKMUR Tbk NOODLE DIVISION',
            'PT. CJ CHEILJEDANG FEED KALIMANTAN',
            'PT. PAMA PERSADA NUSANTARA',
            'PT. TRINAKA ESTU MANUNGGAL',
            'PT. BHUMI RANTAU ENERGI (PT. BRE)',
            'PT. TAPIN SUTHRA BERJAYA',
            'PT. TALENTA BUMI',
            'PT. JAYA MANDIRI SUKSES',
            'PT. KALIMANTAN PRIMA PERSADA (PT. KPP)',
            'PT. HEXINDO ADIPERKASA TBK',
            'PT. CHITRA PARATAMA',
            'PT. NUSA KONTRUKSI ENJINIRING TBK',
            'PT. TRI SWARDANA UTAMA (PT. TSU)',
            'PT. JAMBO MUTIARA PERMATA',
            'AIRNAV',
            'PT. JAPFA COMFEED INDONESIA, Tbk (Unit Bjm)',
            'PT. CITRA PRIMA UTAMA (PT. CPU)',
            'PT. PETROSEA Tbk',
            'PT. ASTRA INTERNATIONAL-Tbk ISUZU Cab. Bjm',
            'PT. CIPTA KRIDATAMA',
        ];

        foreach ($asuransi as $nama) {
            Partner::create([
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => $nama,
                'logo' => null,
                'kategori' => 'ASURANSI',
                'aktif' => true,
            ]);
        }

        foreach ($perusahaan as $nama) {
            Partner::create([
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => $nama,
                'logo' => null,
                'kategori' => 'PERUSAHAAN',
                'aktif' => true,
            ]);
        }
    }
}
