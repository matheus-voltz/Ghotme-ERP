<!DOCTYPE html>
<html lang="pt-BR" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <!--[if mso]>
    <xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch></o:officedocumentsettings></xml>
  <![endif]-->
    <title>Redefinição de Senha | Ghotme ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet" media="screen">
    <style>
        body {
            margin: 0;
            width: 100%;
            padding: 0;
            word-break: break-word;
            -webkit-font-smoothing: antialiased;
            background-color: #f4f7f9;
            font-family: 'Outfit', -apple-system, blinkmacsystemfont, 'Segoe UI', roboto, helvetica, arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 600px) {
            .email-container {
                border-radius: 0 !important;
            }

            .email-header {
                padding: 40px 20px !important;
            }

            .email-body {
                padding: 32px 20px !important;
            }
        }
    </style>
</head>

<body style="background-color: #f4f7f9; padding: 40px 0;">
    <div style="display: none;">Você solicitou a redefinição de sua senha no Ghotme ERP.</div>

    <div role="article" aria-roledescription="email" aria-label="Redefinição de Senha" lang="pt-BR">
        <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <div class="email-container">
                        <!-- Header with Gradient -->
                        <div class="email-header" style="background: linear-gradient(135deg, #7367f0 0%, #a06df4 100%); padding: 40px 40px; text-align: center;">
                            <div style="margin-bottom: 24px;">
                                <img src="{{ asset('assets/img/ghotme-official.png') }}" alt="Ghotme Logo" style="height: 120px; display: inline-block;">
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="email-body" style="padding: 48px; text-align: left;">
                            <div style="font-size: 20px; font-weight: 600; margin-bottom: 12px; color: #1a1a1a;">Olá! 👋</div>
                            <p style="font-size: 16px; color: #5a6a85; line-height: 1.6; margin-bottom: 32px;">
                                Recebemos uma solicitação para redefinir a senha da sua conta no Ghotme ERP. Para sua segurança, use o código abaixo para validar a redefinição:
                            </p>

                            <div style="text-align: center; margin: 40px 0; padding: 30px; background: #f1f0ff; border: 2px dashed #7367f0; border-radius: 16px;">
                                <div style="font-size: 14px; color: #7367f0; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">SEU CÓDIGO DE SEGURANÇA</div>
                                <div style="font-size: 42px; font-weight: 800; color: #7367f0; letter-spacing: 12px; font-family: 'Courier New', Courier, monospace;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <div style="text-align: center; margin: 40px 0;">
                                <p style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;">Você também pode clicar no botão abaixo para ir direto à página:</p>
                                <a href="{{ $url }}" style="display: inline-block; background: linear-gradient(135deg, #7367f0 0%, #a06df4 100%); color: #ffffff; padding: 18px 42px; border-radius: 14px; text-decoration: none; font-weight: 600; font-size: 16px; box-shadow: 0 10px 20px rgba(115, 103, 240, 0.25);">Redefinir minha Senha agora</a>
                            </div>

                            <p style="font-size: 14px; color: #5a6a85; line-height: 1.6; margin-top: 32px;">
                                Este link e código de segurança expiram em 60 minutos. Se você não solicitou esta alteração, ignore este e-mail.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div style="padding: 32px 48px; text-align: center; background: #fcfdfe; border-top: 1px solid #f1f4f9;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0; line-height: 1.5;">
                                © {{ date('Y') }} Ghotme ERP. Todos os direitos reservados.<br>
                                Você recebeu este e-mail porque solicitou a redefinição de senha em <a href="{{ config('app.url') }}" style="color: #94a3b8; text-decoration: underline;">ghotme.com.br</a>
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>