@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
    @if($company && $company->logo_path)
    <img src="{{ config('app.url') . '/storage/' . $company->logo_path }}" alt="{{ $company->name }}" style="max-height: 60px; margin-bottom: 20px;">
    @else
    <h2 style="color: #7367F0; margin-bottom: 5px;">{{ $company->name ?? config('app.name') }}</h2>
    @endif
</div>

# Olá, {{ $name }}! 👋

Seja muito bem-vindo à equipe! Sua conta no sistema **{{ $company->name ?? config('app.name') }}** foi configurada e já está pronta para uso.

Abaixo estão suas credenciais exclusivas para acesso ao painel administrativo e ao aplicativo mobile:

<div style="background-color: #f8f7fa; padding: 25px; border-radius: 12px; border: 1px solid #e6e6e8; margin: 25px 0;">
    <p style="margin: 0; color: #5d596c;"><strong>E-mail de Acesso:</strong></p>
    <p style="margin: 0 0 15px 0; color: #7367F0; font-weight: bold; font-size: 18px;">{{ $email }}</p>

    <p style="margin: 0; color: #5d596c;"><strong>Senha Temporária:</strong></p>
    <p style="margin: 0; color: #7367F0; font-weight: bold; font-size: 18px; font-family: monospace;">{{ $password }}</p>
</div>

@component('mail::button', ['url' => config('app.url') . '/login', 'color' => 'primary'])
Acessar Painel Agora
@endcomponent

### 📱 Acesso Mobile
Você também pode utilizar estas mesmas credenciais para entrar no nosso aplicativo exclusivo para a equipe.

---

<p style="font-size: 13px; color: #a5a3ae; text-align: center;">
    Por motivos de segurança, recomendamos que você altere sua senha após o primeiro acesso realizado no painel web.<br><br>
    © {{ date('Y') }} {{ $company->name ?? config('app.name') }}. Todos os direitos reservados.
</p>
@endcomponent