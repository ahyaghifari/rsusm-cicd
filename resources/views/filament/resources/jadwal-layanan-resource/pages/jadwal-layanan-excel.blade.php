<x-filament-panels::page>

@assets
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@32.3.3/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@32.3.3/styles/ag-theme-alpine.css">
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@32.3.3/dist/ag-grid-community.min.js"></script>
@endassets

<div class="space-y-4">

    {{-- Filter --}}
    <div class="rounded-xl overflow-hidden shadow-sm ring-1 ring-primary-200 dark:ring-primary-700/50">
        <div class="bg-linear-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold text-white tracking-wide">Filter</span>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 py-4">
            {{ $this->filterForm }}
        </div>
    </div>

    @if($this->getActiveRumahSakitId())

        {{-- Tab Hari --}}
        <div class="flex items-end gap-1 overflow-x-auto px-3 pt-3 rounded-t-xl
                    bg-linear-to-b from-primary-50 to-transparent
                    dark:from-primary-950/40 dark:to-transparent
                    border-b border-primary-200 dark:border-primary-800/60">
            @foreach(\App\Enums\Hari::cases() as $hari)
                <button
                    wire:click="setActiveHari('{{ $hari->value }}')"
                    @class([
                        'px-6 py-3 text-sm font-semibold rounded-t-lg border border-b-0 whitespace-nowrap transition-all',
                        'bg-white dark:bg-gray-900 border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 -mb-px z-10 shadow-sm shadow-primary-100 dark:shadow-none'
                            => $activeHari === $hari->value,
                        'bg-white/50 dark:bg-gray-800/50 border-transparent text-gray-500 dark:text-gray-400 hover:bg-white/80 dark:hover:bg-gray-700/60 hover:text-primary-600 dark:hover:text-primary-400 mb-0'
                            => $activeHari !== $hari->value,
                    ])
                >{{ $hari->getLabel() }}</button>
            @endforeach
        </div>

        {{-- AG Grid --}}
        @php
            $poliklinikOptions = $this->getPoliklinikOptions();
            $dokterOptions     = $this->getDokterOptions();
            $statusOptions     = collect(\App\Enums\StatusLayanan::cases())
                ->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()])
                ->toArray();
        @endphp

        <div x-data="jadwalMingguanGrid(
            @js($poliklinikOptions),
            @js($dokterOptions),
            @js($statusOptions),
            @js($this->rows)
        )">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                     bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300
                                     ring-1 ring-primary-200 dark:ring-primary-700">
                            Jadwal Mingguan · Excel
                        </span>
                        <span class="font-bold text-primary-600 dark:text-primary-400">
                            {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                        </span>
                    </div>
                </x-slot>
                <x-slot name="headerEnd">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            x-on:click="addRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                                   border border-dashed border-gray-300 dark:border-gray-600
                                   text-gray-600 dark:text-gray-400
                                   hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400
                                   hover:bg-primary-50 dark:hover:bg-primary-900/10 transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Baris
                        </button>
                        <x-filament::button x-on:click="saveGrid()" icon="heroicon-m-cloud-arrow-up" size="sm">
                            Simpan {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                        </x-filament::button>
                    </div>
                </x-slot>

                <div
                    data-grid-el
                    class="w-full rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
                    style="height: 460px;"
                ></div>

                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                    <span class="text-red-400 font-bold">*</span> Wajib diisi. &nbsp;
                    Klik sel untuk mengedit langsung. &nbsp;
                    Kolom <strong>Jam Selesai</strong> bersifat opsional.
                    <span class="ml-2 inline-flex items-center gap-1">
                        <span class="inline-block w-3 h-3 rounded-sm" style="background:#fef9c3;border:1px solid #fde047;"></span>
                        = kolom wajib yang belum diisi.
                    </span>
                </p>
            </x-filament::section>
        </div>

    @else
        <x-filament::section>
            <div class="py-10 text-center">
                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-sm">
                        Silakan pilih <strong>Rumah Sakit</strong> terlebih dahulu
                        untuk melihat dan mengatur jadwal layanan.
                    </p>
                </div>
            </div>
        </x-filament::section>
    @endif

</div>

<script>
window.jadwalMingguanGrid = function(poliklinikOptions, dokterOptions, statusOptions, initialRows) {
    let gridApi = null;
    let wireOff  = null;

    return {
        init() {
            if (typeof agGrid === 'undefined') {
                console.error('AG Grid belum dimuat');
                return;
            }

            this.$nextTick(() => {
            const gridEl = this.$el.querySelector('[data-grid-el]');
            if (!gridEl) return;

            const isDark = document.documentElement.classList.contains('dark');
            gridEl.classList.add(isDark ? 'ag-theme-alpine-dark' : 'ag-theme-alpine');

            const delBtn = (p) => {
                const btn = document.createElement('button');
                btn.title = 'Hapus baris';
                btn.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;color:#9ca3af;background:transparent;border:none;cursor:pointer;';
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                btn.onmouseover = () => { btn.style.color = '#ef4444'; btn.style.background = '#fef2f2'; };
                btn.onmouseout  = () => { btn.style.color = '#9ca3af'; btn.style.background = 'transparent'; };
                btn.onclick = () => { if (confirm('Hapus baris ini?')) p.api.applyTransaction({ remove: [p.node.data] }); };
                return btn;
            };

            const hl = (ok) => ok ? null : { backgroundColor: '#fef9c3' };

            const colDefs = [
                {
                    headerName: '#',
                    valueGetter: 'node.rowIndex + 1',
                    width: 52,
                    editable: false,
                    pinned: 'left',
                    suppressMovable: true,
                    cellStyle: { color: '#9ca3af', textAlign: 'center', fontSize: '12px' },
                },
                {
                    field: 'poliklinik_id',
                    headerName: 'Poliklinik *',
                    editable: true,
                    minWidth: 160,
                    flex: 2,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: { values: ['', ...Object.values(poliklinikOptions)] },
                    valueGetter:  p => p.data.poliklinik_id ? (poliklinikOptions[p.data.poliklinik_id] || '') : '',
                    valueSetter:  p => {
                        if (!p.newValue) { p.data.poliklinik_id = null; return true; }
                        const e = Object.entries(poliklinikOptions).find(([, v]) => v === p.newValue);
                        if (e) { p.data.poliklinik_id = parseInt(e[0]); return true; }
                        return false;
                    },
                    cellStyle: p => hl(p.data.poliklinik_id),
                },
                {
                    field: 'dokter_id',
                    headerName: 'Dokter',
                    editable: true,
                    minWidth: 160,
                    flex: 2,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: { values: ['', ...Object.values(dokterOptions)] },
                    valueGetter:  p => p.data.dokter_id ? (dokterOptions[p.data.dokter_id] || '') : '',
                    valueSetter:  p => {
                        if (!p.newValue) { p.data.dokter_id = null; return true; }
                        const e = Object.entries(dokterOptions).find(([, v]) => v === p.newValue);
                        if (e) { p.data.dokter_id = parseInt(e[0]); return true; }
                        return false;
                    },
                },
                {
                    field: 'nama_dokter',
                    headerName: 'Nama Dokter',
                    editable: true,
                    minWidth: 150,
                    flex: 2,
                },
                {
                    field: 'jam_mulai',
                    headerName: 'Jam Mulai *',
                    editable: true,
                    width: 115,
                    cellStyle: p => hl(p.data.jam_mulai),
                },
                {
                    field: 'jam_selesai',
                    headerName: 'Jam Selesai',
                    editable: true,
                    width: 115,
                },
                {
                    field: 'status_layanan',
                    headerName: 'Status *',
                    editable: true,
                    width: 120,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: { values: ['', ...Object.values(statusOptions)] },
                    valueGetter:  p => statusOptions[p.data.status_layanan] || '',
                    valueSetter:  p => {
                        if (!p.newValue) { p.data.status_layanan = ''; return true; }
                        const e = Object.entries(statusOptions).find(([, v]) => v === p.newValue);
                        if (e) { p.data.status_layanan = e[0]; return true; }
                        return false;
                    },
                    cellStyle: p => hl(p.data.status_layanan),
                },
                {
                    field: 'catatan',
                    headerName: 'Catatan',
                    editable: true,
                    minWidth: 160,
                    flex: 3,
                },
                {
                    headerName: '',
                    width: 52,
                    editable: false,
                    sortable: false,
                    resizable: false,
                    pinned: 'right',
                    suppressMovable: true,
                    cellRenderer: delBtn,
                },
            ];

            gridApi = agGrid.createGrid(gridEl, {
                columnDefs: colDefs,
                rowData: (initialRows || []).map(r => ({ ...r })),
                defaultColDef: { resizable: true, sortable: false },
                rowHeight: 40,
                headerHeight: 42,
                stopEditingWhenCellsLoseFocus: true,
                singleClickEdit: true,
                animateRows: false,
                domLayout: 'normal',
            });

            wireOff = this.$wire.on('gridRows', ({ rows }) => {
                if (gridApi) gridApi.setGridOption('rowData', (rows || []).map(r => ({ ...r })));
            });
            }); // end $nextTick
        },

        addRow() {
            if (!gridApi) return;
            gridApi.applyTransaction({
                add: [{
                    poliklinik_id: null,
                    dokter_id: null,
                    nama_dokter: '',
                    jam_mulai: '',
                    jam_selesai: '',
                    status_layanan: 'BUKA',
                    catatan: '',
                }]
            });
        },

        saveGrid() {
            if (!gridApi) return;
            const rows = [];
            gridApi.forEachNode(n => rows.push(n.data));
            this.$wire.saveFromGrid(rows);
        },

        destroy() {
            if (wireOff) { wireOff(); wireOff = null; }
            if (gridApi) {
                try { gridApi.destroy(); } catch (e) {}
                gridApi = null;
            }
        },
    };
};
</script>
</x-filament-panels::page>
