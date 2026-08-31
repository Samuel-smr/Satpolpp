@extends('layouts.pelapor')

@section('title', 'Buat Pengaduan')

@section('content')
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Buat Pengaduan</h1>
            <p class="mt-2 text-slate-500">Sampaikan laporan Anda dengan jelas dan lengkap.</p>
        </div>

        {{-- Stepper --}}
        <div class="flex justify-between items-center mb-8 max-w-xl mx-auto relative">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200"></div>
            <div id="progress-bar" class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-500 transition-all duration-300" style="width:0%"></div>

            @foreach (['Jenis', 'Detail', 'Kirim'] as $i => $label)
                <div class="relative z-10 flex flex-col items-center" id="step-ind-{{ $i + 1 }}">
                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold border-2 border-white mb-2 transition-colors">
                        {{ $i + 1 }}
                    </div>
                    <span class="text-xs text-slate-500">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        {{-- Form --}}
        <form id="lapor-form" class="bg-white border border-slate-200 rounded-xl p-6 md:p-8 shadow-sm">
            {{-- STEP 1: Jenis --}}
            <div class="step-content" data-step="1">
                <h2 class="font-headline font-bold text-lg mb-4">Pilih Jenis Pengaduan</h2>

                <input type="hidden" name="jenis" id="jenis" value="darurat">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_radio" value="darurat" class="sr-only peer" checked>
                        <div class="border-2 border-slate-200 rounded-xl p-5 text-center peer-checked:border-accent-500 peer-checked:bg-orange-50 transition-colors">
                            <div class="text-3xl mb-2">🚨</div>
                            <h3 class="font-bold">Lapor Darurat</h3>
                            <p class="text-sm text-slate-500 mt-1">Membutuhkan respons cepat. Contoh: kerusuhan, kebakaran, gangguan ketertiban.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_radio" value="informasi" class="sr-only peer">
                        <div class="border-2 border-slate-200 rounded-xl p-5 text-center peer-checked:border-brand-500 peer-checked:bg-blue-50 transition-colors">
                            <div class="text-3xl mb-2">📋</div>
                            <h3 class="font-bold">Informasi & Pengaduan</h3>
                            <p class="text-sm text-slate-500 mt-1">Tidak butuh respons darurat. Contoh: PKL liar, kebisingan, ketertiban umum.</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="goStep(2)" class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Selanjutnya →</button>
                </div>
            </div>

            {{-- STEP 2: Detail --}}
            <div class="step-content hidden" data-step="2">
                <h2 class="font-headline font-bold text-lg mb-4">Detail Kejadian</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor WhatsApp / Telepon <span class="text-red-500">*</span></label>
                        <input type="tel" name="telepon" required placeholder="Contoh: 081234567890"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">NIK (Opsional)</label>
                        <input type="text" name="nik" placeholder="16 digit NIK jika ada"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Judul / Jenis Kejadian <span class="text-red-500">*</span></label>
                        <select name="kategori" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                            <option value="">Pilih Kategori</option>
                            <option>Ketertiban Umum</option>
                            <option>Keamanan</option>
                            <option>Patroli</option>
                            <option>Pengawalan</option>
                            <option>Kebersihan Lingkungan</option>
                            <option>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi Singkat <span class="text-red-500">*</span></label>
                        <textarea name="deskripsi" rows="4" required placeholder="Jelaskan kejadian secara singkat dan jelas..."
                                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Lokasi Kejadian <span class="text-red-500">*</span></label>
                        <input type="text" name="lokasi" required placeholder="Alamat / titik lokasi kejadian"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="goStep(1)" class="border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg font-medium hover:bg-slate-50 transition-colors">← Sebelumnya</button>
                    <button type="button" onclick="goStep(3)" class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Selanjutnya →</button>
                </div>
            </div>

            {{-- STEP 3: Upload & Kirim --}}
            <div class="step-content hidden" data-step="3">
                <h2 class="font-headline font-bold text-lg mb-4">Upload Bukti & Kirim</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Foto / Video Bukti</label>
                    <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-300 rounded-xl p-6 cursor-pointer hover:border-brand-500 bg-slate-50 transition-colors text-center">
                        <input type="file" name="bukti" accept="image/*,video/*" multiple data-handle class="sr-only">
                        <div class="text-3xl mb-2">📎</div>
                        <p class="text-sm font-medium text-slate-600">Klik untuk memilih Foto / Video</p>
                        <p class="text-xs text-slate-400 mt-1">Maks 3 foto atau 1 video pendek</p>
                    </label>
                    <div id="file-list" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg p-3 mb-6">
                    <input type="checkbox" id="setuju" name="setuju" required class="h-4 w-4 text-brand-600 focus:ring-brand-500">
                    <label for="setuju" class="text-sm text-slate-600">Saya menyatakan data yang diisi adalah benar dan dapat dipertanggungjawabkan.</label>
                </div>

                <div class="flex justify-between">
                    <button type="button" onclick="goStep(2)" class="border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg font-medium hover:bg-slate-50 transition-colors">← Sebelumnya</button>
                    <button type="submit" class="bg-brand-600 text-white px-8 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors flex items-center gap-2">
                        Kirim Laporan
                    </button>
                </div>
            </div>
        </form>

        {{-- Hasil Kode Laporan --}}
        <div id="result" class="hidden mt-8 bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <div class="text-5xl mb-4">✅</div>
            <h2 class="font-headline text-lg font-bold mb-2">Laporan Terkirim!</h2>
            <p class="text-slate-500 mb-4">Simpan kode laporan Anda untuk memantau status:</p>
            <div class="inline-block bg-slate-100 border border-slate-300 rounded-lg px-6 py-3 font-mono text-xl font-bold text-brand-700" id="kode-laporan">--</div>
            <p class="mt-4 text-sm text-slate-500">Gunakan kode ini pada menu <a href="{{ route('pelapor.status') }}" class="text-brand-600 underline">Cek Status Laporan</a>.</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const STEP_COUNT = 3;

    // Radio Jenis -> set hidden input
    document.querySelectorAll('input[name="jenis_radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('jenis').value = radio.value;
        });
    });

    function goStep(step) {
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.querySelector(`.step-content[data-step="${step}"]`).classList.remove('hidden');

        document.getElementById('progress-bar').style.width = ((step - 1) / (STEP_COUNT - 1) * 100) + '%';

        for (let i = 1; i <= STEP_COUNT; i++) {
            const circle = document.querySelector(`#step-ind-${i} > div`);
            const label = document.querySelector(`#step-ind-${i} > span`);
            const done = i <= step;
            circle.classList.toggle('bg-brand-600', done);
            circle.classList.toggle('text-white', done);
            circle.classList.toggle('bg-slate-200', !done);
            circle.classList.toggle('text-slate-500', !done);
            label.classList.toggle('text-brand-600', i === step);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // File upload preview (simple)
    document.querySelector('input[data-handle]').addEventListener('change', function (e) {
        const list = document.getElementById('file-list');
        list.innerHTML = '';
        Array.from(e.target.files).forEach((file) => {
            const div = document.createElement('div');
            div.className = 'border border-slate-200 rounded-lg overflow-hidden h-24 flex items-center justify-center bg-slate-100 text-xs text-slate-500 p-1';
            div.textContent = file.name;
            list.appendChild(div);
        });
    });

    // Submit (front-end only, tanpa database)
    document.getElementById('lapor-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const kode = 'SPJ-' + Date.now().toString().slice(-6);
        document.getElementById('kode-laporan').textContent = kode;
        document.getElementById('lapor-form').classList.add('hidden');
        document.getElementById('result').classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endpush
