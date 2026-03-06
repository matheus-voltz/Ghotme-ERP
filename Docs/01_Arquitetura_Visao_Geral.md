# 01 - Arquitetura e Visão Geral

O **Ghotme ERP** é construído sobre uma base robusta e moderna, utilizando o estado da arte do ecossistema PHP e JavaScript/TypeScript.

## 🛠️ Stack Tecnológica

### Backend (Core)

* **Framework**: [Laravel 12+](https://laravel.com/) - Gerenciamento de rotas, banco de dados (Eloquent), segurança e APIs.
* **Autenticação**: Laravel Jetstream com Fortify e Sanctum.
* **Database**: PostgreSQL/MySQL (Suporte a múltiplos drivers).
* **Broadcasting**: Laravel Reverb para atualizações em tempo real via WebSockets.
* **Cache/Queue**: Redis.

### Frontend (Admin Web)

* **Templating**: Blade + Livewire 3 (Interatividade sem complexidade excessiva de JS).
* **Design System**: [Vuexy Admin Template](https://pixinvent.com/demo/vuexy-bootstrap-html-admin-template/) (Bootstrap 5).
* **Asset Bundler**: Vite.

### Mobile

* **Framework**: Expo SDK 54 / React Native 0.81.
* **Linguagem**: TypeScript.
* **Navegação**: Expo Router (File-based routing).
* **Componentes**: Reanimated 4, Lucide Icons/Ionicons.

---

## 🏛️ Padrões Arquiteturais

### 1. Multi-Nicho (Dynamic Business Logic)

O diferencial do Ghotme é a capacidade de se transformar conforme o negócio do cliente.

* **NicheHelper**: Um singleton que identifica o nicho da empresa (`food_service`, `mechanic_workshop`, `general_services`) e altera dinamicamente nomes de labels (ex: "Mesas" vs "Veículos"), menus e permissões.

### 2. Service Layer

A lógica pesada não reside nos Controllers. Utilizamos `app/Services` para encapsular regras de negócio complexas (ex: Cálculo de Comissões, Fechamento de Caixa, Aprovação de Orçamentos).

### 3. API-First (Partial)

Embora a interface Admin use Livewire, as funcionalidades principais são expostas via rotas `api.php` para consumo do aplicativo móvel, garantindo que o núcleo do sistema seja compartilhado.

---

## 📂 Estrutura de Diretórios (Destaques)

* `app/Models`: Representação do banco de dados e relacionamentos.
* `app/Http/Controllers/Api`: Endpoints consumidos pelo Mobile.
* `app/Helpers`: Funções utilitárias globais (NicheHelper, CurrencyFormatter).
* `mobile/app`: Estrutura de rotas e telas do aplicativo React Native.
* `resources/views`: Templates Blade do painel administrativo.
* `Docs`: Repositório de documentação técnica (este local).

---

## 🔐 Segurança

O sistema implementa múltiplos níveis de proteção:

* **CSRF Protection** em formulários web.
* **Sanctum Tokens** para autenticação móvel segura.
* **Policies & Gates**: Controle granular de acesso baseado em Roles/Permissions.
* **Logs & Auditoria**: Rastreamento de ações via `owen-it/laravel-auditing`.
