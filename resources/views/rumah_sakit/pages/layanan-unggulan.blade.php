<div>
    <div id="hero" class="relative px-10 py-16 bg-primary mb-20">
        <h1 class="text-center text-4xl font-bold text-white">Layanan Unggulan</h1>
    </div>

    <div class="flex flex-col gap-12 w-10/12 mx-auto">
        @foreach($data as $layanan)
            <div class="">
                <h2 class="text-3xl font-bold text-primary">{{ $layanan->nama }}</h2>
                <div class="grid grid-cols-4 gap-5 mt-5">
                    <img src="{{Storage::url($layanan->gambar)}}" alt="{{$layanan->nama}}">
                    <div class="col-span-3 bg-white p-5 rounded-xl text-on-surface shadow">
                        <p>{!! str($layanan->deskripsi)->sanitizeHtml() !!}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
