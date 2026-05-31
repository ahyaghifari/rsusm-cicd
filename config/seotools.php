<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        'defaults' => [
            'title'        => false, // di-set dinamis via RsPortalComponent::boot() → setTitleDefault($rs->nama)
            'titleBefore'  => false,
            'description'  => 'Portal Rumah Sakit Syifa Medika — informasi layanan, dokter, jadwal, dan fasilitas kesehatan.',
            'separator'    => ' - ',
            'keywords'     => ['rumah sakit', 'dokter', 'poliklinik', 'rawat inap', 'layanan kesehatan'],
            'canonical'    => 'full',
            'robots'       => false,
        ],
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],
        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        'defaults' => [
            'title'       => false, // di-set per halaman via booted() RsPortalComponent
            'description' => false,
            'url'         => null,
            'type'        => 'website',
            'site_name'   => env('APP_NAME', 'RSU Syifa Medika'),
            'images'      => [],
        ],
    ],
    'twitter' => [
        'defaults' => [
            //'card'  => 'summary',
            //'site'  => '@syifamedika',
        ],
    ],
    'json-ld' => [
        'defaults' => [
            'title'       => false, // di-set per halaman via booted() RsPortalComponent
            'description' => false,
            'url'         => null,
            'type'        => 'MedicalOrganization',
            'images'      => [],
        ],
    ],
];
