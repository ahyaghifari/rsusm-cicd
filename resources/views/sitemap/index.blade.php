{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($branches as $slug)
    <sitemap>
        <loc>{{ route('rumahsakit.sitemap', ['rumahsakit' => $slug]) }}</loc>
    </sitemap>
@endforeach
</sitemapindex>
