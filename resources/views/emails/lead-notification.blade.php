<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
        }

        .header {
            background: #7367f0;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .field {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #7367f0;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Novo Lead Recebido! 🚀</h2>
        </div>
        <div class="content">
            <p>Olá, um novo contato foi realizado através da landing page do Ghotme.</p>

            <div class="field">
                <span class="label">Nome:</span> {{ $lead->name }}
            </div>
            <div class="field">
                <span class="label">E-mail:</span> {{ $lead->email }}
            </div>
            <div class="field">
                <span class="label">WhatsApp:</span> {{ $lead->whatsapp ?? 'Não informado' }}
            </div>
            <div class="field">
                <span class="label">Assunto:</span> {{ $lead->subject ?? 'Não informado' }}
            </div>
            <div class="field">
                <span class="label">Mensagem:</span><br>
                {{ $lead->message }}
            </div>
        </div>
        <div class="footer">
            Este é um e-mail automático enviado pelo sistema Ghotme ERP.
        </div>
    </div>
</body>

</html>