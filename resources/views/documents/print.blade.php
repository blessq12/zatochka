<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Печать документа</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
</head>
<body>
    <iframe id="pdfFrame" src="{{ $pdfUrl }}"></iframe>
    
    <script>
        const iframe = document.getElementById('pdfFrame');
        let printAttempted = false;
        
        function attemptPrint() {
            if (printAttempted) return;
            
            try {
                if (iframe.contentWindow && iframe.contentDocument) {
                    printAttempted = true;
                    iframe.contentWindow.print();
                }
            } catch (e) {
                console.log('Ожидание загрузки PDF...');
            }
        }
        
        // Пытаемся вызвать print при загрузке iframe
        iframe.onload = function() {
            setTimeout(attemptPrint, 500);
        };
        
        // Альтернативный способ: вызываем print после загрузки страницы
        window.addEventListener('load', function() {
            setTimeout(attemptPrint, 1000);
        });
        
        // Дополнительная попытка через 2 секунды
        setTimeout(attemptPrint, 2000);
    </script>
</body>
</html>
