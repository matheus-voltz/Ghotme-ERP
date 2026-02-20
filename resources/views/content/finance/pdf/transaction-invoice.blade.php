<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fatura #{{ $transaction->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #7367f0; padding-bottom: 20px; }
        .company-info { float: left; }
        .invoice-info { float: right; text-align: right; }
        .clear { clear: both; }
        .section-title { background: #f8f9fa; padding: 10px; font-weight: bold; margin-top: 20px; border-radius: 5px; }
        .details { margin-top: 20px; width: 100%; border-collapse: collapse; }
        .details th { text-align: left; background: #eee; padding: 10px; }
        .details td { padding: 10px; border-bottom: 1px solid #eee; }
        .total { margin-top: 30px; text-align: right; }
        .total h2 { color: #7367f0; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h2 style="color: #7367f0; margin-bottom: 5px;">{{ $company->name }}</h2>
                <p>{{ $company->email }} | {{ $company->phone }}</p>
            </div>
            <div class="invoice-info">
                <h1 style="margin: 0; color: #444;">FATURA</h1>
                <p>#TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}<br>
                Data: {{ $transaction->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="clear"></div>

        <div class="section-title">DADOS DO CLIENTE</div>
        <p>
            <strong>{{ $transaction->client->name ?? $transaction->client->company_name ?? 'Cliente Avulso' }}</strong><br>
            {{ $transaction->client->email ?? '' }}<br>
            {{ $transaction->client->phone ?? $transaction->client->whatsapp ?? '' }}
        </p>

        <div class="section-title">DETALHES DO LANÇAMENTO</div>
        <table class="details">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Vencimento</th>
                    <th style="text-align: right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->due_date->format('d/m/Y') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            <p>Subtotal: R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
            <h2>Total: R$ {{ number_format($transaction->amount, 2, ',', '.') }}</h2>
            <p style="text-transform: uppercase; font-size: 12px;">Status: <strong>{{ $transaction->status === 'paid' ? 'PAGO' : 'PENDENTE' }}</strong></p>
        </div>

        <div class="footer">
            <p>Obrigado por utilizar nossos serviços!<br>
            Este documento é um recibo gerado automaticamente pelo sistema Ghotme ERP.</p>
        </div>
    </div>
</body>
</html>
