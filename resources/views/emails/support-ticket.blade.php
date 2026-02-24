<h2>Novo Chamado de Suporte</h2>

<p><strong>De:</strong> {{ $user->name }} ({{ $user->email }})</p>
<p><strong>Empresa ID:</strong> {{ $user->company_id }}</p>
<hr>
<p><strong>Assunto:</strong> {{ $ticketData['subject'] }}</p>
<p><strong>Prioridade:</strong> {{ strtoupper($ticketData['priority']) }}</p>
<p><strong>Mensagem:</strong></p>
<p>{!! nl2br(e($ticketData['message'])) !!}</p>
<hr>
<p><small>Este e-mail foi gerado automaticamente pelo sistema Ghotme ERP.</small></p>
