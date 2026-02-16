<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle ?? 'Документ' }}</title>
    <style>
        @charset "UTF-8";
        /* 
         * ФИКСИРОВАННЫЕ РАЗМЕРЫ ДЛЯ А4 ПОРТРЕТНОЙ ОРИЕНТАЦИИ
         * НЕ МЕНЯТЬ БЕЗ ЯВНОГО УКАЗАНИЯ!
         * Формат: A4 (210mm x 297mm), портретная ориентация
         * Отступы страницы: 10mm со всех сторон
         * Контейнер: 190mm ширина, центрирован, padding 5mm
         */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: dejavusans, DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #000;
            padding: 0;
            margin: 0;
            width: 210mm;
            max-width: 210mm;
            min-height: 297mm;
            overflow: hidden;
        }

        .document-container {
            width: 190mm;
            max-width: 190mm;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        .document-header {
            text-align: center;
            margin-bottom: 4mm;
            padding-bottom: 2mm;
            border-bottom: 0.5px solid #000;
        }

        .document-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 0.5mm;
            letter-spacing: 0.5px;
        }

        .document-subtitle {
            font-size: 9px;
            color: #333;
            font-weight: 400;
        }

        .document-body {
            margin-bottom: 0;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .document-part {
                page-break-inside: avoid;
            }
        }

        .document-part {
            width: 100%;
            max-width: 100%;
            margin-bottom: 3mm;
            border: 0.5px solid #000;
            padding: 2mm;
            min-height: 0;
            page-break-inside: avoid;
            box-sizing: border-box;
            overflow: hidden;
            background: #fff;
        }

        .document-part-header {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2mm;
            padding-bottom: 1mm;
            border-bottom: 0.5px solid #000;
        }

        .client-part {
            border: 0.5px solid #000;
        }

        .workshop-part {
            border: 0.5px solid #000;
        }

        .section {
            margin-bottom: 2mm;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1mm;
            border-bottom: 0.5px solid #000;
            padding-bottom: 0.5mm;
        }

        .info-row {
            margin-bottom: 0.5mm;
            overflow: hidden;
        }

        .info-label {
            float: left;
            width: 100px;
            font-weight: bold;
            padding-right: 2px;
            font-size: 9px;
        }

        .info-value {
            overflow: hidden;
            font-size: 9px;
        }

        .table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
            font-size: 8px;
            table-layout: fixed;
        }

        .table th,
        .table td {
            border: 0.5px solid #000;
            padding: 2px 3px;
            text-align: left;
            font-size: 8px;
            vertical-align: top;
        }

        .table th {
            background-color: #fff;
            font-weight: bold;
            border: 0.5px solid #000;
        }

        .table td {
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .signature-section {
            margin-top: 4mm;
            overflow: hidden;
            clear: both;
            padding-top: 2mm;
            border-top: 0.5px solid #000;
        }

        .signature-block {
            float: left;
            width: 48%;
            margin-right: 4%;
            min-height: 20mm;
        }

        .signature-block:last-child {
            margin-right: 0;
        }

        .signature-line {
            border-top: 0.5px solid #000;
            margin-top: 5mm;
            padding-top: 1mm;
            min-height: 15mm;
        }

        .signature-label {
            font-size: 9px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 1mm;
            letter-spacing: 0.2px;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .urgent-badge {
            display: inline-block;
            border: 0.5px solid #000;
            padding: 1px 3px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer {
            margin-top: 5px;
            font-size: 7px;
            text-align: center;
        }

        .responsibility-section {
            margin-top: 2mm;
            padding: 2mm;
            border: 0.5px solid #000;
            font-size: 8px;
            line-height: 1.4;
        }

        .responsibility-title {
            font-weight: bold;
            margin-bottom: 0.8mm;
            font-size: 9px;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .responsibility-text {
            margin-bottom: 0.5mm;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="document-container">
        <div class="document-header">
            <div class="document-title">{{ $documentTitle ?? 'Документ' }}</div>
            <div class="document-subtitle">№ {{ $data->orderNumber }} от {{ $data->orderDate }}</div>
        </div>

        <div class="document-body">
            @yield('content')
        </div>
    </div>
</body>
</html>
