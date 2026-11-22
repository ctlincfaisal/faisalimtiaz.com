@php
    $mobileNavVariant = $variant ?? 'default';
    $mobileNavLinks = $mobileNavVariant === 'about'
        ? [
            ['label' => 'Home', 'href' => route('studio')],
            ['label' => 'About', 'href' => '#about'],
            ['label' => 'Education', 'href' => '#education'],
            ['label' => 'Experience', 'href' => '#experience'],
            ['label' => 'Skills', 'href' => '#skills'],
            ['label' => 'FAQ', 'href' => '#faq'],
            ['label' => 'Contact', 'href' => '#contact'],
        ]
        : [
            ['label' => 'Home', 'href' => route('studio')],
            ['label' => 'Work', 'href' => route('studio').'#work'],
            ['label' => 'About', 'href' => route('aboutme')],
            ['label' => 'Contact', 'href' => route('aboutme').'#contact'],
        ];
@endphp

<div id="site-mobile-nav" data-site-mobile-nav class="hidden border-t border-line bg-ink/95 backdrop-blur-md md:hidden">
    <ul class="mx-auto flex max-w-7xl flex-col gap-1 px-5 py-4 sm:px-10">
        @foreach ($mobileNavLinks as $link)
            <li>
                <a href="{{ $link['href'] }}"
                   data-site-nav-link
                   class="block rounded-lg px-4 py-3 text-sm font-semibold text-paper/80 transition-colors hover:bg-surface hover:text-paper">
                    {{ $link['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
