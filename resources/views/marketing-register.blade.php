@extends('marketing.auth-layout')

@section('title', 'Create your account')
@section('meta_description', 'Create a free MyLeadTrail account.')
@section('canonical', route('marketing.register'))

@section('auth_content')
<div class="auth-card-heading">
    <h2>Start for free</h2>
    <p>Create your workspace and bring your outreach together.</p>
</div>

<form class="auth-form" action="{{ route('marketing.register.submit') }}" method="POST">
    @csrf

    <div class="auth-field">
        <label class="auth-label" for="name">Your name</label>
        <input id="name" class="auth-input @error('name') auth-invalid @enderror" type="text" name="name" value="{{ old('name') }}" placeholder="Faisal Imtiaz" required autofocus autocomplete="name">
        @error('name')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="email">Email address</label>
        <input id="email" class="auth-input @error('email') auth-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
        @error('email')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="password">Password</label>
        <input id="password" class="auth-input @error('password') auth-invalid @enderror" type="password" name="password" placeholder="At least 8 characters" required autocomplete="new-password">
        @error('password')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password">
    </div>

    <button class="auth-submit" type="submit"><i class="bi bi-arrow-right"></i> Create free account</button>
</form>

<p class="auth-legal">By creating an account, you agree to use MyLeadTrail responsibly and respectfully.</p>
<p class="auth-switch">Already have an account? <a href="{{ route('marketing.login') }}">Log in</a></p>
@endsection
