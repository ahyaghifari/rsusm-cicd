<div>
    <div id="hero" class="relative px-10 py-16">
        <h1 class="text-center text-4xl font-bold bg-primary text-white p-3 w-fit">Fasilitas Pendukung</h1>
    </div>
    <div class="w-10/12 mx-auto mt-10 grid grid-cols-2 gap-12">
        @foreach($fasilitas as $f)
            @if($loop->first)
                <div data-aos="zoom-in-up" class="col-span-2 bg-surface-container grid grid-cols-3 rounded-xl overflow-hidden">
                    <img src="{{ Storage::url($f->gambar) }}" alt="{{ $f->nama }}" class="h-72 w-full object-contain">
                    <div class="col-span-2 p-3 border-l border-gray-300">
                        <h3 class="bg-primary text-white w-fit px-5 py-2 text-3xl font-bold ">{{ $f->nama }}</h3>
                        <div class="text-sm mt-4">
                            {!! str($f->deskripsi)->sanitizeHtml() !!}
                        </div>
                    </div>
                </div>
            @else
                <div  data-aos="zoom-in-up" class="bg-surface-container  rounded-xl overflow-hidden">
                    <div class="relative w-full h-60">
                        <img src="{{ Storage::url($f->gambar) }}" alt="{{ $f->nama }}" class="h-60 w-full object-contain z-50">
                    </div>
                    <div class="p-3 mt-2">
                        <h3 class="text-primary text-2xl font-bold ">{{ $f->nama }}</h3>
                        <div class="text-on-surface-variant mt-4">
                            {!! str($f->deskripsi)->sanitizeHtml() !!}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
