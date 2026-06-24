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
    .marketing-contact,
    .marketing-sent-email,
    .marketing-template {
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

    .marketing-nav-label {
        align-items: center;
        color: #0f172a;
        display: flex;
        font-weight: 700;
        gap: 10px;
        padding: 12px 14px 4px;
    }

    .marketing-nav-sub {
        display: grid;
        gap: 6px;
        padding-left: 22px;
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

    .marketing-sent-emails {
        display: grid;
        gap: 12px;
    }

    .marketing-sent-email {
        padding: 16px;
    }

    .marketing-sent-row {
        align-items: center;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1fr) auto auto;
    }

    .marketing-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: 12px;
        font-weight: 700;
        justify-content: center;
        min-width: 98px;
        padding: 6px 10px;
    }

    .marketing-badge-success {
        background: #ecfdf5;
        color: #047857;
    }

    .marketing-badge-danger {
        background: #fef2f2;
        color: #b91c1c;
    }

    .marketing-badge-muted {
        background: #f1f5f9;
        color: #475569;
    }

    .marketing-templates {
        display: grid;
        gap: 12px;
        margin-top: 24px;
    }

    .marketing-contact {
        padding: 14px 16px;
    }

    .marketing-template {
        padding: 16px;
    }

    .marketing-template-row {
        align-items: center;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .marketing-template-actions {
        display: flex;
        gap: 8px;
    }

    .marketing-template-preview {
        color: #64748b;
        margin: 8px 0 0;
        white-space: pre-line;
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

        .marketing-template-row {
            grid-template-columns: 1fr;
        }

        .marketing-sent-row {
            grid-template-columns: 1fr;
        }

        .marketing-template-actions {
            justify-content: flex-start;
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
                    <a class="{{ $activeTab === 'sent-emails' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                        <i class="bi bi-envelope-check"></i>
                        Sent emails
                    </a>
                    <a class="{{ $activeTab === 'contacts' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'contacts']) }}">
                        <i class="bi bi-people"></i>
                        My contacts
                    </a>
                    <div class="marketing-nav-label">
                        <i class="bi bi-file-earmark-text"></i>
                        Templates
                    </div>
                    <div class="marketing-nav-sub">
                        <a class="{{ $activeTab === 'templates-create' ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'templates-create']) }}">
                            <i class="bi bi-plus-circle"></i>
                            Create template
                        </a>
                        <a class="{{ in_array($activeTab, ['templates-list', 'templates-edit'], true) ? 'active' : '' }}" href="{{ route('marketing', ['tab' => 'templates-list']) }}">
                            <i class="bi bi-list-ul"></i>
                            Template listings
                        </a>
                    </div>
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
                            <span>Emails opened</span>
                            <strong>{{ number_format($emailsOpened) }}</strong>
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
                                    <div class="text-muted small">
                                        {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                        &middot;
                                        {{ $email->opens->whereNotNull('opened_at')->count() }} opened
                                    </div>
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
                            <label for="template">Template</label>
                            <select id="template" class="form-select">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}">
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                @elseif ($activeTab === 'sent-emails')
                    <h1 class="marketing-title">Sent emails</h1>
                    <p class="marketing-subtitle">Review emails you sent, delivery status, and open status.</p>

                    <div class="marketing-sent-emails">
                        @forelse ($sentEmails as $email)
                            @php
                                $openedCount = $email->opens->whereNotNull('opened_at')->count();
                                $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');
                                $sentAt = $email->sent_at ?: $email->created_at;
                            @endphp
                            <div class="marketing-sent-email">
                                <div class="marketing-sent-row">
                                    <div>
                                        <strong>{{ $email->subject }}</strong>
                                        <div class="text-muted small">
                                            {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                            &middot;
                                            {{ optional($sentAt)->format('M d, Y h:i A') }}
                                        </div>
                                    </div>

                                    @if ($deliveryStatus === 'delivered')
                                        <span class="marketing-badge marketing-badge-success">Delivered</span>
                                    @elseif ($deliveryStatus === 'pending')
                                        <span class="marketing-badge marketing-badge-muted">Pending</span>
                                    @else
                                        <span class="marketing-badge marketing-badge-danger">Not delivered</span>
                                    @endif

                                    @if ($openedCount > 0)
                                        <span class="marketing-badge marketing-badge-success">Opened</span>
                                    @else
                                        <span class="marketing-badge marketing-badge-muted">Not opened</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="marketing-empty">No sent emails yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'contacts')
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
                @elseif ($activeTab === 'templates-create')
                    <h1 class="marketing-title">Create template</h1>
                    <p class="marketing-subtitle">Create a reusable email template for the send page.</p>

                    <form class="marketing-form" action="{{ route('marketing.templates.store') }}" method="POST">
                        @csrf

                        <div>
                            <label for="name">Template name</label>
                            <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="template_subject">Email title</label>
                            <input id="template_subject" class="form-control @error('subject') is-invalid @enderror" type="text" name="subject" value="{{ old('subject') }}">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="template_content">Email template</label>
                            <textarea id="template_content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="marketing-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-save me-1"></i>
                                Save template
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'templates-edit')
                    <h1 class="marketing-title">Edit template</h1>
                    <p class="marketing-subtitle">Update this saved template.</p>

                    <form class="marketing-form" action="{{ route('marketing.templates.update', $editingTemplate) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="edit_name">Template name</label>
                            <input id="edit_name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $editingTemplate->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_template_subject">Email title</label>
                            <input id="edit_template_subject" class="form-control @error('subject') is-invalid @enderror" type="text" name="subject" value="{{ old('subject', $editingTemplate->subject) }}">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_template_content">Email template</label>
                            <textarea id="edit_template_content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content', $editingTemplate->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="marketing-actions gap-2">
                            <a class="btn btn-soft-secondary" href="{{ route('marketing', ['tab' => 'templates-list']) }}">Cancel</a>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-save me-1"></i>
                                Update template
                            </button>
                        </div>
                    </form>
                @else
                    <h1 class="marketing-title">Template listings</h1>
                    <p class="marketing-subtitle">Manage saved templates for the send page.</p>

                    <div class="marketing-templates">
                        @forelse ($templates as $template)
                            <div class="marketing-template">
                                <div class="marketing-template-row">
                                    <div>
                                        <strong>{{ $template->name }}</strong>
                                        <p class="marketing-template-preview">
                                            {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before(str_replace(["\r\n", "\r"], "\n", $template->content), "\n"), 140) }}
                                        </p>
                                    </div>
                                    <div class="marketing-template-actions">
                                        <a class="btn btn-sm btn-soft-primary" href="{{ route('marketing', ['tab' => 'templates-edit', 'template' => $template->id]) }}">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('marketing.templates.delete', $template) }}" method="POST" onsubmit="return confirm('Delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-soft-danger" type="submit">
                                                <i class="bi bi-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="marketing-empty">No templates yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
<script>
    const templateSelect = document.getElementById('template');
    const templates = @json($templateOptions);

    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            const subject = document.getElementById('subject');
            const content = document.getElementById('content');
            const template = templates[this.value];

            if (!template) {
                return;
            }

            subject.value = template.subject || '';
            content.value = template.content || '';
        });
    }
</script>
</body>

</html>
