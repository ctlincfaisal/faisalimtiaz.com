<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marketing Login</title>
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <script>
        window.tailwind = window.tailwind || {};
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    }
                }
            }
        };

        if (localStorage.getItem('marketing-theme') === 'dark' || (!localStorage.getItem('marketing-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php
    $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500';
    $label = 'mb-2 block text-sm font-medium text-slate-800 dark:text-slate-200';
@endphp

<main class="flex min-h-screen items-center justify-center px-4 py-10">
    <section class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-7">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">Marketing login</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Sign in to open the email console.</p>
        </div>

        @if (session('marketing_login_error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">{{ session('marketing_login_error') }}</div>
        @endif

        <form class="grid gap-5" action="{{ route('marketing.login.submit') }}" method="POST">
            @csrf

            <div>
                <label class="{{ $label }}" for="username">Username</label>
                <input id="username" class="{{ $input }} @error('username') border-red-500 @enderror" type="text" name="username" value="{{ old('username') }}" autocomplete="username" autofocus>
                @error('username')
                    <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="{{ $label }}" for="password">Password</label>
                <input id="password" class="{{ $input }} @error('password') border-red-500 @enderror" type="password" name="password" autocomplete="current-password">
                @error('password')
                    <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                @enderror
            </div>

            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                <i class="bi bi-box-arrow-in-right"></i>
                Login
            </button>
        </form>
    </section>
</main>
</body>

</html>
