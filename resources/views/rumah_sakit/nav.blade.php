<div class="flex flex-wrap sm:justify-start sm:flex-nowrap w-full bg-primary sticky top-0 z-100 border-b-2 border-yellow-500 py-1">
    <nav class="max-w-340 w-full mx-auto px-4 flex flex-wrap basis-full items-center justify-between text-on-primary">
        <div class="sm:order-3 flex items-center gap-x-2">
            <button type="button"
                class="sm:hidden hs-collapse-toggle relative size-9 flex justify-center items-center gap-x-2 rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                id="hs-navbar-alignment-collapse" aria-expanded="false" aria-controls="hs-navbar-alignment"
                aria-label="Toggle navigation" data-hs-collapse="#hs-navbar-alignment">
                <svg class="hs-collapse-open:hidden shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" x2="21" y1="6" y2="6" />
                    <line x1="3" x2="21" y1="12" y2="12" />
                    <line x1="3" x2="21" y1="18" y2="18" />
                </svg>
                <svg class="hs-collapse-open:block hidden shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
                <span class="sr-only">Toggle</span>
            </button>
            <button type="button"
                class="p-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none">
                <span class="material-symbols-outlined text-[16px]">search</span>
            </button>
        </div>
        <div id="hs-navbar-alignment"
            class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow sm:grow-0 sm:basis-auto sm:block sm:order-2"
            aria-labelledby="hs-navbar-alignment-collapse" role="region">
            <div class="flex flex-col gap-5 mt-5 sm:flex-row sm:items-center sm:mt-0 sm:ps-5">
                 <a wire:navigate  class="text-sm font-medium text-primary-active focus:outline-hidden"
                    href="{{ rumahsakit_route('rumahsakit.home') }}">Beranda</a>
                <!-- Dropdown Dokter -->
                <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex">
                    <button id="hs-dropdown-dokter-kami" type="button"
                        class="hs-dropdown-toggle inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer text-layer-foreground hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Dokter Kami
                        <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 after:h-4 after:absolute after:-bottom-4 after:inset-s-0 after:w-full before:h-4 before:absolute before:-top-4 before:inset-s-0 before:w-full z-50 text-on-surface"
                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-dokter-kami">
                        <div class="p-1 space-y-0.5">
                            <a wire:navigate class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200"
                                href="{{ rumahsakit_route('rumahsakit.dokter_kami') }}">
                                Cari Dokter
                            </a>
                            <a wire:navigate class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200"
                                href="{{ rumahsakit_route('rumahsakit.jadwal_praktek') }}">
                                Jadwal Praktek
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End Dropdown Dokter -->
                <!-- Dropdown Layanan Kami -->
                <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex">
                    <button id="hs-dropdown-dokter-kami" type="button"
                        class="hs-dropdown-toggle inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer text-layer-foreground hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Layanan Kami
                        <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 after:h-4 after:absolute after:-bottom-4 after:inset-s-0 after:w-full before:h-4 before:absolute before:-top-4 before:inset-s-0 before:w-full z-50 text-on-surface"
                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-dokter-kami">
                        <div class="p-1 space-y-0.5">
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.rawat_jalan') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Rawat Jalan
                            </a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.rawat_inap') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Rawat Inap
                            </a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.unggulan') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Unggulan
                            </a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.penunjang_medis') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Penunjang Medis
                            </a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.fasilitas_pendukung') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Fasilitas Pendukung
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End Dropdown Layanan -->
                <!-- Dropdown Tentang Kami -->
                <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex">
                    <button  id="hs-dropdown-dokter-kami" type="button"
                        class="hs-dropdown-toggle inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer text-layer-foreground hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Tentang Kami
                        <svg  class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 after:h-4 after:absolute after:-bottom-4 after:inset-s-0 after:w-full before:h-4 before:absolute before:-top-4 before:inset-s-0 before:w-full z-50 text-on-surface"
                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-dokter-kami">
                        <div class="p-1 space-y-0.5">
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Profil Perusahaan
                            </a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.partner_kami') }}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus hover:bg-gray-200">
                                Partner Kami
                            </a>
                    </div>
                </div>
                </div>
                <!-- End Dropdown Tentang -->
                <a class="text-sm text-navbar-nav-foreground hover:text-primary-hover focus:outline-hidden focus:text-primary-focus"
                    wire:navigate href="{{ rumahsakit_route('rumahsakit.hubungi_kami') }}">Hubungi Kami</a>
                <a class="text-sm text-navbar-nav-foreground hover:text-primary-hover focus:outline-hidden focus:text-primary-focus bg-linear-to-r from-yellow-300 to-yellow-400 text-primary px-2 p-1 hover:from-yellow-400 hover:to-yellow-500 hover:shadow-yellow-500 hover:shadow-lg transition ease-in-out"
                    href="#">Pendaftaran Online</a>
            </div>
        </div>
    </nav>
</div>
