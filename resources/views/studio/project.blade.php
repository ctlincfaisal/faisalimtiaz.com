@extends('studio.app')

@section('title', $project['name'].' — Case Study · Faisal Imtiaz')
@section('meta_description', $project['desc'])

@section('head')
<style>
    .showcase-glow {
        background: radial-gradient(ellipse at 50% 45%, rgba(200, 240, 62, 0.10), rgba(200, 240, 62, 0.02) 45%, transparent 65%);
    }
    .tech-chip {
        border: 1px solid rgba(var(--line), 0.12);
        background: rgb(var(--surface));
    }
</style>
@endsection

@section('content')

@php
    $isExternal = fn ($url) => str_starts_with($url, 'http');
    $backHref = route('studio').'#work';
@endphp

<header class="site-header sticky top-0 z-40" id="site-header">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-10 lg:px-6">
        <a href="{{ route('studio') }}" class="text-sm font-black tracking-tight text-paper">
            Faisal<span class="text-accent">.</span>Imtiaz
        </a>
        <nav class="flex items-center gap-6 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
            <a href="{{ $backHref }}" class="transition-colors hover:text-paper">← Work</a>
            <a href="{{ route('aboutme') }}#contact" class="transition-colors hover:text-paper">Contact</a>
        </nav>
    </div>
</header>

<!-- ========== PROJECT HERO ========== -->
<section class="px-5 pb-20 pt-14 sm:px-10 lg:px-6">
    <div class="mx-auto max-w-7xl">
        <a href="{{ $backHref }}"
           class="mb-10 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-smoke transition-colors hover:text-paper">
            <span aria-hidden="true">←</span> Back to Selected Work
        </a>

        <div class="grid items-center gap-12 lg:grid-cols-12">
            <!-- Image -->
            <div class="lg:col-span-7">
                <div class="relative">
                    <div class="showcase-glow pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
                    <div class="overflow-hidden rounded-3xl bg-surface ring-1 ring-line">
                        <img src="{{ url($project['image']) }}"
                             alt="{{ $project['name'] }}"
                             class="aspect-[4/3] w-full object-cover">
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="lg:col-span-5">
                <span class="inline-flex rounded-full border border-line bg-surface px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-paper/80">
                    {{ $project['category'] }}
                </span>

                <h1 class="project-title mt-6 text-paper">{{ $project['name'] }}</h1>
                <p class="mt-2 text-sm font-semibold uppercase tracking-[0.2em] text-accent">{{ $project['tag'] }}</p>

                <p class="mt-8 text-base leading-relaxed text-smoke">{{ $project['desc'] }}</p>
                <p class="mt-4 text-base leading-relaxed text-smoke">{{ $project['long_desc'] }}</p>

                <!-- Tech stack -->
                <div class="mt-8">
                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.3em] text-smoke">Tech Stack</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($project['tech'] as $tech)
                            <span class="tech-chip rounded-full px-4 py-2 text-xs font-semibold text-paper">{{ $tech }}</span>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-10 flex flex-wrap items-center gap-4">
                    @if (!empty($project['live']))
                        <a href="{{ $project['live'] }}"
                           target="_blank" rel="noopener"
                           class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white"
                           data-magnetic>
                            {{ $project['live_label'] ?? 'Visit live project' }}
                            <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">↗</span>
                        </a>
                    @endif

                    <a href="{{ $project['href'] }}"
                       @if ($isExternal($project['href'])) target="_blank" rel="noopener" @endif
                       class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-8 py-4 text-sm font-semibold text-paper transition-colors duration-300 hover:border-accent hover:text-accent">
                        {{ !empty($project['live']) ? 'Enquire about a similar project' : 'Start a conversation' }}
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== MORE WORK ========== -->
<section class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-10 flex items-end justify-between">
            <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">More Work</h2>
            <a href="{{ $backHref }}" class="text-xs font-semibold uppercase tracking-[0.25em] text-smoke transition-colors hover:text-paper">
                View all →
            </a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (collect(config('projects'))->where('slug', '!=', $project['slug'])->take(3) as $related)
                <a href="{{ route('studio.work', $related['slug']) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl bg-surface transition-colors duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]">
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <img src="{{ url($related['image']) }}"
                             alt="{{ $related['name'] }}"
                             loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-lg font-black tracking-tight text-paper">{{ $related['name'] }}</h3>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-accent">{{ $related['category'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
    (function () {
        const header = document.getElementById('site-header');
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 10);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        document.querySelectorAll('[data-magnetic]').forEach((btn) => {
            btn.addEventListener('mousemove', (e) => {
                const r = btn.getBoundingClientRect();
                const x = (e.clientX - r.left - r.width / 2) * 0.15;
                const y = (e.clientY - r.top - r.height / 2) * 0.15;
                btn.style.transform = `translate(${x}px, ${y}px)`;
            });
            btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
        });
    })();
</script>
@endsection
