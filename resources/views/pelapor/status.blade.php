@extends('layouts.pelapor')

@section('title', 'Cek Status Laporan')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Cek Status Laporan</h1>
            <p class="mt-2 text-slate-500">Masukkan kode laporan untuk melihat perkembangan penanganan.</p>
        </div>

        {{-- Form cari --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm mb-8">
            <label class="block text-sm font-medium mb-2">Kode Laporan</label>
            <div class="flex gap-2">
                <input type="text" id="kode-input" placeholder="Contoh: SPJ-123456"
                       class="flex-1 border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                <button type="button" onclick="cekStatus()" class="bg-brand-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-brand-700 transition-colors">Cari</button>
            </div>
            <p class="text-xs text-slate-400 mt-2">Demo: gunakan kode apa saja untuk melihat contoh tampilan status.</p>
        </div>

        {{-- Tracking status --}}
        <div id="tracking" class="hidden bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-headline font-bold">Pelacakan Laporan</h2>
                <span class="text-sm font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full" id="badge-status">Selesai</span>
            </div>

            <ol class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                @php
                    $statuses = [
                        ['Laporan dibuat', 'Laporan Anda telah kami terima.'],
                        ['Laporan diterima', 'Tim kami telah menerima laporan.'],
                        ['Sedang diverifikasi', 'Sedang memeriksa kebenaran laporan.'],
                        ['Ditindaklanjuti', 'Laporan sedang ditangani petugas.'],
                        ['Petugas menuju lokasi', 'Petugas dalam perjalanan ke lokasi.'],
                        ['Penanganan selesai', 'Penanganan di lokasi selesai.'],
                        ['Laporan selesai', 'Laporan telah selesai ditangani.'],
                    ];
                @endphp
                @foreach ($statuses as $i => [$title, $desc])
                    <li class="status-item relative ml-4 {{ $i < 5 ? 'done' : '' }}">
                        <span class="status-dot absolute -left-[27px] top-0 w-3 h-3 rounded-full {{ $i < 5 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        <h3 class="font-semibold text-slate-800">{{ $title }}</h3>
                        <p class="text-sm text-slate-500">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function cekStatus() {
        const kode = document.getElementById('kode-input').value.trim();
        if (!kode) { alert('Masukkan kode laporan terlebih dahulu.'); return; }
        document.getElementById('tracking').classList.remove('hidden');
    }
</script>
@endpush
