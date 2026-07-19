<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        html, body {
            margin: 0;
            height: 100%;
            background: #525659;
            font-family: system-ui, sans-serif;
        }
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2;
            display: flex;
            gap: 8px;
            align-items: center;
            padding: 8px 12px;
            background: #1f2937;
            color: #fff;
        }
        .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            background: #f59e0b;
            color: #111;
            font-weight: 600;
            cursor: pointer;
        }
        .toolbar span {
            font-size: 14px;
        }
        iframe {
            position: fixed;
            top: 48px;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: calc(100% - 48px);
            border: 0;
            background: #fff;
        }
        @media print {
            .toolbar { display: none !important; }
            iframe {
                top: 0;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" id="print-btn">Печать</button>
        <span>{{ $title }}</span>
    </div>
    <iframe id="document-frame" src="{{ $pdfUrl }}" title="{{ $title }}"></iframe>
    <script>
        const frame = document.getElementById('document-frame');
        const printBtn = document.getElementById('print-btn');

        const printDocument = () => {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                window.print();
            }
        };

        printBtn.addEventListener('click', printDocument);
        frame.addEventListener('load', () => {
            setTimeout(printDocument, 400);
        });
    </script>
</body>
</html>
