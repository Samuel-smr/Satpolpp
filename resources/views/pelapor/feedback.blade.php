@extends('layouts.pelapor')

@section('title', 'Kepuasan Masyarakat')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Kepuasan Masyarakat</h1>
            <p class="mt-2 text-slate-500">Bagaimana kepuasan Anda terhadap layanan kami?</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-6 md:p-8 shadow-sm">
            <form id="feedback-form">
                <div id="sukses" class="hidden text-center py-6">
                    <div class="text-5xl mb-4">🙏</div>
                    <h2 class="font-headline font-bold text-lg mb-2">Terima Kasih!</h2>
                    <p class="text-slate-500">Masukan Anda sangat berarti bagi kami untuk terus meningkatkan layanan.</p>
                </div>

                <div id="form-body">
                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-1">Kode Laporan</label>
                        <input type="text" placeholder="Masukkan kode laporan (opsional)"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-2">Penilaian</label>
                        <div class="flex gap-2 justify-center text-3xl" id="rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="rating-star grayscale hover:scale-110 transition-transform" data-value="{{ $i }}">⭐</button>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-1">Komentar / Saran</label>
                        <textarea rows="4" placeholder="Tulis komentar atau saran Anda..."
                                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" data-handle></textarea>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 text-white py-3 rounded-lg font-medium hover:bg-brand-700 transition-colors">Kirim Feedback</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let selected = 0;
    const stars = document.querySelectorAll('.rating-star');

    function render(hover) {
        const active = hover || selected;
        stars.forEach(s => {
            s.classList.toggle('grayscale', +s.dataset.value > active);
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseenter', () => render(+star.dataset.value));
        star.addEventListener('mouseleave', () => render(0));
        star.addEventListener('click', () => {
            selected = +star.dataset.value;
            render(0);
        });
    });

    // Default kosong
    render(0);

    document.getElementById('feedback-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!selected) { alert('Silakan pilih penilaian bintang.'); return; }
        document.getElementById('form-body').classList.add('hidden');
        document.getElementById('sukses').classList.remove('hidden');
    });
</script>
@endpush
