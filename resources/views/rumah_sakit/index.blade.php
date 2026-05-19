<div>
    <!-- Carousel -->
    <div class="w-10/12 mx-auto">
        <div id="hs-carousel" class="relative"
            data-hs-carousel='{"loadingClasses": "opacity-0", "dotsItemClasses": "hs-carousel-active:bg-primary hs-carousel-active:border-primary size-3 border border-line-4 border-primary rounded-full cursor-pointer", "isAutoPlay": true, "isInfiniteLoop": true}'>
            <div class="hs-carousel relative w-full min-h-96 overflow-hidden">
                <!-- Carousel Body -->
                <div
                    class="hs-carousel-body flex flex-nowrap absolute top-0 bottom-0 inset-s-0 transition-transform duration-700 opacity-0">
                    <div class="hs-carousel-slide ">
                        <div class="flex justify-center h-full bg-surface p-6">
                            <span class="self-center text-4xl text-foreground transition duration-700">First slide</span>
                        </div>
                    </div>
                    <div class="hs-carousel-slide ">
                        <div class="flex justify-center h-full bg-surface-1 p-6">
                            <span class="self-center text-4xl text-foreground transition duration-700">Second
                                slide</span>
                        </div>
                    </div>
                </div>
                <!-- End Carousel Body -->
            </div>

            <!-- Arrows -->
            <button type="button"
                class="hs-carousel-prev hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-s-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden">
                <span class="text-2xl" aria-hidden="true">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </span>
                <span class="sr-only">Previous</span>
            </button>
            <button type="button"
                class="hs-carousel-next hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-e-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden">
                <span class="sr-only">Next</span>
                <span class="text-2xl" aria-hidden="true">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </span>
            </button>
            <!-- End Arrows -->

            <div class="hs-carousel-pagination flex justify-center absolute bottom-3 inset-s-0 inset-e-0 gap-x-2">
            </div>
            <!-- End Pagination -->
        </div>
    </div>
    <!-- End Carousel -->

    <hr class="border border-gray-200 my-16">

    <!-- informasi rawat dan antrian -->
    <div class="grid md:grid-cols-3 gap-8 p-10 px-14 mx-auto bg-primary">
        <a
            class="bg-white p-8 rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0px_12px_30px_rgba(152,46,142,0.1)] hover:-translate-y-2 transition-all duration-300 group cursor-pointer hover:shadow-primary/50">
            <div class="w-20 h-20 bg-primary/20 flex items-center justify-center rounded-2xl mb-6">
                <span class="material-symbols-outlined text-primary text-6xl">hotel</span>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Ketersediaan Ruang Rawat Pasien</h3>
            <p class="text-on-surface-variant">Lihat ketersediaan ruang rawat pasien sebelum anda menuju rumah sakit
                disini
            </p>
        </a>
        <a
            class="bg-white p-8 rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0px_12px_30px_rgba(152,46,142,0.1)] hover:-translate-y-2 transition-all duration-300 group cursor-pointer hover:shadow-primary/50">
            <div class="w-20 h-20 bg-primary/20 flex items-center justify-center rounded-2xl mb-6">
                <span class="material-symbols-outlined text-primary text-6xl">calendar_clock</span>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Jadwal Praktek Dokter Kami</h3>
            <p class="text-on-surface-variant">Periksa jadwal prakter dokter kami agar dapat menyesuaikan dengan jadwal
                ketersediaan anda</p>
        </a>
        <a
            class="bg-white p-8 rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0px_12px_30px_rgba(152,46,142,0.1)] hover:-translate-y-2 transition-all duration-300 group cursor-pointer hover:shadow-primary/50">
            <div class="w-20 h-20 bg-primary/20 flex items-center justify-center rounded-2xl mb-6">
                <span class="material-symbols-outlined text-primary text-6xl">monitor</span>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Pantau Antrian Jadwal</h3>
            <p class="text-on-surface-variant">Pantau Jadwal Antrian anda disini agar waktu tidak terbuang sia-sia</p>
        </a>
    </div>

    <!-- kenapa memilih RSU -->
    <section class="grid grid-cols-2 mt-24">
        <div>
            <img src="{{ asset('img/syifa-medika.webp') }}" class="w-full h-full object-cover" alt="">
        </div>
        <div class="p-6 relative">
            <img src="{{ asset('img/bg-header.png') }}"
                class="absolute top-0 left-0 right-0 w-full h-full -z-10 opacity-50 bg-blend-overlay object-cover blur-xs"
                alt="">
            <h2 class="text-on-surface text-xl">Kenapa harus memilih</h2>
            <img src="{{ asset('img/syifa-medika-banjarbaru.png') }}" class="w-80 mt-2" alt="">
            <p class="text-primary mt-6">RSU Syifa Medika Banjarbaru hadir untuk masyarakat yang ingin mendapatkan
                pelayanan
                kesehatan yang berkualitas, RSU Syifa Medika Banjarbaru merupakan pelayanan kesehatan yang jujur dalam
                pelayanan dan selalu memberikan kemudahan karena di dukung oleh staff medis yang profesional,
                bersertifikasi, Ahli dibidangnya serta di dukung oleh peralatan yang mutakhir dan terkini sesuai dengan
                moto
                kami yaitu Pelayanan yang profesional dan terpercaya.</p>

        </div>
    </section>

    <!-- layanan unggulan -->
    <section id="unggulan" class="bg-secondary p-6 mt-24 relative">
        <h1 class="text-white font-bold text-center text-3xl">LAYANAN UNGGULAN KAMI</h1>
        <div class="grid grid-cols-4 w-10/12 mx-auto mt-10">
            <div class="p-6">
                <img src="{{ asset('img/syifa-medika-mobile.webp') }}" class="aspect-square w-full object-cover"
                    alt="">
                <p class="text-white mt-3 text-center font-semibold">SYIFA MEDIKA MOBILE</p>
            </div>

        </div>
    </section>

    <!-- dokter kami -->
    <section class="mt-24">
        <div class="w-10/12 mx-auto">
            <div class s="flex justify-between">
                <div>
                    <h2 class="text-tertiary text-4xl text-center font-bold">Dokter Kami</h2>
                    <h4 class="text-on-surface-variant text-center mt-4 w-4/6 mx-auto">Kami memiliki banyak dokter
                        spesialis
                        hingga sub spesialis ahli dibidangnya dan melayani Anda secara profesional dan terpercaya.</h4>
                </div>
                <div class="grid md:grid-cols-3 mt-8 gap-10">
                    <div class="group relative overflow-hidden bg-white rounded-xl shadow-sm">
                        <div class="aspect-square overflow-hidden">
                            <img alt=""
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAIDlRtTwwgiUBHAcQ8IXDsuYAQs2j9f5-GKsKX4kPFQhwdJsrERteMwQ7TzW7Y_zE7KbHaezaHw3js0Zhkq3dpM0ujxpt40ujf0cydX5ty0vjDiCzxmxKpJdZuirmYjHP2JQdwuFskm0fQSk8SIkZivnyCZVyKhF7q6hoNqO7zgtbNAGVSDB6DQ3pJUNuoNocq349hTOWa7k9BHy_K3YqNX5laeFlIe4_pWnmzs0V6BLW-fSe3HQOXTWoNbZ9ek4_z7JnW8YMRYQu6" />
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="font-semibold text-on-surface">dr. Ahmad Syarif, Sp.PD</h4>
                            <p class=" w-fit p-1 px-2  mt-2 text-primary rounded-lg  mx-auto font-medium mb-4">Spesialis
                                Penyakit Dalam</p>
                            <a href="">
                                <button
                                    class="w-full sm:w-auto self-start border-2 border-tertiary  text-tertiary hover:bg-tertiary hover:text-white px-6 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-sm mx-auto cursor-pointer">
                                    Lihat Jadwal Praktek
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </button>
                            </a>
                        </div>
                    </div>
                    <div class="group relative overflow-hidden bg-white rounded-xl shadow-sm">
                        <div class="aspect-square overflow-hidden">
                            <img alt=""
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAIDlRtTwwgiUBHAcQ8IXDsuYAQs2j9f5-GKsKX4kPFQhwdJsrERteMwQ7TzW7Y_zE7KbHaezaHw3js0Zhkq3dpM0ujxpt40ujf0cydX5ty0vjDiCzxmxKpJdZuirmYjHP2JQdwuFskm0fQSk8SIkZivnyCZVyKhF7q6hoNqO7zgtbNAGVSDB6DQ3pJUNuoNocq349hTOWa7k9BHy_K3YqNX5laeFlIe4_pWnmzs0V6BLW-fSe3HQOXTWoNbZ9ek4_z7JnW8YMRYQu6" />
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="font-semibold text-on-surface">dr. Ahmad Syarif, Sp.PD</h4>
                            <p class=" w-fit p-1 px-2  mt-2 text-primary rounded-lg  mx-auto font-medium mb-4">
                                Spesialis
                                Penyakit Dalam</p>
                            <a href="">
                                <button
                                    class="w-full sm:w-auto self-start border-2 border-tertiary  text-tertiary hover:bg-tertiary hover:text-white px-6 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-sm mx-auto cursor-pointer">
                                    Lihat Jadwal Praktek
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="mt-8">
            <a href="">
                <button
                    class="w-full sm:w-auto self-start border-2 border-tertiary bg-tertiary text-white px-6 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-sm mx-auto cursor-pointer">
                    Lihat Selengkapnya
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </a>
        </div>
    </section>

</div>
