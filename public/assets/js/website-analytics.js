(function () {
    var script = document.currentScript;
    var scriptUrl = script && script.src ? new URL(script.src) : new URL(window.location.href);
    var websiteKey = script && script.dataset ? script.dataset.websiteKey : null;
    var endpoint = scriptUrl.origin;
    var visitUrl = endpoint + '/analytics/visit';
    var heartbeatUrl = endpoint + '/analytics/heartbeat';
    var clickUrl = endpoint + '/analytics/click';
    var sessionKey = 'website_analytics_session_id';
    var sessionId = localStorage.getItem(sessionKey);
    var visitId = null;

    if (!sessionId) {
        sessionId = (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2));
        localStorage.setItem(sessionKey, sessionId);
    }

    function post(url, payload, useBeacon) {
        var body = JSON.stringify(payload);

        if (useBeacon && navigator.sendBeacon) {
            navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: body,
            keepalive: true,
            mode: 'cors'
        }).then(function (response) { return response.json().catch(function () { return {}; }); }).then(function (data) {
            if (data && data.id) visitId = data.id;
        }).catch(function () {});
    }

    function payload() {
        return {
            website_key: websiteKey,
            session_id: sessionId,
            url: window.location.href,
            path: window.location.pathname,
            referrer: document.referrer,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight
        };
    }

    post(visitUrl, payload(), false);
    window.setInterval(function () {
        if (visitId) post(heartbeatUrl, { website_key: websiteKey, visit_id: visitId, session_id: sessionId, url: window.location.href, path: window.location.pathname }, false);
    }, 30000);

    document.addEventListener('click', function (event) {
        var target = event.target.closest('a, button, input, textarea, select, [role="button"]');
        if (!target) return;
        post(clickUrl, { website_key: websiteKey, visit_id: visitId, session_id: sessionId, url: window.location.href, path: window.location.pathname, element: target.tagName.toLowerCase(), element_text: (target.innerText || target.value || '').trim().slice(0, 500), x: Math.max(0, Math.round(event.pageX)), y: Math.max(0, Math.round(event.pageY)) }, true);
    }, true);
})();
