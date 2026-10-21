const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const vm = require('node:vm');

const view = readFileSync(resolve(__dirname, '../../resources/views/marketing.blade.php'), 'utf8');
const start = view.indexOf("    const sendForm = document.getElementById('sendForm');");
const end = view.indexOf('    function fetchMarketingProgress', start);
assert.ok(start >= 0 && end > start, 'Send form script must be present');
const script = view.slice(start, end).replace(/\{\{.*?\}\}/g, '/marketing?tab=dashboard');

function classList() {
    const classes = new Set(['hidden']);
    return {
        add: value => classes.add(value),
        remove: value => classes.delete(value),
        contains: value => classes.has(value),
    };
}

async function submitResponse(status, body, redirected = false) {
    let submit;
    let request;
    const button = { disabled: false, classList: classList() };
    const fields = [button, { disabled: false }];
    const error = { textContent: '', classList: classList() };
    const form = {
        action: '/marketing/send',
        querySelectorAll: () => fields,
        addEventListener: (event, callback) => { submit = callback; },
    };
    const elements = { sendForm: form, sendButton: button, sendError: error };
    const window = { location: { href: '/marketing?tab=send' } };
    vm.runInNewContext(script, {
        document: { getElementById: id => elements[id] },
        FormData: class {},
        window,
        fetch: async (url, options) => {
            request = { url, options };
            return { status, ok: status >= 200 && status < 300, redirected, text: async () => body };
        },
    });
    submit({ preventDefault() {} });
    await new Promise(setImmediate);
    return { error, button, fields, window, request };
}

test('HTML error pages produce a useful message and restore the send form', async () => {
    const result = await submitResponse(500, '<!DOCTYPE html><html>Server Error</html>');
    assert.match(result.error.textContent, /unexpected response \(HTTP 500\)/);
    assert.doesNotMatch(result.error.textContent, /Unexpected token|<!DOCTYPE/);
    assert.equal(result.error.classList.contains('hidden'), false);
    assert.ok(result.fields.every(field => !field.disabled));
    assert.equal(result.window.location.href, '/marketing?tab=send');
});

for (const status of [401, 419]) {
    test(`HTTP ${status} explains that the session expired`, async () => {
        const result = await submitResponse(status, '<!DOCTYPE html><html>Sign in</html>');
        assert.match(result.error.textContent, /session has expired/);
        assert.equal(result.button.disabled, false);
    });
}

test('a followed login redirect is handled without parsing HTML', async () => {
    const result = await submitResponse(200, '<!DOCTYPE html><html>Sign in</html>', true);
    assert.match(result.error.textContent, /session has expired/);
    assert.equal(result.window.location.href, '/marketing?tab=send');
});

test('an HTML upload rejection explains the file size limit', async () => {
    const result = await submitResponse(413, '<html>Payload too large</html>');
    assert.match(result.error.textContent, /attachment exceeds the server upload limit/);
});

test('JSON validation errors appear in the error box', async () => {
    const result = await submitResponse(422, JSON.stringify({ errors: { recipients: ['Invalid email address.'] } }));
    assert.equal(result.error.textContent, 'Invalid email address.');
});

test('JSON storage errors appear in the error box', async () => {
    const result = await submitResponse(503, JSON.stringify({ message: 'Marketing storage is not ready yet.' }));
    assert.equal(result.error.textContent, 'Marketing storage is not ready yet.');
});

test('a successful send opens the dashboard and requests JSON', async () => {
    const result = await submitResponse(200, JSON.stringify({ id: 42, recipient_count: 1 }));
    assert.equal(result.window.location.href, '/marketing?tab=dashboard');
    assert.equal(result.request.options.headers.Accept, 'application/json');
    assert.equal(result.error.classList.contains('hidden'), true);
});
