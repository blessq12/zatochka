<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle ?? 'Документ' }}</title>
    @include('documents.layouts.styles')
</head>
<body>
    <div class="document-container">
        <div class="document-header">
            <div class="document-title">{{ $documentTitle ?? 'Документ' }}</div>
            <div class="document-subtitle">№ {{ $data->orderNumber }} от {{ $data->orderDate }}</div>
        </div>

        <div class="document-body">
            {!! $bodyHtml !!}
        </div>
    </div>
</body>
</html>
