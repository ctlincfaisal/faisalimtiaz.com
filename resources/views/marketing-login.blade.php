@extends('marketing.auth-layout')

@section('title', 'Log in')
@section('meta_description', 'Log in to your MyLeadTrail workspace.')
@section('canonical', route('marketing.login'))

@section('auth_content')
<div class="auth-card-heading">
    <h2>Welcome back</h2>
    <p>Sign in to continue to your MyLeadTrail workspace.</p>
</div>

@if (session('marketing_login_error'))
    <div class="auth-alert" role="alert">{{ session('marketing_login_error') }}</div>
@endif

<form class="auth-form" action="{{ route('marketing.login.submit') }}" method="POST">
    @csrf

    <div class="auth-field">
        <label class="auth-label" for="identifier">Email or username</label>
        <input id="identifier" class="auth-input @error('identifier') auth-invalid @enderror" type="text" name="identifier" value="{{ old('identifier') }}" placeholder="you@example.com" autocomplete="username" autofocus>
        @error('identifier')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="password">Password</label>
        <input id="password" class="auth-input @error('password') auth-invalid @enderror" type="password" name="password" placeholder="Enter your password" autocomplete="current-password">
        @error('password')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <button class="auth-submit" type="submit"><i class="bi bi-box-arrow-in-right"></i> Log in to MyLeadTrail</button>
</form>

<p class="auth-switch">New to MyLeadTrail? <a href="{{ route('marketing.register') }}">Create a free account</a></p>
@endsection
