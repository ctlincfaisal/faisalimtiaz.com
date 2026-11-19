@php
    $inputName = $inputName ?? 'content';
    $hasError = $hasError ?? false;
    $showHint = $showHint ?? true;
@endphp
<div data-rich-text-editor data-editor-id="{{ $editorId }}" data-input-id="{{ $inputId }}" class="overflow-hidden rounded-lg border {{ $hasError ? 'border-red-500' : 'border-slate-300 dark:border-slate-700' }} bg-white dark:bg-slate-950">
    <div class="flex items-center gap-1 border-b border-slate-200 bg-slate-50 px-2 pt-2 dark:border-slate-800 dark:bg-slate-900" role="tablist" aria-label="Template editor mode">
        <button type="button" data-editor-tab="preview" role="tab" aria-selected="true" class="rounded-t-md border border-b-0 border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-slate-700 dark:bg-slate-950 dark:text-emerald-300">Preview</button>
        <button type="button" data-editor-tab="code" role="tab" aria-selected="false" class="rounded-t-md px-3 py-2 text-xs font-semibold text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300">Code</button>
    </div>
    <div data-editor-preview>
        <div data-editor-toolbar class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-900" role="toolbar" aria-label="Rich text formatting">
            <button type="button" data-editor-action="undo" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Undo"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" data-editor-action="redo" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Redo"><i class="bi bi-arrow-clockwise"></i></button>
            <span class="mx-1 h-6 w-px bg-slate-200 dark:bg-slate-700"></span>
            <button type="button" data-editor-action="paragraph" class="rounded px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">Paragraph</button>
            <button type="button" data-editor-action="h1" class="rounded px-2.5 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">H1</button>
            <button type="button" data-editor-action="h2" class="rounded px-2.5 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">H2</button>
            <button type="button" data-editor-action="quote" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Quote"><i class="bi bi-blockquote-left"></i></button>
            <span class="mx-1 h-6 w-px bg-slate-200 dark:bg-slate-700"></span>
            <button type="button" data-editor-action="bold" class="rounded px-2.5 py-1.5 text-sm font-bold text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Bold">B</button>
            <button type="button" data-editor-action="italic" class="rounded px-2.5 py-1.5 text-sm italic text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Italic">I</button>
            <button type="button" data-editor-action="underline" class="rounded px-2.5 py-1.5 text-sm underline text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Underline">U</button>
            <button type="button" data-editor-action="strikethrough" class="rounded px-2.5 py-1.5 text-sm line-through text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Strikethrough">S</button>
            <button type="button" data-editor-action="code" class="rounded px-2.5 py-1.5 font-mono text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Inline code">&lt;/&gt;</button>
            <button type="button" data-editor-action="link" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Add link"><i class="bi bi-link-45deg"></i></button>
            <button type="button" data-editor-action="bullet" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Bulleted list"><i class="bi bi-list-ul"></i></button>
            <button type="button" data-editor-action="number" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Numbered list"><i class="bi bi-list-ol"></i></button>
            <button type="button" data-editor-action="image" class="rounded px-2.5 py-1.5 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" title="Insert image"><i class="bi bi-image"></i></button>
            <input type="file" data-editor-image-input accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
        </div>
        <div data-editor-image-controls hidden class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-emerald-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-emerald-500/10">
            <span class="font-semibold text-emerald-800 dark:text-emerald-200">Selected image width</span>
            <input type="number" data-editor-image-width min="10" max="100" step="5" value="100" class="w-20 rounded border border-emerald-200 bg-white px-2 py-1 text-xs text-slate-800 outline-none focus:border-emerald-500 dark:border-emerald-500/30 dark:bg-slate-950 dark:text-slate-100">
            <span class="text-emerald-700 dark:text-emerald-300">%</span>
            <button type="button" data-editor-image-apply class="rounded bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-emerald-700">Apply</button>
            <button type="button" data-editor-image-reset class="rounded border border-emerald-200 bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-slate-950 dark:text-emerald-300 dark:hover:bg-emerald-500/10">Reset</button>
        </div>
        <div data-editor-link-popover hidden class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
            <input type="url" data-editor-link-input placeholder="https://example.com" class="min-w-0 flex-1 rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <button type="button" data-editor-link-apply class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700">Apply</button>
            <button type="button" data-editor-link-cancel class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
        </div>
        <div id="{{ $editorId }}" contenteditable="true" role="textbox" aria-multiline="true" data-editor-surface class="min-h-72 px-4 py-4 text-sm leading-7 text-slate-800 outline-none empty:before:text-slate-400 empty:before:content-['Start_writing_your_email...'] dark:text-slate-100 dark:empty:before:text-slate-600"></div>
    </div>
    <div data-editor-code-panel hidden>
        <textarea data-editor-code-surface spellcheck="false" class="min-h-72 w-full resize-y border-0 bg-slate-950 px-4 py-4 font-mono text-xs leading-6 text-emerald-100 outline-none" aria-label="Email HTML code">{{ $initialContent }}</textarea>
    </div>
    <textarea id="{{ $inputId }}" name="{{ $inputName }}" class="hidden">{{ $initialContent }}</textarea>
</div>
@if ($showHint)
    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use Preview for visual editing or Code to enter complete HTML directly. Select an inserted image to resize it.</p>
@endif
@if ($hasError)
    @error($inputName)
        <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
    @enderror
@endif
