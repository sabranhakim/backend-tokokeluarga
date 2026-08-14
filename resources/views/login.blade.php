<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grosir Toko Keluarga - Login</title>

    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Work+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "on-error": "#ffffff",
                      "primary-fixed-dim": "#ffb870",
                      "on-surface-variant": "#524439",
                      "secondary-fixed": "#ffdad4",
                      "surface-container-lowest": "#ffffff",
                      "primary-container": "#c68642",
                      "on-secondary": "#ffffff",
                      "on-tertiary": "#ffffff",
                      "surface-variant": "#e5e2dd",
                      "on-primary": "#ffffff",
                      "surface-bright": "#fcf9f4",
                      "tertiary-fixed": "#baeaff",
                      "on-primary-fixed": "#2c1600",
                      "background": "#fcf9f4",
                      "error-container": "#ffdad6",
                      "surface-tint": "#885210",
                      "secondary-fixed-dim": "#e3beb8",
                      "error": "#ba1a1a",
                      "on-primary-fixed-variant": "#693c00",
                      "outline": "#847467",
                      "inverse-surface": "#31302d",
                      "surface-container-high": "#ebe8e3",
                      "surface": "#fcf9f4",
                      "surface-container-low": "#f6f3ee",
                      "on-tertiary-fixed": "#001f29",
                      "outline-variant": "#d6c3b4",
                      "on-secondary-fixed-variant": "#5b403c",
                      "secondary": "#745853",
                      "inverse-on-surface": "#f3f0eb",
                      "tertiary-container": "#4c9ebc",
                      "primary": "#885210",
                      "on-tertiary-container": "#003140",
                      "primary-fixed": "#ffdcbe",
                      "on-error-container": "#93000a",
                      "on-secondary-container": "#795c57",
                      "surface-container-highest": "#e5e2dd",
                      "on-tertiary-fixed-variant": "#004d62",
                      "on-primary-container": "#442500",
                      "tertiary": "#006781",
                      "tertiary-fixed-dim": "#82d1f1",
                      "on-background": "#1c1c19",
                      "inverse-primary": "#ffb870",
                      "surface-dim": "#dcdad5",
                      "secondary-container": "#fed7d0",
                      "on-surface": "#1c1c19",
                      "on-secondary-fixed": "#2b1613",
                      "surface-container": "#f0ede9"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "fontFamily": {
                      "headline": ["Manrope"],
                      "body": ["Work Sans"],
                      "label": ["Work Sans"]
              }
            },
          }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Work Sans', sans-serif;
            background-color: #eaf1ff;
            color: #1c1c19;
        }
        h1, h2, h3, .headline {
            font-family: 'Manrope', sans-serif;
        }
        .burnt-caramel-gradient {
            background: linear-gradient(135deg, #885210 0%, #c68642 100%);
        }
        .soft-glass {
            background: rgba(252, 249, 244, 0.8);
            backdrop-filter: blur(24px);
        }
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen antialiased">
    <livewire:auth.login />
    @livewireScripts
</body>
</html>
