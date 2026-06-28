<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title>Faisal Imtiaz | Mobile Application Developer</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">

    <!-- Font -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"> -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">



    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ url('assets/vendor/hs-mega-menu/dist/hs-mega-menu.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/vendor/aos/dist/aos.css') }}">
    <link rel="stylesheet" href="{{ url('assets/vendor/hs-video-bg/dist/hs-video-bg.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/vendor/swiper/swiper-bundle.min.css') }}">

    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{ url('assets/css/theme.min.css') }}">
    
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XT8E1TZG6C"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-XT8E1TZG6C');
</script>

<!-- Event snippet for Website lead conversion page -->
<script>
  gtag('event', 'conversion', {'send_to': 'AW-866786990/M3V4CM7Y9LwZEK69qJ0D'});
</script>







<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-866786990"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-866786990');
</script>



    @yield('css')

    <style>
    html {
        scroll-behavior: smooth;
    }
    </style>

</head>

<body>

    <div class="sticky-top bg-light">
        @include('_layout.header')
    </div>

    <main id="content" class="" role="main">
        @yield('content')
    </main>

    @include('_layout.footer')

    <script>
    (function () {
        const visitUrl = "{{ route('analytics.visit') }}";
        const heartbeatUrl = "{{ route('analytics.heartbeat') }}";
        const clickUrl = "{{ route('analytics.click') }}";
        const sessionKey = 'website_analytics_session_id';
        let sessionId = localStorage.getItem(sessionKey);
        let visitId = null;

        if (!sessionId) {
            sessionId = (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2));
            localStorage.setItem(sessionKey, sessionId);
        }

        function post(url, payload, callback, useBeacon) {
            const body = JSON.stringify(payload);

            if (useBeacon && navigator.sendBeacon) {
                navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
                if (callback) {
                    callback();
                }
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: body,
                keepalive: true
            }).then(function (response) {
                return response.json().catch(function () {
                    return {};
                });
            }).then(function (data) {
                if (callback) {
                    callback(data);
                }
            }).catch(function () {});
        }

        function heartbeat(useBeacon) {
            if (!visitId) {
                return;
            }

            post(heartbeatUrl, {
                visit_id: visitId,
                session_id: sessionId,
                url: window.location.href,
                path: window.location.pathname
            }, null, useBeacon);
        }

        post(visitUrl, {
            session_id: sessionId,
            url: window.location.href,
            path: window.location.pathname,
            referrer: document.referrer,
            screen_width: window.screen ? window.screen.width : null,
            screen_height: window.screen ? window.screen.height : null,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight
        }, function (data) {
            if (data && data.id) {
                visitId = data.id;
            }
        }, false);

        window.setInterval(function () {
            heartbeat(false);
        }, 30000);

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                heartbeat(false);
            } else {
                heartbeat(true);
            }
        });

        window.addEventListener('beforeunload', function () {
            heartbeat(true);
        });

        document.addEventListener('click', function (event) {
            const target = event.target.closest('a, button, input, textarea, select, [role="button"]');

            if (!target) {
                return;
            }

            post(clickUrl, {
                visit_id: visitId,
                session_id: sessionId,
                url: window.location.href,
                path: window.location.pathname,
                element: target.tagName.toLowerCase() + (target.id ? '#' + target.id : '') + (target.className && typeof target.className === 'string' ? '.' + target.className.trim().split(/\s+/).slice(0, 3).join('.') : ''),
                element_text: (target.innerText || target.value || target.getAttribute('aria-label') || target.getAttribute('href') || '').trim().slice(0, 500),
                x: Math.max(0, Math.round(event.pageX)),
                y: Math.max(0, Math.round(event.pageY))
            }, null, true);
        }, true);
    })();
    </script>

</body>

</html>
