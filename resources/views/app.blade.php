<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
                --app-url: "{{ rtrim(config('app.url'), '/') }}";
                --kids-bg-image: url("{{ rtrim(config('app.url'), '/') }}/images/kids-bg-pattern.svg");
                --youth-bg-image: url("{{ rtrim(config('app.url'), '/') }}/images/juvenil.png");
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name') }}</title>

        <link rel="icon" href="{{ rtrim(config('app.url'), '/') }}/images/Logo1.png" type="image/png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <script>
            window.__APP_URL__ = "{{ rtrim(config('app.url'), '/') }}";
        </script>

        @routes
        <script>
            if (window.Ziggy) {
                window.Ziggy.url = "{{ rtrim(config('app.url'), '/') }}";
            }
        </script>
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
