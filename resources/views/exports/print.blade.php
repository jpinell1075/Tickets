<!DOCTYPE html>
<html lang="es">

@php
    $usePagedJs = isset($pageProperties['pagedjs']) && $pageProperties['pagedjs'] == true;
    $hasBackground = isset($pageProperties['background']) && $pageProperties['background'] == true;
    $openInNewTab = $pageProperties['openInNewTab'] ?? false;
    $backgroundImage = $pageProperties['backgroundimage'] ?? '';

    $pageSize = $pageProperties['size'] ?? 'letter';
    $pageOrientation = $pageProperties['orientation'] ?? 'portrait';

    $pageMargin = $pageProperties['margin'] ?? null;
    $marginTop = $pageProperties['margintop'] ?? '3.8cm';
    $marginBottom = $pageProperties['marginbottom'] ?? '1.5cm';
    $marginLeft = $pageProperties['marginleft'] ?? '1.5cm';
    $marginRight = $pageProperties['marginright'] ?? '1.5cm';

    $contentPadding = $pageMargin
        ? $pageMargin
        : "$marginTop $marginRight $marginBottom $marginLeft";
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @if(isset($pageProperties['loadBootstrap']) && $pageProperties['loadBootstrap'] == true)
        <link href="/css/bootstrap.css" rel="stylesheet">
    @endif
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @if(!$usePagedJs)
            html,
            body {
                background: transparent;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                position: relative;
                isolation: isolate;
            }

            .print-page-background {
                position: fixed;
                inset: 0;
                z-index: -1;
                pointer-events: none;
                background-repeat: no-repeat;
                background-position: center top;
                background-size: 100% 100%;
            }

            .print-content {
                position: relative;
                z-index: 1;
                padding: {{ $contentPadding }};
                -webkit-box-decoration-break: clone;
                box-decoration-break: clone;
            }
        @endif

        @page {
            size: {{ $pageSize }} {{ $pageOrientation }};
            @if(!$usePagedJs)
                margin: 0;
            @else
                @if($pageMargin)
                    margin: {{ $pageMargin }};
                @else
                    margin-top: {{ $marginTop }};
                    margin-bottom: {{ $marginBottom }};
                    margin-left: {{ $marginLeft }};
                    margin-right: {{ $marginRight }};
                @endif
            @endif
        }

        @page {
            @if($hasBackground && $usePagedJs)
                background-image: url('{{ $backgroundImage }}');
                background-size: 100% auto;
                background-repeat: no-repeat;
                background-position: center center;
            @endif

            @if(isset($pageProperties['pagecounter']) && $pageProperties['pagecounter'] == true)
                @bottom-center {
                    content: "Página " counter(page) " de " counter(pages);
                    margin-bottom: 10px;
                }
            @endif
        }
    </style>

    @stack('styles')
</head>

<body class="print-container" id="print-container">
    @if($hasBackground && !$usePagedJs)
        <div
            class="print-page-background"
            aria-hidden="true"
            style="background-image: url('{{ $backgroundImage }}');"
        ></div>
    @endif

    <div class="print-content">
        @if($params)
            @include($view_name, $params)
        @else
            @include($view_name)
        @endif
    </div>
</body>

<script>
    window.loadPagedJs = @json($usePagedJs);
    window.openInNewTab = @json($openInNewTab);
</script>

<script src="/js/exports/print.js"></script>
@stack('scripts')

</html>