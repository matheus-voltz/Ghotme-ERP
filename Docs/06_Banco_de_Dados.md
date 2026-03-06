# 06 - Banco de Dados (Database)

O banco de dados do Ghotme ERP é otimizado para integridade referencial e alta performance em consultas de múltiplos inquilinos (Tenants).

## 🗃️ Tabelas Nucleares

### 🏢 Organização e Segurança

* **users**: Usuários do sistema, roles e preferências.
* **companies**: Dados cadastrais da empresa matriz e filiais.
* **tenants**: Configurações de isolamento de dados.

### 🛠️ Core Business (OS & Orçamentos)

* **ordem_servicos**: Tabela mestre das Ordens de Serviço (Status, Datas, Totais).
* **ordem_servico_items**: Serviços prestados em cada OS.
* **ordem_servico_parts**: Peças ou insumos utilizados.
* **budgets**: Orçamentos prévios que podem ser convertidos em OS.

### 📦 Estoque e Produtos

* **inventory_items**: Cadastro global de produtos e peças.
* **stock_movements**: Histórico completo de auditoria de estoque (entrada/saída).
* **menu_categories**: Categorias para organização visual (Food e General).

### 👥 CRM e Atendimento

* **clients**: Cadastro unificado de clientes (Física/Jurídica).
* **vehicles**: Entidades vinculadas aos clientes (Veículos, Pets, Propriedades). *Depende do Nicho.*
* **leads**: Gestão de prospecção.

### 💰 Financeiro

* **financial_transactions**: Lançamentos de contas a pagar e receber.
* **cash_registers**: Sessões de caixa (abertura e fechamento).
* **cash_register_movements**: Detalhamento de cada movimentação de dinheiro/cartão no PDV.

---

## 🔗 Relacionamentos Principais

* **Empresa (Company) -> Muitos Clientes**: Isolamento por `company_id`.
* **Cliente (Client) -> Muitos Veículos/Entidades**: O cliente é o proprietário.
* **Veículo (Vehicle) -> Muitas OS**: Histórico completo de manutenção por entidade.
* **OS -> Muitas Transações**: Uma OS pode gerar vários recebimentos (ex: Parcelamento).

---

## 🔍 Convenções

* **Soft Deletes**: Quase todas as tabelas usam `DeletedAt` para evitar perda acidental de dados.
* **Auditoria**: Logs automáticos de quem criou/editou registros via `laravel-auditing`.
* **UUIDs**: Utilizados em colunas de ID expostas externamente (API/Portal) por segurança.
