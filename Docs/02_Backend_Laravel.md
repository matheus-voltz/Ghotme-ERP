# 02 - Backend (Laravel)

O backend do Ghotme ERP é o motor central de inteligência e persistência de dados.

## 📦 Módulos Principais (Models & Business Logic)

### 🛠️ Gestão de Ordens de Serviço (OS)

O coração do ERP. Gerenciado via `OrdemServico` e `OrdemServicoService`.

* **Fluxo**: Orçamento -> Aprovação -> Execução -> Faturamento.
* **Itens e Peças**: Suporte a adição dinâmica de serviços (`OrdemServicoItem`) e peças/produtos (`OrdemServicoPart`).
* **Checklists**: Integração com `OsTechnicalChecklist` para inspeção técnica conforme o nicho.

### 💰 Financeiro (Financial)

* **Transações**: `FinancialTransaction` registra toda entrada e saída.
* **Caixa**: `CashRegister` e `CashRegisterMovement` controlam o fechamento de caixa diário.
* **Pagamentos**: Integração com métodos de pagamento (`PaymentMethod`) e gateways externos via `AsaasService`.

### 📦 Estoque (Inventory)

* **Itens**: `InventoryItem` armazena produtos e peças.
* **Movimentações**: `StockMovement` rastreia entradas e saídas.
* **Dedução Automática**: O `StockDeductionService` garante que ao vender um item na OS ou PDV, o estoque seja baixado corretamente.

### 🤖 Inteligência Artificial (AI)

O sistema utiliza IA para análise de negócios e suporte.

* **AiToolsService**: Integração com LLMs para gerar relatórios inteligentes e responder dúvidas sobre o ERP.
* **Business Memory**: `AiBusinessMemory` armazena o contexto da empresa para que a IA dê respostas personalizadas.

---

## 🚀 Camada de Serviços (app/Services)

Seguimos o padrão de "Skinny Controllers, Fat Services":

* **OrdemServicoService**: Encapsula a lógica de transição de status de OS.
* **AsaasService**: Gerencia cobranças via Pix/Boleto/Cartão.
* **FiscalService**: Preparado para emissão de notas fiscais (NFe/NFSe).
* **StockDeductionService**: Centraliza a regra de baixa de materiais.

---

## 🌐 API (Mobile Backend)

Localizada em `app/Http/Controllers/Api`, a API é protegida por **Laravel Sanctum**.

| Endpoint | Descrição |
| :--- | :--- |
| `/api/login` | Autenticação via Email/Senha. |
| `/api/ordens-servico` | Listagem e criação de OS. |
| `/api/inventory` | Consulta de estoque e scanners de código de barras. |
| `/api/dashboard` | Dados resumidos para os gráficos mobile. |
| `/api/ai/report` | Relatório gerado por IA para o empresário. |

---

## ⚙️ Configurações & Tenancy

* **TenantManager**: Identifica a empresa logada e filtra todos os dados automaticamente (Global Scopes).
* **CompanySetting**: Armazena preferências específicas como logotipo, cores do portal do cliente e horários de funcionamento.
