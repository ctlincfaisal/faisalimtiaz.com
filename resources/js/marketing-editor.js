function setupEditor(container) {
    if (container.dataset.editorReady === 'true') return;
    const surface = container.querySelector('[data-editor-surface]');
    const input = document.getElementById(container.dataset.inputId);
    if (!surface || !input) return;
    container.dataset.editorReady = 'true';

    const previewPanel = container.querySelector('[data-editor-preview]');
    const codePanel = container.querySelector('[data-editor-code-panel]');
    const codeSurface = container.querySelector('[data-editor-code-surface]');
    const previewTab = container.querySelector('[data-editor-tab="preview"]');
    const codeTab = container.querySelector('[data-editor-tab="code"]');
    const linkPopover = container.querySelector('[data-editor-link-popover]');
    const linkInput = container.querySelector('[data-editor-link-input]');
    const linkApply = container.querySelector('[data-editor-link-apply]');
    const linkCancel = container.querySelector('[data-editor-link-cancel]');
    const imageInput = container.querySelector('[data-editor-image-input]');
    const imageControls = container.querySelector('[data-editor-image-controls]');
    const imageWidthInput = container.querySelector('[data-editor-image-width]');
    const imageApply = container.querySelector('[data-editor-image-apply]');
    const imageReset = container.querySelector('[data-editor-image-reset]');

    let savedRange = null;
    let selectedImage = null;

    const isInsideSurface = (node) => node && (node === surface || surface.contains(node));

    const saveSelection = () => {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;
        if (isInsideSurface(selection.anchorNode) && isInsideSurface(selection.focusNode)) {
            savedRange = selection.getRangeAt(0).cloneRange();
        }
    };

    const restoreSelection = () => {
        surface.focus();
        const selection = window.getSelection();
        if (!selection) return null;

        selection.removeAllRanges();
        if (savedRange && isInsideSurface(savedRange.commonAncestorContainer)) {
            selection.addRange(savedRange);
            return savedRange;
        }

        const range = document.createRange();
        range.selectNodeContents(surface);
        range.collapse(false);
        selection.addRange(range);
        savedRange = range.cloneRange();
        return range;
    };

    const emitContentChange = () => {
        container.dispatchEvent(new CustomEvent('marketing-editor:content-change', {
            bubbles: true,
            detail: { content: input.value },
        }));
    };

    const syncInput = () => {
        input.value = surface.innerHTML;
        emitContentChange();
    };

    const sanitizePreviewHtml = (html) => {
        const documentFragment = new DOMParser().parseFromString(html || '<p><br></p>', 'text/html');
        documentFragment.querySelectorAll('script,iframe,object,embed,form,style').forEach(node => node.remove());
        documentFragment.querySelectorAll('*').forEach(node => {
            [...node.attributes].forEach(attribute => {
                if (/^on/i.test(attribute.name)) node.removeAttribute(attribute.name);
                if (['href', 'src'].includes(attribute.name.toLowerCase()) && /^\s*javascript:/i.test(attribute.value)) {
                    node.setAttribute(attribute.name, '#');
                }
            });
        });
        return documentFragment.body.innerHTML;
    };

    const setEditorContent = (html) => {
        const content = String(html || '');
        surface.innerHTML = sanitizePreviewHtml(content);
        input.value = content;
        if (codeSurface) codeSurface.value = content;
        emitContentChange();
        selectedImage = null;
        if (imageControls) imageControls.hidden = true;
    };

    container.addEventListener('marketing-editor:set-content', event => {
        setEditorContent(event.detail?.content || '');
    });

    const focusAndSync = () => {
        syncInput();
        saveSelection();
        surface.focus();
    };

    const runCommand = (command, value = null) => {
        restoreSelection();
        document.execCommand(command, false, value);
        focusAndSync();
    };

    const closeLinkPopover = () => {
        if (linkPopover) linkPopover.hidden = true;
        if (linkInput) linkInput.value = '';
    };

    const applyLink = () => {
        let url = linkInput?.value.trim() || '';
        if (url && !/^[a-z][a-z\d+.-]*:/i.test(url)) url = `https://${url}`;
        runCommand('createLink', url || null);
        closeLinkPopover();
    };

    const showImageControls = (image) => {
        selectedImage = image;
        if (!imageControls) return;
        imageControls.hidden = false;
        if (imageWidthInput) {
            const width = image.getAttribute('width') || image.style.width || '100';
            imageWidthInput.value = String(parseInt(width, 10) || 100);
        }
    };

    const hideImageControls = () => {
        selectedImage = null;
        if (imageControls) imageControls.hidden = true;
    };

    const applyImageWidth = (width) => {
        if (!selectedImage) return;
        const numericWidth = Math.min(100, Math.max(10, Number.parseInt(width, 10) || 100));
        selectedImage.setAttribute('width', `${numericWidth}%`);
        selectedImage.style.width = `${numericWidth}%`;
        selectedImage.style.maxWidth = '100%';
        selectedImage.style.height = 'auto';
        focusAndSync();
    };

    const resetImageWidth = () => {
        if (!selectedImage) return;
        selectedImage.removeAttribute('width');
        selectedImage.style.removeProperty('width');
        selectedImage.style.maxWidth = '100%';
        selectedImage.style.height = 'auto';
        if (imageWidthInput) imageWidthInput.value = '100';
        focusAndSync();
    };

    const setEditorTab = (mode) => {
        const isCode = mode === 'code';
        if (isCode) {
            syncInput();
            if (codeSurface) codeSurface.value = input.value;
        } else if (codeSurface) {
            surface.innerHTML = sanitizePreviewHtml(codeSurface.value);
            syncInput();
            surface.focus();
            saveSelection();
        }

        if (previewPanel) previewPanel.hidden = isCode;
        if (codePanel) codePanel.hidden = !isCode;
        previewTab?.setAttribute('aria-selected', String(!isCode));
        codeTab?.setAttribute('aria-selected', String(isCode));
        previewTab?.classList.toggle('editor-tab-active', !isCode);
        codeTab?.classList.toggle('editor-tab-active', isCode);
    };

    surface.innerHTML = sanitizePreviewHtml(input.value);
    surface.addEventListener('input', syncInput);
    surface.addEventListener('keyup', saveSelection);
    surface.addEventListener('mouseup', saveSelection);
    document.addEventListener('selectionchange', saveSelection);

    previewTab?.addEventListener('click', () => setEditorTab('preview'));
    codeTab?.addEventListener('click', () => setEditorTab('code'));
    codeSurface?.addEventListener('input', () => {
        input.value = codeSurface.value;
        emitContentChange();
    });

    linkApply?.addEventListener('mousedown', event => event.preventDefault());
    linkCancel?.addEventListener('mousedown', event => event.preventDefault());
    linkApply?.addEventListener('click', applyLink);
    linkCancel?.addEventListener('click', () => {
        closeLinkPopover();
        surface.focus();
    });
    linkInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyLink();
        } else if (event.key === 'Escape') {
            closeLinkPopover();
            surface.focus();
        }
    });

    container.querySelectorAll('[data-editor-action]').forEach(button => {
        button.addEventListener('mousedown', event => {
            saveSelection();
            event.preventDefault();
        });

        button.addEventListener('click', () => {
            const action = button.dataset.editorAction;
            if (action === 'undo') runCommand('undo');
            else if (action === 'redo') runCommand('redo');
            else if (action === 'paragraph') runCommand('formatBlock', '<p>');
            else if (action === 'h1') runCommand('formatBlock', '<h1>');
            else if (action === 'h2') runCommand('formatBlock', '<h2>');
            else if (action === 'quote') runCommand('formatBlock', '<blockquote>');
            else if (action === 'bold') runCommand('bold');
            else if (action === 'italic') runCommand('italic');
            else if (action === 'underline') runCommand('underline');
            else if (action === 'strikethrough') runCommand('strikeThrough');
            else if (action === 'code') runCommand('formatBlock', '<pre>');
            else if (action === 'bullet') runCommand('insertUnorderedList');
            else if (action === 'number') runCommand('insertOrderedList');
            else if (action === 'link') {
                saveSelection();
                if (linkPopover) linkPopover.hidden = false;
                linkInput?.focus();
            } else if (action === 'image') {
                saveSelection();
                imageInput?.click();
            }
        });
    });

    surface.addEventListener('click', event => {
        const image = event.target instanceof HTMLImageElement
            ? event.target
            : event.target.closest?.('img');
        if (image && surface.contains(image)) showImageControls(image);
        else hideImageControls();
        saveSelection();
    });

    imageApply?.addEventListener('mousedown', event => event.preventDefault());
    imageReset?.addEventListener('mousedown', event => event.preventDefault());
    imageApply?.addEventListener('click', () => applyImageWidth(imageWidthInput?.value));
    imageReset?.addEventListener('click', resetImageWidth);

    imageInput?.addEventListener('change', () => {
        const file = imageInput.files?.[0];
        if (!file) return;

        const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            window.alert('Please choose a PNG, JPG, GIF, or WebP image.');
            imageInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', () => {
            if (typeof reader.result !== 'string') return;
            const range = restoreSelection();
            if (!range) return;

            const image = document.createElement('img');
            image.src = reader.result;
            image.alt = file.name;
            image.setAttribute('data-marketing-image', 'true');
            image.style.maxWidth = '100%';
            image.style.height = 'auto';
            range.deleteContents();
            range.insertNode(image);
            range.setStartAfter(image);
            range.collapse(true);
            const selection = window.getSelection();
            selection?.removeAllRanges();
            selection?.addRange(range);
            savedRange = range.cloneRange();
            showImageControls(image);
            focusAndSync();
            imageInput.value = '';
        });
        reader.readAsDataURL(file);
    });

    const form = container.closest('form');
    form?.addEventListener('submit', () => {
        if (codePanel?.hidden === false && codeSurface) input.value = codeSurface.value;
        else syncInput();
    });
}

window.setupMarketingEditor = setupEditor;
document.querySelectorAll('[data-rich-text-editor]').forEach(setupEditor);
