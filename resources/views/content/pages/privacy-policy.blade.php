@php
$configData = Helper::appClasses();
$isFront = true;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Política de Privacidade - Ghotme')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y mt-12">
    <div class="card shadow-none border">
        <div class="card-body p-10">
            <div class="text-center mb-8">
                <h3 class="mb-2">Política de Privacidade 🛡️</h3>
                <p class="text-muted">Sua segurança é nossa prioridade.</p>
            </div>

            <div class="privacy-content">
                <h5>1. Coleta de Informações</h5>
                <p>Coletamos informações necessárias para a prestação de nossos serviços de ERP, incluindo dados de cadastro da empresa, informações de contato e dados operacionais inseridos no sistema.</p>

                <h5 class="mt-6">2. Uso dos Dados</h5>
                <p>Seus dados são utilizados exclusivamente para o funcionamento do sistema, suporte técnico, melhoria dos serviços e comunicações importantes sobre sua conta.</p>

                <h5 class="mt-6">3. Proteção de Dados</h5>
                <p>Implementamos medidas de segurança técnicas e organizacionais para proteger seus dados contra acesso não autorizado, perda ou alteração.</p>

                <h5 class="mt-6">4. Compartilhamento com Terceiros</h5>
                <p>Não vendemos nem alugamos seus dados pessoais para terceiros. O compartilhamento ocorre apenas quando necessário para integração com serviços que você autorizar (ex: emissão de notas fiscais, meios de pagamento).</p>

                <h5 class="mt-6">5. Seus Direitos</h5>
                <p>Você tem o direito de acessar, corrigir ou excluir seus dados a qualquer momento através das configurações do sistema ou via suporte.</p>

                <hr class="my-10">

                <p class="text-center mb-0">Dúvidas sobre privacidade? <a href="mailto:contato@ghotme.com.br">contato@ghotme.com.br</a></p>
            </div>
        </div>
    </div>
</div>
@endsection