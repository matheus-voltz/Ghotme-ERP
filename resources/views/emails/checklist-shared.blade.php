<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .button { display: inline-block; padding: 12px 25px; background-color: #7367f0; color: #fff !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { font-size: 12px; color: #777; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Checklist de Entrada</h2>
            <p>Olá, <strong>{{ $inspection->veiculo->client->name ?? 'Cliente' }}</strong>!</p>
        </div>
        
        <p>Seguem os detalhes da inspeção realizada na entrada do seu veículo <strong>{{ $inspection->veiculo->marca }} {{ $inspection->veiculo->modelo }} (Placa: {{ $inspection->veiculo->placa }})</strong>.</p>
        
        <p>Você pode visualizar o checklist completo, incluindo fotos das avarias, clicando no botão abaixo:</p>
        
        <p style="text-align: center;">
            <a href="{{ route('public.checklist.show', $inspection->id) }}" class="button">Ver Checklist Completo</a>
        </p>
        
        <p>Atenciosamente,<br>
        <strong>{{ $inspection->company->name ?? config('app.name') }}</strong></p>
        
        <div class="footer">
            Este é um e-mail automático, por favor não responda.
        </div>
    </div>
</body>
</html>
