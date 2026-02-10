@extends('layouts/layoutMaster')

@section('title', 'Chat Suporte')

@section('vendor-style')
@vite('resources/assets/vendor/libs/maxLength/maxLength.scss')
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('page-script')
@vite('resources/assets/js/app-chat.js')
@endsection

@section('content')
  @livewire('support-chat')
@endsection