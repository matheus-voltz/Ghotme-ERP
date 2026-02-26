<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #7367f0;">Ghotme Newsletter</h2>
        </div>
        
        <div class="content">
            {!! $content !!}
        </div>

        <div class="footer">
            <p>Você recebeu este e-mail porque se inscreveu na newsletter do Ghotme.</p>
            <p>&copy; {{ date('Y') }} Ghotme - Gestão Inteligente</p>
        </div>
    </div>
</body>
</html>
