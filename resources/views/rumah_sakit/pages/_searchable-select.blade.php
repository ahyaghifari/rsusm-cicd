{{--
    Searchable Select — Tom Select + Livewire 3
    Variables:
      $property    : string  — nama properti Livewire (contoh: 'poliklinikId')
      $options     : array   — [['value' => '1', 'label' => 'Nama'], ...]
      $placeholder : string  — teks placeholder
      $currentValue: mixed   — nilai yang sedang aktif (opsional, untuk selected state)
--}}
@php
    $currentValue ??= '';
    $wrapperClass ??= 'w-full max-w-sm';
@endphp

<div
    wire:ignore
    x-data="{
        ts: null,
        init() {
            this.ts = new TomSelect(this.$refs.select, {
                allowEmptyOption: true,
                placeholder: '{{ addslashes($placeholder) }}',
                plugins: ['clear_button'],
                onInitialize() {
                    // Pastikan nilai awal tersinkron
                },
                onChange: (val) => {
                    this.$wire.set('{{ $property }}', val ?? '');
                }
            });
        },
        destroy() {
            if (this.ts) { this.ts.destroy(); this.ts = null; }
        }
    }"
    class="{{ $wrapperClass }} ts-portal-wrapper"
>
    <select x-ref="select">
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $opt)
            <option value="{{ $opt['value'] }}"
                    {{ (string)($currentValue ?? '') === (string)$opt['value'] ? 'selected' : '' }}>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>
</div>
