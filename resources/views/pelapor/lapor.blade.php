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

                @foreach (['Detail', 'Kirim'] as $i => $label)
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
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="lapor-form" action="{{ route('pelapor.store') }}" method="POST" enctype="multipart/form-data" novalidate class="{{ session('report_code') ? 'hidden ' : '' }}bg-white border border-slate-200 rounded-xl p-6 md:p-8 shadow-sm">
            @csrf
            <input type="hidden" name="jenis" value="lapor">

            {{-- STEP 1: Detail --}}
            <div class="step-content" data-step="1">
                <h2 class="font-headline font-bold text-lg mb-4">Detail Kejadian</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor WhatsApp / Telepon <span
                                class="text-red-500">*</span></label>
                        <input type="tel" name="telepon" required inputmode="tel" maxlength="20" pattern="\+?[0-9][0-9\s-]{8,18}" title="Masukkan 9–19 digit; boleh diawali + serta menggunakan spasi atau tanda hubung." placeholder="Contoh: 081234567890"
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
                            <input type="file" name="bukti[]" accept="image/jpeg,image/png,image/webp" multiple data-handle class="sr-only">
                            <div class="text-3xl mb-2">📎</div>
                            <p class="text-sm font-medium text-slate-600">Klik untuk memilih Foto / Video</p>
                            <p class="text-xs text-slate-400 mt-1">Maks 3 foto, masing-masing maks 5 MB (JPG, PNG, atau WEBP)</p>
                        </label>
                        <div id="file-list" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                    <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                        <span class="text-red-500">*</span> Catatan: Proses pengerjaan laporan membutuhkan waktu maksimal 3
                        hari kerja.
                    </p>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="nextFromDetail()"
                        class="bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Selanjutnya
                        →</button>
                </div>
            </div>

            {{-- STEP 2: Kirim --}}
            <div class="step-content hidden" data-step="2">
                <h2 class="font-headline font-bold text-lg mb-4">Kirim</h2>

                <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg p-3 mb-6">
                    <input type="checkbox" id="setuju" name="setuju" required
                        class="h-4 w-4 text-brand-600 focus:ring-brand-500">
                    <label for="setuju" class="text-sm text-slate-600">Saya menyatakan data yang diisi adalah benar dan
                        dapat dipertanggungjawabkan.</label>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button type="button" onclick="goStep(1)"
                        class="w-full border border-slate-300 text-slate-600 px-6 py-2.5 rounded-lg sm:w-auto font-medium hover:bg-slate-50 transition-colors">←
                        Sebelumnya</button>
                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-8 py-2.5 font-medium text-white transition-colors hover:bg-brand-700 sm:w-auto">
                        Kirim Laporan
                    </button>
                </div>
            </div>
        </form>

        <button type="button" id="ai-assistant-toggle"
            class="fixed bottom-5 right-5 z-40 flex items-center gap-2 rounded-full bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-xl transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
            aria-controls="ai-assistant-panel" aria-expanded="false">
            <span class="material-symbols-outlined text-[20px]">smart_toy</span>
            Tanya Asisten AI
        </button>

        <section id="ai-assistant-panel"
            class="fixed bottom-20 left-4 right-4 z-50 hidden w-auto max-w-md sm:left-auto sm:w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
            aria-label="Asisten AI SIPRAJA">
            <header class="flex items-center justify-between bg-brand-600 px-4 py-3 text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">smart_toy</span>
                    <div>
                        <h2 class="text-sm font-bold">Asisten SIPRAJA</h2>
                        <p class="text-xs text-blue-100">Panduan membuat laporan</p>
                    </div>
                </div>
                <button type="button" id="ai-assistant-close"
                    class="rounded-full p-1 text-blue-100 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup asisten AI">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </header>

            <div id="ai-assistant-messages" class="flex h-72 flex-col gap-3 overflow-y-auto bg-slate-50 p-4"
                aria-live="polite">
                <div class="max-w-[85%] self-start rounded-2xl rounded-bl-sm border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                    Halo! Saya dapat menjelaskan cara melapor atau membantu menyusun deskripsi kejadian. Jangan kirim NIK atau data sensitif.
                </div>
            </div>

            <div class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-3">
                <button type="button" data-ai-question="Bagaimana cara membuat laporan?"
                    class="shrink-0 rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                    Cara melapor
                </button>
                <button type="button" data-ai-question="Bantu saya menyusun deskripsi laporan yang baik."
                    class="shrink-0 rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                    Bantu deskripsi
                </button>
            </div>

            <form id="ai-assistant-form" class="flex items-end gap-2 border-t border-slate-200 bg-white p-3">
                <label for="ai-assistant-input" class="sr-only">Pertanyaan untuk asisten AI</label>
                <textarea id="ai-assistant-input" rows="1" maxlength="600"
                    placeholder="Tulis pertanyaan..."
                    class="max-h-28 min-h-10 flex-1 resize-none rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                <button type="submit" id="ai-assistant-send"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Kirim pertanyaan">
                    <span class="material-symbols-outlined text-[20px]">send</span>
                </button>
            </form>
            <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-400">
                Jawaban AI dapat keliru. Periksa kembali sebelum mengirim laporan.
            </p>
        </section>

        {{-- Hasil Kode Laporan --}}
        <div id="result" class="{{ session('report_code') ? '' : 'hidden ' }}mt-8 bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <div class="text-5xl mb-4">✅</div>
            <h2 class="font-headline text-lg font-bold mb-2">Laporan Terkirim!</h2>
            <p class="text-slate-500 mb-4">Simpan kode laporan Anda untuk memantau status:</p>
            <div class="inline-block bg-slate-100 border border-slate-300 rounded-lg px-6 py-3 font-mono text-xl font-bold text-brand-700"
                id="kode-laporan">{{ session('report_code', '--') }}</div>
            <p class="mt-4 text-sm text-slate-500">Gunakan kode ini pada menu <a href="{{ route('pelapor.status') }}"
                    class="text-brand-600 underline">Cek Status Laporan</a>.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const STEP_COUNT = 2;
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
            const x2 = centerX(2);

            track.style.left = x1 + 'px';
            track.style.width = (x2 - x1) + 'px';

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

        // Validasi detail laporan sebelum tahap kirim.
        function nextFromDetail() {
            const detailStep = document.querySelector('.step-content[data-step="1"]');
            const invalidField = Array.from(detailStep.querySelectorAll('input, textarea, select'))
                .find(field => !field.checkValidity());

            if (invalidField) {
                invalidField.reportValidity();
                invalidField.focus();
                return;
            }

            goStep(2);
        }

        document.getElementById('lapor-form').addEventListener('submit', function (event) {
            const invalidField = Array.from(this.elements)
                .find(field => field.willValidate && !field.checkValidity());

            if (!invalidField) {
                return;
            }

            event.preventDefault();
            const invalidStep = invalidField.closest('.step-content')?.dataset.step;

            if (invalidStep) {
                goStep(Number(invalidStep));
            }

            requestAnimationFrame(() => {
                invalidField.focus();
                invalidField.reportValidity();
            });
        });

        // Recalc saat layout berubah
        window.addEventListener('resize', updateLines);
        window.addEventListener('load', updateLines);

        // File upload preview (simple)
        document.querySelector('input[data-handle]').addEventListener('change', function (e) {
            const list = document.getElementById('file-list');
            const MAX_SIZE = 5 * 1024 * 1024;
            const oversized = Array.from(e.target.files).filter((file) => file.size > MAX_SIZE);

            if (oversized.length > 0) {
                alert('Terdapat file berukuran lebih dari 5 MB. Masing-masing bukti maksimal 5 MB.');
                e.target.value = '';
                list.innerHTML = '';
                return;
            }

            list.innerHTML = '';
            Array.from(e.target.files).forEach((file) => {
                const div = document.createElement('div');
                div.className = 'border border-slate-200 rounded-lg overflow-hidden h-24 flex items-center justify-center bg-slate-100 text-xs text-slate-500 p-1';
                div.textContent = file.name;
                list.appendChild(div);
            });
        });

        const aiPanel = document.getElementById('ai-assistant-panel');
        const aiToggle = document.getElementById('ai-assistant-toggle');
        const aiClose = document.getElementById('ai-assistant-close');
        const aiForm = document.getElementById('ai-assistant-form');
        const aiInput = document.getElementById('ai-assistant-input');
        const aiSend = document.getElementById('ai-assistant-send');
        const aiMessages = document.getElementById('ai-assistant-messages');

        function setAiPanel(open) {
            aiPanel.classList.toggle('hidden', !open);
            aiToggle.setAttribute('aria-expanded', String(open));
            if (open) {
                aiInput.focus();
            }
        }

        function appendAiMessage(text, role, canUseAsDescription = false) {
            const wrapper = document.createElement('div');
            wrapper.className = role === 'user'
                ? 'max-w-[85%] self-end rounded-2xl rounded-br-sm bg-brand-600 px-4 py-3 text-sm text-white'
                : 'max-w-[85%] self-start rounded-2xl rounded-bl-sm border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700';

            const content = document.createElement('p');
            content.className = 'whitespace-pre-wrap';
            content.textContent = text;
            wrapper.appendChild(content);

            if (canUseAsDescription) {
                const useButton = document.createElement('button');
                useButton.type = 'button';
                useButton.className = 'mt-3 text-xs font-semibold text-brand-600 hover:underline';
                useButton.textContent = 'Gunakan sebagai deskripsi';
                useButton.addEventListener('click', () => {
                    const description = document.querySelector('textarea[name="deskripsi"]');
                    description.value = text;
                    description.dispatchEvent(new Event('input', { bubbles: true }));
                    setAiPanel(false);
                    description.focus();
                });
                wrapper.appendChild(useButton);
            }

            aiMessages.appendChild(wrapper);
            aiMessages.scrollTop = aiMessages.scrollHeight;
            return wrapper;
        }

        async function askAi(question) {
            const message = question.trim();
            if (!message || aiSend.disabled) {
                return;
            }

            const canUseAsDescription = /\b(susun|buatkan|draf|draft|deskripsi)\b/i.test(message);
            const abortController = new AbortController();
            const requestTimeout = setTimeout(() => abortController.abort(), 27000);
            appendAiMessage(message, 'user');
            aiInput.value = '';
            aiSend.disabled = true;

            const loading = appendAiMessage('Sedang menyiapkan jawaban...', 'model');

            try {
                const response = await fetch(@json(route('pelapor.ai-assistant')), {
                    method: 'POST',
                    signal: abortController.signal,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        message,
                    }),
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Asisten AI sedang tidak tersedia.');
                }

                loading.remove();
                appendAiMessage(result.answer, 'model', canUseAsDescription);
            } catch (error) {
                loading.remove();
                const errorMessage = error.name === 'AbortError'
                    ? 'Respons AI terlalu lama. Silakan coba kirim kembali.'
                    : (error.message || 'Asisten AI sedang tidak tersedia. Silakan coba lagi.');
                appendAiMessage(errorMessage, 'model');
            } finally {
                clearTimeout(requestTimeout);
                aiSend.disabled = false;
                aiInput.focus();
            }
        }

        aiToggle.addEventListener('click', () => setAiPanel(aiPanel.classList.contains('hidden')));
        aiClose.addEventListener('click', () => setAiPanel(false));
        aiForm.addEventListener('submit', (event) => {
            event.preventDefault();
            askAi(aiInput.value);
        });
        aiInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                aiForm.requestSubmit();
            }
        });
        document.querySelectorAll('[data-ai-question]').forEach((button) => {
            button.addEventListener('click', () => askAi(button.dataset.aiQuestion));
        });

    </script>
@endpush
