<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Marketing Dashboard</title>
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/theme.min.css') }}">
<style>
    body {
        font-family: Poppins, sans-serif;
    }

    .marketing-shell {
        background: #f6f8fb;
        min-height: 100vh;
        padding: 32px 0 56px;
    }

    .marketing-layout {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        gap: 24px;
    }

    .marketing-sidebar,
    .marketing-panel,
    .marketing-stat,
    .marketing-contact {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 8px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
    }

    .marketing-sidebar {
        align-self: start;
        padding: 16px;
        position: sticky;
        top: 96px;
    }

    .marketing-nav {
        display: grid;
        gap: 8px;
    }

    .marketing-nav a {
        align-items: center;
        border-radius: 8px;
        color: #334155;
        display: flex;
        font-weight: 600;
        gap: 10px;
        padding: 12px 14px;
    }

    .marketing-nav a:hover,
    .marketing-nav a.active {
        background: #ecfdf5;
        color: #047857;
        text-decoration: none;
    }

    .marketing-panel {
        padding: 28px;
    }

    .marketing-title {
        color: #0f172a;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .marketing-subtitle {
        color: #64748b;
        margin-bottom: 24px;
    }

    .marketing-stats {
        display: grid;
        gap: 18px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 26px;
    }

    .marketing-stat {
        padding: 22px;
    }

    .marketing-stat span {
        color: #64748b;
        display: block;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .marketing-stat strong {
        color: #0f172a;
        display: block;
        font-size: 36px;
        line-height: 1;
    }

    .marketing-recent {
        border-top: 1px solid #e7ecf3;
        padding-top: 22px;
    }

    .marketing-recent-item,
    .marketing-contact {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 0;
    }

    .marketing-recent-item + .marketing-recent-item {
        border-top: 1px solid #eef2f7;
    }

    .marketing-form {
        display: grid;
        gap: 18px;
    }

    .marketing-form label {
        color: #0f172a;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .marketing-form textarea {
        min-height: 170px;
        resize: vertical;
    }

    .marketing-actions {
        display: flex;
        justify-content: flex-end;
    }

    .marketing-contacts {
        display: grid;
        gap: 10px;
    }

    .marketing-contact {
        padding: 14px 16px;
    }

    .marketing-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        padding: 24px;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .marketing-layout,
        .marketing-stats {
            grid-template-columns: 1fr;
        }

        .marketing-sidebar {
            position: static;
        }
    }
</style>
</head>

<body>
<section class="marketing-shell">
    <div class="container">
        @if (session('marketing_success'))
            <div class="alert alert-success" role="alert">{{ session('marketing_success') }}</div>
        @endif

        @if (session('marketing_error'))
            <div class="alert alert-danger" role="alert">{{ session('marketing_error') }}</div>
        @endif

        @if (! $databaseReady)
            <div class="alert alert-warning" role="alert">
                Marketing storage is not ready yet. Run the migration after your database credentials are configured.
            </div>
        @endif

        <div class="marketing-layout">
            <aside class="marketing-sidebar">
                <nav class="marketing-nav" aria-label="Marketing dashboard">
                    <a class="{{ $activeTab === 'dashboard' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'dashboard']) }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                    <a class="{{ $activeTab === 'send' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'send']) }}">
                        <i class="bi bi-send"></i>
                        Send new email
                    </a>
                    <a class="{{ $activeTab === 'contacts' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'contacts']) }}">
                        <i class="bi bi-people"></i>
                        My contacts
                    </a>
                </nav>
            </aside>

            <div class="marketing-panel">
                @if ($activeTab === 'dashboard')
                    <h1 class="marketing-title">Marketing dashboard</h1>
                    <p class="marketing-subtitle">Quick summary of your sent email activity.</p>

                    <div class="marketing-stats">
                        <div class="marketing-stat">
                            <span>Emails sent</span>
                            <strong>{{ number_format($emailsSent) }}</strong>
                        </div>
                        <div class="marketing-stat">
                            <span>Contacts</span>
                            <strong>{{ number_format($contactsCount) }}</strong>
                        </div>
                    </div>

                    <div class="marketing-recent">
                        <h2 class="h4 mb-3">Recent emails</h2>
                        @forelse ($recentEmails as $email)
                            <div class="marketing-recent-item">
                                <div>
                                    <strong>{{ $email->subject }}</strong>
                                    <div class="text-muted small">{{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}</div>
                                </div>
                                <span class="text-muted small">{{ optional($email->sent_at)->format('M d, Y') }}</span>
                            </div>
                        @empty
                            <div class="marketing-empty">No emails sent yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'send')
                    <h1 class="marketing-title">Send new email</h1>
                    <p class="marketing-subtitle">Write one message and send it to comma-separated email addresses.</p>

                    <form class="marketing-form" action="{{ route('marketing.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label for="recipients">Email addresses</label>
                            <input id="recipients" class="form-control @error('recipients') is-invalid @enderror" type="text" name="recipients" value="{{ old('recipients') }}" placeholder="name@example.com, second@example.com">
                            @error('recipients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="subject">Email title</label>
                            <input id="subject" class="form-control @error('subject') is-invalid @enderror" type="text" name="subject" value="{{ old('subject') }}">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="content">Email content</label>
                            <textarea id="content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="attachment">Attach file</label>
                            <input id="attachment" class="form-control @error('attachment') is-invalid @enderror" type="file" name="attachment">
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="marketing-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send me-1"></i>
                                Send email
                            </button>
                        </div>
                    </form>
                @else
                    <h1 class="marketing-title">My contacts</h1>
                    <p class="marketing-subtitle">Contacts are collected from email addresses you have sent messages to.</p>

                    <div class="marketing-contacts">
                        @forelse ($contacts as $contact)
                            <div class="marketing-contact">
                                <span>{{ $contact }}</span>
                                <i class="bi bi-envelope text-muted"></i>
                            </div>
                        @empty
                            <div class="marketing-empty">No contacts yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
</body>

</html>
