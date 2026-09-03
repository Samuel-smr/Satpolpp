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
        <div class="mb-10 max-w-xl mx-auto">
            <div id="stepper" class="relative flex justify-between">
                {{-- Garis latar --}}
                <div id="track-line" class="absolute top-5 h-1 bg-slate-200"></div>
                {{-- Garis progress --}}
                <div id="progress-bar" class="absolute top-5 h-1 bg-brand-500 transition-all duration-300" style="width:0">
                </div>

                @foreach (['Jenis', 'Detail', 'Kirim'] as $i => $label)
                    <div class="relative z-10 flex flex-col items-center">
                        <div id="step-circle-{{ $i + 1 }}"
                            class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold border-2 border-white transition-colors">
                            {{ $i + 1 }}
                        </div>
                        <span id="step-label-{{ $i + 1 }}" class="mt-2 text-xs text-slate-500">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form --}}
        <form id="lapor-form" class="bg-white border border-slate-200 rounded-xl p-6 md:p-8 shadow-sm">
            {{-- STEP 1: Jenis --}}
            <div class="step-content" data-step="1">
                <h2 class="font-headline font-bold text-lg mb-4">Pilih Jenis Pengaduan</h2>

                <input type="hidden" name="jenis" id="jenis" value="">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_radio" value="darurat" class="sr-only peer">
                        <div
                            class="border-2 border-slate-200 rounded-xl p-5 text-center peer-checked:border-accent-500 peer-checked:bg-orange-50 transition-colors">
                            <div class="text-3xl mb-2">🚨</div>
                            <h3 class="font-bold">Lapor Darurat</h3>
                            <p class="text-sm text-slate-500 mt-1">Membutuhkan respons cepat. Contoh: kerusuhan, kebakaran,
                                gangguan ketertiban.</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_radio" value="informasi" class="sr-only peer">
                        <div
                            class="border-2 border-slate-200 rounded-xl p-5 text-center peer-checked:border-brand-500 peer-checked:bg-blue-50 transition-colors">
                            <div class="text-3xl mb-2">📋</div>
                            <h3 class="font-bold">Informasi & Pengaduan</h3>
                            <p class="text-sm text-slate-500 mt-1">Tidak butuh respons darurat. Contoh: PKL liar,
                                kebisingan, ketertiban umum.</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="nextFromJenis()"
                        class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Selanjutnya
                        →</button>
                </div>
            </div>

            {{-- STEP 2: Detail --}}
            <div class="step-content hidden" data-step="2">
                <h2 class="font-headline font-bold text-lg mb-4">Detail Kejadian</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor WhatsApp / Telepon <span
                                class="text-red-500">*</span></label>
                        <input type="tel" name="telepon" required placeholder="Contoh: 081234567890"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">NIK (Opsional)</label>
                        <input type="text" name="nik" placeholder="16 digit NIK jika ada"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi Singkat <span
                                class="text-red-500">*</span></label>
                        <textarea name="deskripsi" rows="4" required
                            placeholder="Jelaskan kejadian secara singkat dan jelas..."
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Lokasi Kejadian <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="lokasi" required placeholder="Alamat / titik lokasi kejadian"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Foto / Video Bukti</label>
                        <label
                            class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-300 rounded-xl p-6 cursor-pointer hover:border-brand-500 bg-slate-50 transition-colors text-center">
                            <input type="file" name="bukti" accept="image/*,video/*" multiple data-handle class="sr-only">
                            <div class="text-3xl mb-2">📎</div>
                            <p class="text-sm font-medium text-slate-600">Klik untuk memilih Foto / Video</p>
                            <p class="text-xs text-slate-400 mt-1">Maks 3 foto atau 1 video pendek</p>
                        </label>
                        <div id="file-list" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                    <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                        <span class="text-red-500">*</span> Catatan: Proses pengerjaan laporan membutuhkan waktu maksimal 3
                        hari kerja.
                    </p>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="goStep(1)"
                        class="border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg font-medium hover:bg-slate-50 transition-colors">←
                        Sebelumnya</button>
                    <button type="button" onclick="nextFromDetail()"
                        class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Selanjutnya
                        →</button>
                </div>
            </div>

            {{-- STEP 3: Kirim --}}
            <div class="step-content hidden" data-step="3">
                <h2 class="font-headline font-bold text-lg mb-4">Kirim</h2>

                <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg p-3 mb-6">
                    <input type="checkbox" id="setuju" name="setuju" required
                        class="h-4 w-4 text-brand-600 focus:ring-brand-500">
                    <label for="setuju" class="text-sm text-slate-600">Saya menyatakan data yang diisi adalah benar dan
                        dapat dipertanggungjawabkan.</label>
                </div>

                <div class="flex justify-between">
                    <button type="button" onclick="goStep(2)"
                        class="border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg font-medium hover:bg-slate-50 transition-colors">←
                        Sebelumnya</button>
                    <button type="submit"
                        class="bg-brand-600 text-white px-8 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors flex items-center gap-2">
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
            <div class="inline-block bg-slate-100 border border-slate-300 rounded-lg px-6 py-3 font-mono text-xl font-bold text-brand-700"
                id="kode-laporan">--</div>
            <p class="mt-4 text-sm text-slate-500">Gunakan kode ini pada menu <a href="{{ route('pelapor.status') }}"
                    class="text-brand-600 underline">Cek Status Laporan</a>.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const STEP_COUNT = 3;
        let currentStep = 1;

        // Posisi center bulatan relatif terhadap container #stepper
        function centerX(step) {
            const container = document.getElementById('stepper').getBoundingClientRect();
            const circle = document.getElementById(`step-circle-${step}`).getBoundingClientRect();
            return circle.left - container.left + circle.width / 2;
        }

        // Gambar garis dan progress agar presisi dengan bulatan
        function updateLines() {
            const track = document.getElementById('track-line');
            const progress = document.getElementById('progress-bar');
            const x1 = centerX(1);
            const x3 = centerX(3);

            track.style.left = x1 + 'px';
            track.style.width = (x3 - x1) + 'px';

            const xt = centerX(currentStep);
            progress.style.left = x1 + 'px';
            progress.style.width = Math.max(0, xt - x1) + 'px';
        }

        function markStep(step) {
            for (let i = 1; i <= STEP_COUNT; i++) {
                const circle = document.getElementById(`step-circle-${i}`);
                const label = document.getElementById(`step-label-${i}`);
                const done = i <= step;
                const active = i === step;

                if (done && active) {
                    // Step aktif saat ini (sedang dikerjakan): isi brand
                    circle.classList.add('bg-brand-600', 'text-white');
                    circle.classList.remove('bg-slate-200', 'text-slate-500', 'bg-emerald-500');
                } else if (done) {
                    // Step selesai: hijau/tercentang
                    circle.classList.add('bg-emerald-500', 'text-white');
                    circle.classList.remove('bg-slate-200', 'text-slate-500', 'bg-brand-600');
                } else {
                    // Belum dikerjakan
                    circle.classList.add('bg-slate-200', 'text-slate-500');
                    circle.classList.remove('bg-brand-600', 'bg-emerald-500', 'text-white');
                }

                label.classList.toggle('text-brand-600', active);
                label.classList.toggle('text-slate-500', !active);
            }
            updateLines();
        }

        function goStep(step) {
            currentStep = step;
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.querySelector(`.step-content[data-step="${step}"]`).classList.remove('hidden');
            markStep(step);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Radio Jenis: set hidden & trigger bulatan step 1 langsung aktif (tercentang)
        document.querySelectorAll('input[name="jenis_radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.getElementById('jenis').value = radio.value;
                // Step 1 dianggap selesai -> bulatan langsung ter-trigger
                if (currentStep === 1) {
                    markStep(1);
                }
            });
        });

        function nextFromJenis() {
            const jen = document.querySelector('input[name="jenis_radio"]:checked');
            if (!jen) {
                alert('Pilih jenis pengaduan terlebih dahulu.');
                return;
            }
            document.getElementById('jenis').value = jen.value;
            markStep(1); // pastikan step 1 ter-trigger
            goStep(2);
        }

        // Validasi step 2 (field wajib): telepon, deskripsi, lokasi
        function nextFromDetail() {
            const required = ['telepon', 'deskripsi', 'lokasi'];
            const labels = {
                telepon: 'Nomor WhatsApp / Telepon',
                deskripsi: 'Deskripsi Singkat',
                lokasi: 'Lokasi Kejadian',
            };

            const empty = required.find(name => {
                const el = document.querySelector(`[name="${name}"]`);
                return !el || !el.value.trim();
            });

            if (empty) {
                const el = document.querySelector(`[name="${empty}"]`);
                el.focus();
                el.classList.add('border-red-500');
                alert('Mohon lengkapi: ' + labels[empty]);
                setTimeout(() => el.classList.remove('border-red-500'), 2000);
                return;
            }

            goStep(3);
        }

        // Recalc saat layout berubah
        window.addEventListener('resize', updateLines);
        window.addEventListener('load', updateLines);

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

        // Submit: simpan laporan ke localStorage (front-end, tanpa database)
        document.getElementById('lapor-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const kode = 'SPJ-' + Date.now().toString().slice(-6);

            const report = {
                kode: kode,
                jenis: document.getElementById('jenis').value || 'informasi',
                deskripsi: document.querySelector('textarea[name="deskripsi"]').value,
                lokasi: document.querySelector('input[name="lokasi"]').value,
                telepon: document.querySelector('input[name="telepon"]').value,
                nik: document.querySelector('input[name="nik"]').value,
                dibuat: new Date().toLocaleString('id-ID'),
                statusIdx: 0, // baru dibuat -> "Laporan dibuat"
            };

            const reports = JSON.parse(localStorage.getItem('sipraja_reports') || '[]');
            reports.unshift(report);
            localStorage.setItem('sipraja_reports', JSON.stringify(reports));

            document.getElementById('kode-laporan').textContent = kode;
            document.getElementById('lapor-form').classList.add('hidden');
            document.getElementById('result').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
@endpush