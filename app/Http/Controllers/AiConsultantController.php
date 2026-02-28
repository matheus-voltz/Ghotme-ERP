<?php

namespace App\Http\Controllers;

use App\Models\AiConsultantChat;
use App\Models\AiConsultantMessage;
use App\Models\FinancialTransaction;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AiConsultantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $chats = AiConsultantChat::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('content.pages.ai-consultant.index', compact('chats'));
    }

    public function createChat()
    {
        $user = Auth::user();
        $chat = AiConsultantChat::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'title' => 'Nova Consultoria ' . now()->format('d/m H:i')
        ]);

        return redirect()->route('ai-consultant.show', $chat->id);
    }

    public function show($id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);
        $messages = $chat->messages;

        $chats = AiConsultantChat::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('content.pages.ai-consultant.chat', compact('chat', 'messages', 'chats'));
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);
        $companyId = $user->company_id;
        $company = $user->company;

        // Plano e Limites
        if (!$user->hasFeature('ai_analysis')) {
            return response()->json(['success' => false, 'message' => 'Plano insuficiente.'], 403);
        }

        if (!$user->hasFeature('ai_unlimited')) {
            $monthKey = now()->format('Y-m');
            $usageKey = "ai_usage_{$companyId}_{$monthKey}";
            $usageCount = Cache::get($usageKey, 0);

            if ($usageCount >= 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limite mensal de 10 consultas atingido no plano Padrão.'
                ], 403);
            }
            Cache::put($usageKey, $usageCount + 1, now()->addMonth());
        }

        $userMessage = $request->input('message');

        // Salva mensagem do usuário
        $chat->messages()->create([
            'role' => 'user',
            'content' => $userMessage
        ]);

        // Busca contexto do negócio
        $nicheKey = get_current_niche();
        $nichesNames = [
            'workshop' => 'Oficina Mecânica',
            'automotive' => 'Centro Automotivo',
            'electronics' => 'Assistência Técnica de Eletrônicos',
            'pet' => 'Pet Shop e Clínica Veterinária',
            'beauty_clinic' => 'Clínica de Estética',
            'construction' => 'Construtora e Empreiteira'
        ];
        $nicheName = $nichesNames[$nicheKey] ?? $nicheKey;

        $revenue = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
        $expenses = FinancialTransaction::where('company_id', $companyId)->where('type', 'out')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
        $pendingOS = OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count();

        $companyName = $company->name ?? 'sua empresa';

        // ═══════════════════════════════════════════════════════════════
        // BASE DE CONHECIMENTO COMPLETA DO GHOTME ERP
        // ═══════════════════════════════════════════════════════════════
        $systemKnowledge = "
## MANUAL COMPLETO DO SISTEMA GHOTME ERP

### O QUE É O GHOTME ERP?
O Ghotme ERP é um sistema SaaS (Software como Serviço) multitenant e multi-nicho para empresas de prestação de serviço. Ele se adapta automaticamente ao setor da empresa (Oficina Mecânica, Centro Automotivo, Assistência Técnica, Pet Shop/Clínica Veterinária, Clínica de Estética, Construtora). Cada nicho tem nomenclaturas e fluxos adaptados (ex.: 'veículo' vira 'pet' para um Pet Shop).

---

### MÓDULO 1 - DASHBOARD (Painel Principal)
**Onde acessar:** Menu lateral > Dashboard (rota /dashboard)

**O que exibe:**
- **Receita do Mês:** Total de transações do tipo 'entrada' com status 'pago' no mês atual.
- **Despesas do Mês:** Total de transações do tipo 'saída' com status 'pago' no mês atual.
- **Ticket Médio:** Valor médio das Ordens de Serviço finalizadas no mês.
- **Lucratividade:** Diferença entre receita e despesas.
- **Gráfico de Receita (7 dias):** Evolução diária do faturamento da última semana.
- **Ordens de Serviço por Status:** Contador de OS pendentes, em andamento e finalizadas.
- **Ações Rápidas:** Botões para criar nova OS, novo cliente, novo orçamento.
- **Análise IA:** Botão para gerar uma análise estratégica do negócio com base nos dados do mês.

---

### MÓDULO 2 - ORDENS DE SERVIÇO (OS)
**Onde acessar:** Menu lateral > Ordens de Serviço (rota /ordens-servico)

**Fluxo completo de uma OS:**
1. **Criação:** Clique em 'Nova OS'. Selecione o cliente, o ativo dele (veículo/pet/dispositivo), adicione os serviços e peças/produtos. Preencha a descrição do problema.
2. **Status disponíveis:** `Pendente` → `Em Manutenção` → `Em Teste` → `Em Limpeza` → `Pronto para Retirada` → `Finalizado/Pago`. Também existe `Aguardando Aprovação`.
3. **Edição:** Clique no ícone de lápis na lista de OS para editar. É possível alterar serviços, peças, status, KM de entrada.
4. **Ativos automáticos:** Ao atualizar o status para 'Em Manutenção', 'Finalizado' ou 'Pago', o sistema registra automaticamente um evento no histórico do ativo do cliente.
5. **Notificação Push:** O dono da empresa recebe uma notificação push no app mobile quando a OS muda de status.
6. **Campos Personalizados:** É possível adicionar campos extras às OS em: Configurações > Campos Personalizados.
7. **Filtros:** Técnicos (usuários com role 'employee') veem apenas as OS atribuídas a eles. Admins veem todas.

**Checklist de Vistoria (Laudo):**
- **Onde:** Menu Ordens de Serviço > Checklist de Vistoria (rota /ordens-servico/checklist)
- **Como criar:** Clique em 'Novo Checklist', selecione a OS e preencha o laudo visual com pontos de dano e fotos.
- **Compartilhar:** Cada checklist tem uma URL pública (sem login) para o cliente visualizar. Pode enviar por e-mail direto do sistema.

---

### MÓDULO 3 - ORÇAMENTOS
**Onde acessar:** Menu lateral > Orçamentos (rota /budgets/pending)

**Fluxo:**
1. **Criar:** Clique em 'Novo Orçamento', selecione cliente, ativo, adicione serviços e peças com seus valores.
2. **Status:** `Pendente` → `Aprovado` ou `Recusado`.
3. **Enviar por WhatsApp:** Clique no ícone do WhatsApp na lista. O sistema monta a mensagem automaticamente com o resumo e um link de aprovação online.
4. **Portal de Aprovação Online:** O cliente recebe um link único (/view-budget/{uuid}) onde pode ver todos os itens do orçamento e clicar em 'Aprovar' ou 'Recusar' sem precisar de login.
5. **Converter em OS:** Quando aprovado, clique em 'Converter para OS'. O sistema cria a OS automaticamente com todos os serviços e peças do orçamento.
6. **Validade:** A validade padrão do orçamento pode ser configurada em: Configurações > Configurações da OS.

---

### MÓDULO 4 - FINANCEIRO
**Onde acessar:** Menu lateral > Financeiro

**Submódulos:**
- **Contas a Receber** (/finance/accounts-receivable): Lista de todas as entradas (pagas e a receber).
- **Contas a Pagar** (/finance/accounts-payable): Lista de todas as saídas (pagas e a pagar).
- **Fluxo de Caixa** (/finance/cash-flow): Visão mensal com saldo projetado.
- **Relatórios Financeiros** (/finance/reports): Gráficos de receita vs. despesa por período.
- **Métodos de Pagamento** (/finance/payment-methods): Cadastro de formas de pagamento aceitas (Dinheiro, Pix, Cartão, etc.).

**Como lançar uma transação:**
1. Em 'Contas a Receber' ou 'Contas a Pagar', clique no botão '+'.
2. Preencha: descrição, valor, data de vencimento, categoria, método de pagamento.
3. Para marcar como pago: clique no ícone de ✓ ao lado da transação.

**Contabilidade (BPO Fiscal):**
- **Onde:** Menu > Contabilidade (/accounting)
- **Importar OFX:** Importe o extrato bancário em formato OFX para conciliação automática.
- **Portal do Contador:** Gere um token único para o contador acessar os dados sem fazer login no sistema principal (/portal-contador/{token}).

---

### MÓDULO 5 - CRM / CLIENTES
**Onde acessar:** Menu lateral > Clientes (a rota varia pelo nicho: /clientes, /tutores, etc.)

**Funcionalidades:**
- **Cadastro:** Nome, CPF/CNPJ, telefone, WhatsApp, endereço, e-mail.
- **Quick View:** Clique no ícone de olho para ver um resumo do cliente sem sair da tela.
- **Histórico:** Cada cliente tem ativos cadastrados (veículos, pets, dispositivos). Cada ativo tem um histórico completo de atendimentos.
- **Portal do Cliente:** Cada cliente tem uma URL pública única (/portal/{uuid}) onde pode ver suas OS abertas, histórico e conversar com a empresa via chat integrado.
- **Campos Personalizados:** É possível adicionar campos extras aos clientes em: Configurações > Campos Personalizados.
- **Importação em Massa:** Em Configurações > Importação de Dados, você pode importar clientes via planilha Excel (baixe o template primeiro).

---

### MÓDULO 6 - ATIVOS (Veículos / Pets / Dispositivos)
**Onde acessar:** Menu lateral > Veículos / Pets / Dispositivos (varia pelo nicho)

**Funcionalidades:**
- **Cadastro de Ativo:** Vincule o ativo a um cliente. Para veículos: placa, marca, modelo, ano, KM.
- **Dossier:** Clique em 'Dossier' para ver o histórico completo cronológico de todos os atendimentos daquele ativo.
- **Consulta de Placa:** No cadastro de veículo, ao digitar a placa, o sistema consulta automaticamente os dados do veículo (marca, modelo, ano) via API.
- **Importação em Massa:** Importe veículos via planilha em: Configurações > Importação de Dados.

---

### MÓDULO 7 - ESTOQUE (Inventário)
**Onde acessar:** Menu lateral > Estoque

**Submódulos:**
- **Itens** (/inventory/items): Cadastro de peças, produtos e materiais. Cada item tem: nome, código, preço de custo, preço de venda, quantidade atual, quantidade mínima e fornecedor.
- **Fornecedores** (/inventory/suppliers): Cadastro de fornecedores com CNPJ, contato e condições.
- **Entradas de Estoque** (/inventory/stock-in): Registre a entrada de produtos (compras).
- **Saídas de Estoque** (/inventory/stock-out): Registre saídas manuais.
- **Ajustes** (/inventory/adjustments): Corrija divergências de inventário.
- **Estoque Crítico** (/inventory/critical-stock): Lista automática de itens abaixo do estoque mínimo.
- **Ordens de Compra** (/inventory/purchase-orders): Gere pedidos de compra automáticos para itens em estoque crítico.
- **Histórico de Movimentações** (/inventory/movements-history): Log completo de todas as entradas e saídas.
- **Importação em Massa:** Importe itens de estoque via planilha em: Configurações > Importação de Dados.

---

### MÓDULO 8 - SERVIÇOS E PACOTES
**Onde acessar:** Menu lateral > Serviços

**Submódulos:**
- **Tabela de Serviços** (/services/table): Cadastro de serviços oferecidos com nome, descrição, preço e status (ativo/inativo).
- **Pacotes de Serviço** (/services/packages): Agrupe múltiplos serviços em pacotes promocionais com desconto.

---

### MÓDULO 9 - KANBAN
**Onde acessar:** Menu lateral > Kanban (rota /kanban)

**O que é:** Um quadro visual de cards onde cada coluna representa uma etapa do processo. Ideal para gerenciar o fluxo visual das OS.

**Como usar:**
- **Criar Coluna (Board):** Clique em '+' para adicionar uma nova etapa ao fluxo.
- **Criar Card (Item):** Dentro de uma coluna, clique em '+' para adicionar um card. Você pode vincular uma OS, atribuir a um técnico, definir prioridade e prazo.
- **Mover Card:** Arraste e solte o card para mudar de etapa (coluna).
- **Detalhes do Card:** Clique no card para ver detalhes, adicionar comentários e ver o histórico de atividades.
- **Atribuir Técnico:** No detalhe do card, é possível atribuir o serviço a um membro da equipe.

---

### MÓDULO 10 - CALENDÁRIO
**Onde acessar:** Menu lateral > Calendário (rota /calendar)

**Como usar:**
- Visualize seus agendamentos em formato mensal, semanal ou diário.
- Clique em um dia para criar um novo evento.
- Eventos podem ser vinculados a clientes e categorias.
- **Agendamento Online (Site Público):** Acesse Configurações > Integrações para ativar a página pública de agendamento. Clientes acessam /agendar/{slug-da-empresa} para marcar horário diretamente, e o evento aparece automaticamente no calendário.

---

### MÓDULO 11 - EQUIPE
**Onde acessar:** Menu lateral > Equipe

**Submódulos:**
- **Colaboradores** (/settings/team OU /team/employees): Cadastre membros da equipe. Cada colaborador recebe um login e senha. Defina o papel: `admin` (acesso total) ou `employee` (acesso limitado às próprias OS).
- **Comissões** (/team/commissions): Registre e controle as comissões a pagar para cada técnico/colaborador com base nas OS finalizadas.
- **Dashboard do Técnico:** Técnicos com papel 'employee' acessam /employee e veem apenas suas próprias OS com um timer para registrar o tempo de execução.

---

### MÓDULO 12 - CONTRATOS DE MANUTENÇÃO
**Onde acessar:** Menu lateral > Contratos (rota /contratos ou /planos para nicho Pet)

**Como usar:** Registre contratos recorrentes com clientes (ex.: frotas, planos mensais). O sistema gera automaticamente novas OS ou faturas mensais para os contratos ativos.

---

### MÓDULO 13 - RELATÓRIOS
**Onde acessar:** Menu lateral > Relatórios

**Relatórios disponíveis:**
- **Lucratividade por Serviço:** Qual serviço dá mais lucro.
- **Status de OS:** Distribuição das OS por status em gráficos.
- **Estoque Consumido:** Quais peças/produtos foram mais utilizados.
- **Faturamento:** Evolução da receita por período.
- **Performance do Técnico:** Produtividade de cada membro da equipe.
- **Custo por Serviço:** Análise de custo x receita por tipo de serviço.
- **Tempo Médio de Atendimento:** Quanto tempo em média cada serviço leva.

---

### MÓDULO 14 - CONFIGURAÇÕES
**Onde acessar:** Menu lateral > Configurações

**Opções:**
- **Dados da Empresa** (/settings/company-data): Nome, CNPJ, logo, endereço, telefone, assinatura de e-mail.
- **Configurações da OS** (/settings/os-settings): Defina validade padrão de orçamentos, configurações de numeração de OS, etc.
- **Campos Personalizados** (/settings/custom-fields): Adicione campos extras ao cadastro de clientes ou de OS (ex.: 'Modelo do Roteador', 'Raça do Pet').
- **Checklist Personalizado** (/settings/custom-checklist): Crie checklists de vistoria com itens personalizados.
- **Templates de Impressão** (/settings/print-templates): Customize o layout das OS, orçamentos e recibos que são impressos ou enviados ao cliente.
- **Integrações** (/settings/integrations): Configure APIs de terceiros (WhatsApp, Mercado Livre, etc.).
- **Importação de Dados** (/settings/import): Importe clientes, veículos, serviços e estoque via planilha Excel.
- **Gestão de Usuários** (/settings/user-management): Gerencie todos os usuários do sistema.
- **Planos e Assinatura** (/settings): Veja seu plano atual, histórico de pagamentos e faça upgrade.

---

### MÓDULO 15 - PLANOS E ASSINATURA
**Onde acessar:** Menu lateral > Configurações > Plano e Assinatura (rota /settings)

**Planos disponíveis:**
- **Teste Grátis (Free):** 30 dias gratuitos com acesso a todas as funcionalidades. Limite de 10 consultas de IA por mês.
- **Plano Padrão:** R$ 149,00/mês ou R$ 1.490,00/ano. Ideal para pequenas e médias empresas.
- **Plano Enterprise:** R$ 279,00/mês ou R$ 2.790,00/ano. Para grandes operações, frotas e múltiplos usuários. Inclui IA ilimitada.

**Métodos de pagamento aceitos:** PIX, Boleto Bancário, Cartão de Crédito (parcelamento disponível no plano anual via cartão).
**Processadora:** Asaas (plataforma de pagamentos brasileira).

**Como assinar:**
1. Em /settings, clique em 'Escolher um Plano'.
2. Selecione o plano desejado (Padrão ou Enterprise) e o período (Mensal ou Anual).
3. Clique em 'Gerar Cobrança' e escolha o método de pagamento.
4. Para PIX: um QR Code e código Copia e Cola serão gerados.
5. Para Cartão: preencha os dados do cartão e o plano é ativado imediatamente.

---

### MÓDULO 16 - CONSULTOR IA (VOCÊ)
**Onde acessar:** Menu lateral > Consultor IA (rota /ai-consultant)

**O que você pode fazer:**
- **Análise de Negócio:** Analise dados reais de receita, despesas e OS pendentes do mês atual.
- **Suporte ao Sistema:** Tire dúvidas sobre como usar qualquer módulo do Ghotme ERP.
- **Estratégia:** Sugira melhorias de processos, formas de aumentar o ticket médio, reduzir custos.
- **Histórico de Chats:** Cada conversa é salva. Você pode criar múltiplos chats para assuntos diferentes.
- **Limites:** No Plano Padrão, são 10 consultas por mês. No Plano Enterprise, consultas ilimitadas.

---

### MÓDULO 17 - SUPORTE
**Onde acessar:** Menu lateral > Suporte

**Opções:**
- **Chat WhatsApp** (/support/chat-whatsapp): Abre conversa direta com o suporte da Ghotme no WhatsApp.
- **Abrir Ticket** (/support/open-ticket): Envie um ticket de suporte formal com descrição do problema.
- **Base de Conhecimento** (/support/knowledge-base): Artigos e tutoriais sobre o sistema.

---

### MÓDULO 18 - NOTIFICAÇÕES
**Onde acessar:** Ícone de sino no topo da página (rota /notifications)

**Tipos de notificações:**
- Mudança de status de OS.
- Novo agendamento recebido pela página pública.
- Estoque crítico atingido.
- Novo ticket de suporte respondido.

**App Mobile:** O sistema possui um aplicativo React Native (Expo) que envia notificações push em tempo real para o celular do dono da empresa sempre que há uma atualização importante.

---

### PERGUNTAS FREQUENTES / COMO FAZER:

**P: Como criar uma nova Ordem de Serviço?**
R: Vá em 'Ordens de Serviço' no menu → clique no botão azul 'Nova OS' → preencha os dados do cliente, selecione o veículo/pet/dispositivo, adicione os serviços e peças, e clique em Salvar.

**P: Como enviar o orçamento para o cliente aprovar online?**
R: Crie o orçamento em 'Orçamentos' → clique no ícone do WhatsApp na lista → o sistema monta a mensagem com o link de aprovação automaticamente → o cliente abre o link, vê os detalhes e clica em Aprovar ou Recusar sem precisar de login.

**P: Como transformar um orçamento aprovado em OS?**
R: Na lista de Orçamentos, clique no ícone 'Converter para OS'. O sistema cria a OS automaticamente com todos os itens do orçamento.

**P: Como controlar o tempo de execução de um serviço?**
R: No Dashboard do Técnico (/employee), o colaborador clica no ícone de cronômetro ao lado da OS. O timer inicia e registra o tempo. Ao finalizar, clica novamente para parar.

**P: Como importar meus clientes/estoque de uma planilha?**
R: Vá em Configurações → Importação de Dados → baixe o template da planilha no formato correto → preencha e faça o upload. O sistema valida e importa os dados.

**P: Como adicionar um campo extra na OS ou no cliente?**
R: Vá em Configurações → Campos Personalizados → clique em 'Novo Campo' → escolha o tipo (texto, número, data, select) e onde ele aparece (Cliente ou OS).

**P: Como ver o histórico completo de atendimentos de um veículo/pet?**
R: Vá no menu de Veículos/Pets → localize o ativo → clique em 'Dossier'. Você verá uma linha do tempo de todos os atendimentos.

**P: Como permitir que meu cliente veja o andamento do serviço?**
R: Cada cliente tem um 'Portal do Cliente' com URL única. Compartilhe o link com ele. Pelo portal, o cliente vê as OS abertas, o histórico e pode enviar mensagens para a equipe pelo chat integrado.

**P: Como cancelar ou mudar o meu plano?**
R: Vá em Configurações → Plano e Assinatura → clique em 'Gerenciar Plano'. Entre em contato com o suporte pelo WhatsApp para processar o cancelamento.
        ";

        $systemPrompt = "Você é o 'Ghotme Advisor' — Consultor Estratégico e Especialista em Suporte Nível 1 do sistema Ghotme ERP, especializado no nicho de {$nicheName}.

Sua dupla missão é:
1. **Consultor de Negócios:** Analise os dados reais da empresa e sugira ações concretas para aumentar a receita, reduzir custos e otimizar processos.
2. **Especialista no Sistema:** Oriente com precisão sobre como usar qualquer funcionalidade do Ghotme ERP, desde a criação de uma OS até configurações avançadas. Use APENAS o conhecimento do manual fornecido. Nunca invente funcionalidades que não existam.

**DADOS EM TEMPO REAL DA EMPRESA (Mês atual):**
- 🏢 Empresa: {$companyName}
- 💰 Receita: R$ " . number_format($revenue, 2, ',', '.') . "
- 💸 Despesas: R$ " . number_format($expenses, 2, ',', '.') . "
- 📋 OS Pendentes: {$pendingOS}
- 🏭 Nicho: {$nicheName}

{$systemKnowledge}

**REGRAS DE COMPORTAMENTO:**
- Responda sempre em Português do Brasil.
- Use Markdown para formatar (negritos, listas, títulos).
- Seja direto, claro e objetivo. Evite respostas genéricas.
- Quando o usuário perguntar 'como fazer algo no sistema', guie passo a passo usando o manual acima.
- Quando o usuário perguntar sobre negócios/finanças, use os dados reais fornecidos.
- Se não souber a resposta, oriente o usuário a contatar o suporte via WhatsApp em: /support/chat-whatsapp.
- Nunca invente preços, rotas ou funcionalidades que não estejam documentadas acima.";

        // Prepara histórico para o Gemini
        $history = $chat->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'role' => $msg->role === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg->content]]
            ];
        })->toArray();

        // Usa o campo systemInstruction nativo da API Gemini para melhor qualidade
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => $history
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');

                // Salva mensagem da IA
                $chat->messages()->create([
                    'role' => 'assistant',
                    'content' => trim($aiText)
                ]);

                // Atualiza timestamp do chat para ordenação
                $chat->touch();

                return response()->json([
                    'success' => true,
                    'message' => $aiText
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro na IA: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'Erro ao processar consulta.'], 500);
    }
}
