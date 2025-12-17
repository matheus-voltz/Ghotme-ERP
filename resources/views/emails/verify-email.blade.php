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
  <title>Verificação de e-mail</title>

  <link href="https://fonts.googleapis.com/css?family=Montserrat:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700" rel="stylesheet" media="screen">

  <style>
    .hover-underline:hover {
      text-decoration: underline !important;
    }
    @media (max-width: 600px) {
      .sm-w-full { width: 100% !important; }
      .sm-px-24 { padding-left: 24px !important; padding-right: 24px !important; }
      .sm-py-32 { padding-top: 32px !important; padding-bottom: 32px !important; }
      .sm-leading-32 { line-height: 32px !important; }
    }
  </style>
</head>

<body style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #eceff1;">
  <div style="font-family: 'Montserrat', sans-serif; display: none;">
    Por favor, confirme seu endereço de e-mail
  </div>

  <div role="article" aria-roledescription="email" aria-label="Verificação de E-mail" lang="pt-BR"
       style="font-family: 'Montserrat', sans-serif;">
    <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
      <tr>
        <td align="center" style="background-color: #eceff1;">
          <table class="sm-w-full" style="width: 600px;" cellpadding="0" cellspacing="0">

            <!-- LOGO -->
            <tr>
              <td class="sm-py-32 sm-px-24" style="padding: 48px; text-align: center;">
                <a href="{{ config('app.url') }}">
                  <img src="{{ asset('images/logo.png') }}" width="155" alt="{{ config('app.name') }}">
                </a>
              </td>
            </tr>

            <!-- CONTEÚDO -->
            <tr>
              <td class="sm-px-24">
                <table style="width: 100%;" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-radius: 4px; background-color: #ffffff; padding: 48px; text-align: left; font-size: 16px; line-height: 24px; color: #626262;">

                      <p style="margin-bottom: 0; font-size: 20px; font-weight: 600;">
                        Olá
                      </p>

                      <p style="margin-top: 0; font-size: 24px; font-weight: 700; color: #ff5850;">
                        {{ $user->name }}!
                      </p>

                      <p class="sm-leading-32" style="margin-bottom: 16px; font-size: 24px; font-weight: 600; color: #263238;">
                        Obrigado por se cadastrar! 👋
                      </p>

                      <p style="margin-bottom: 24px;">
                        Para concluir seu cadastro, confirme seu endereço de e-mail clicando no botão abaixo.
                      </p>

                      <p style="margin-bottom: 24px;">
                        Caso você não tenha criado uma conta em {{ config('app.name') }},
                        ignore este e-mail ou entre em contato pelo endereço
                        <a href="mailto:{{ config('mail.from.address') }}" class="hover-underline" style="color: #7367f0; text-decoration: none;">
                          {{ config('mail.from.address') }}
                        </a>.
                      </p>

                      <!-- LINK VISÍVEL -->
                      <a href="{{ $url }}" style="margin-bottom: 24px; display: block; color: #7367f0; text-decoration: none;">
                        {{ $url }}
                      </a>

                      <!-- BOTÃO -->
                      <table cellpadding="0" cellspacing="0">
                        <tr>
                          <td style="border-radius: 4px; background-color: #7367f0;">
                            <a href="{{ $url }}"
                               style="display: block; padding: 16px 24px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none;">
                              Verificar e-mail agora →
                            </a>
                          </td>
                        </tr>
                      </table>

                      <table style="width: 100%;" cellpadding="0" cellspacing="0">
                        <tr>
                          <td style="padding: 32px 0;">
                            <div style="height: 1px; background-color: #eceff1;"></div>
                          </td>
                        </tr>
                      </table>

                      <p style="margin-bottom: 16px;">
                        Não reconhece este e-mail?
                        <a href="mailto:{{ config('mail.from.address') }}" class="hover-underline" style="color: #7367f0; text-decoration: none;">
                          Avise-nos
                        </a>.
                      </p>

                      <p style="margin-bottom: 16px;">
                        Obrigado,<br>
                        Equipe {{ config('app.name') }}
                      </p>

                    </td>
                  </tr>

                </table>
              </td>
            </tr>

          </table>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>