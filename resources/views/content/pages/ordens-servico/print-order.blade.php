<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0;
            padding: 5px;
            font-size: 12px;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .order-info {
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
        }
        .items-table td {
            padding: 5px 0;
        }
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 5px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        .no-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #7367f0;
            color: #fff;
            text-align: center;
            text-decoration: none;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <a href="javascript:window.close()" class="no-print">FECHAR E VOLTAR</a>

    <div class="header">
        <h2 style="margin: 0;">{{ $company->name ?? 'Ghotme ERP' }}</h2>
        <p style="margin: 5px 0;">{{ get_current_niche() === 'food_service' ? 'COMANDA DE PEDIDO' : 'ORDEM DE SERVIÇO' }}</p>
    </div>

    <div class="order-info">
        <b>PEDIDO: #{{ $order->id }}</b><br>
        DATA: {{ $order->created_at->format('d/m/Y H:i') }}<br>
        CLIENTE: {{ $order->client->name ?? 'Consumidor Final' }}<br>
        {{ get_current_niche() === 'food_service' ? 'MESA/IDENT:' : 'ENTIDADE:' }} {{ $order->veiculo->placa ?? $order->description ?? 'N/A' }}
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>QTD</th>
                <th>ITEM</th>
                <th style="text-align: right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ (int)$item->quantity }}x</td>
                <td>{{ $item->service->name }}</td>
                <td style="text-align: right;">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            @foreach($order->parts as $part)
            <tr>
                <td>{{ (int)$part->quantity }}x</td>
                <td>{{ $part->inventoryItem->name }}</td>
                <td style="text-align: right;">{{ number_format($part->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        TOTAL: R$ {{ number_format($order->total, 2, ',', '.') }}
    </div>

    <div class="footer">
        <img src="{{ $qrCodeUrl }}" style="width: 80px; height: 80px;"><br>
        ACOMPANHE SEU PEDIDO PELO QR CODE<br>
        Obrigado pela preferência!
    </div>
</body>
</html>