@php
$configData = Helper::appClasses();
$isFront = true;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Termos de Uso - Ghotme')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y mt-12">
    <div class="card shadow-none border">
        <div class="card-body p-10">
            <div class="text-center mb-8">
                <h3 class="mb-2">Termos de Uso ✨</h3>
                <p class="text-muted">Última atualização: Fevereiro de 2024</p>
            </div>

            <div class="terms-content">
                <h5>1. Aceitação dos Termos</h5>
                <p>Ao acessar e usar o Ghotme ERP, você concorda em cumprir e estar vinculado a estes Termos de Uso. Se você não concordar com qualquer parte destes termos, você não deve usar nossos serviços.</p>

                <h5 class="mt-6">2. Uso do Serviço</h5>
                <p>O Ghotme é um sistema de gestão empresarial. Você é responsável por manter a confidencialidade de sua conta e senha e por todas as atividades que ocorrem sob sua conta.</p>

                <h5 class="mt-6">3. Propriedade Intelectual</h5>
                <p>Todo o conteúdo, marcas e dados presentes no Ghotme são de propriedade exclusiva da nossa empresa ou licenciadores.</p>

                <h5 class="mt-6">4. Limitação de Responsabilidade</h5>
                <p>O serviço é fornecido "como está". Não garantimos que o serviço será ininterrupto ou livre de erros.</p>

                <hr class="my-10">

                <p class="text-center mb-0">Dúvidas? Entre em contato pelo e-mail: <a href="mailto:suporte@ghotme.com.br">suporte@ghotme.com.br</a></p>
            </div>
        </div>
    </div>
</div>
@endsection