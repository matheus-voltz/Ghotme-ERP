<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta Item #{{ $item->sku ?? $item->id }}</title>
    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f5f5f9;
        }

        .label-card {
            width: 100mm;
            height: 100mm;
            background: #fff;
            padding: 5mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid #eee;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            margin-bottom: 15px;
            width: 100%;
            border-bottom: 2px solid #7367f0;
            padding-bottom: 10px;
        }

        .brand {
            font-weight: 800;
            color: #7367f0;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .qr-code {
            margin: 20px 0;
        }

        .qr-code img {
            width: 150px;
            height: 150px;
        }

        .info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .id {
            font-size: 18px;
            font-weight: 600;
            color: #666;
        }

        .type {
            font-size: 14px;
            font-weight: 800;
            color: #999;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .footer {
            font-size: 12px;
            color: #aaa;
            margin-top: 15px;
            border-top: 1px dashed #eee;
            width: 100%;
            padding-top: 10px;
        }

        @media print {
            body {
                background: none;
            }

            .label-card {
                box-shadow: none;
                border: 1px solid #000;
                margin: 0;
                position: absolute;
                top: 0;
                left: 0;
            }

            .no-print {
                display: none;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #7367f0;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(115, 103, 240, 0.4);
        }
    </style>
</head>

<body>
    <div class="label-card">
        <div class="header">
            <span class="brand">GHOTME ERP</span>
        </div>

        <div class="qr-code">
            <img src="{{ $qrCodeUrl }}" alt="QR Code">
        </div>

        <div class="info">
            <div class="title">{{ $item->name }}</div>
            <div class="id">SKU: {{ $item->sku ?? '#' . $item->id }}</div>
            <div class="type">Item de Estoque</div>
        </div>

        <div class="footer">
            Localização: {{ $item->location ?? '-' }}
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">Imprimir Etiqueta</button>

</body>

</html>