@extends('layouts.admin')

@section('title', 'Feedback Masyarakat')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Feedback &amp; Kepuasan Masyarakat</h1>
            <p class="text-sm text-slate-500 mt-1">Ulasan, tingkat kepuasan, dan saran dari warga terhadap penanganan laporan.</p>
        </div>
    </div>

    {{-- Ringkasan Kepuasan --}}
    @php
        $summaries = [
            ['label' => 'Total Feedback', 'value' => $feedbackSummary['total'], 'icon' => 'chat', 'color' => 'text-orange-600 bg-orange-100/70'],
            ['label' => 'Rata-rata Rating', 'value' => $feedbackSummary['average'].' / 5', 'icon' => 'star', 'color' => 'text-amber-600 bg-amber-100/70'],
            ['label' => 'Sangat Puas (5★)', 'value' => $feedbackSummary['five_star'], 'icon' => 'sentiment_very_satisfied', 'color' => 'text-emerald-600 bg-emerald-100/70'],
            ['label' => 'Perlu Perbaikan (&lt;3★)', 'value' => $feedbackSummary['low_rating'], 'icon' => 'sentiment_dissatisfied', 'color' => 'text-red-600 bg-red-100/70'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach ($summaries as $s)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 leading-snug">{{ $s['label'] }}</p>
                        <p class="font-headline text-3xl font-bold text-slate-900 mt-1">{{ $s['value'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl {{ $s['color'] }} flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">{{ $s['icon'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Daftar Feedback --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Feedback Pelapor</span>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">Database</span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($feedbacks as $f)
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-800">{{ $f->code }}</span>
                                <span class="text-xs text-slate-400">{{ $f->feedback_submitted_at?->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-amber-400 text-sm">
                                    @for ($i = 1; $i <= 5; $i++)
                                        {{ $i <= $f->feedback_rating ? '★' : '☆' }}
                                    @endfor
                                </span>
                                <span class="text-xs text-slate-400">({{ $f->feedback_rating }}/5)</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="text-sm font-medium text-slate-700">{{ $f->category?->label() ?? '-' }}</p>
                        <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ $f->feedback_comment ?: '-' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($feedbacks->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $feedbacks->links() }}</div>
        @endif
    </div>
@endsection
