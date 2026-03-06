# 03 - Frontend Web

O painel administrativo do Ghotme ERP é focado em produtividade e clareza visual, utilizando o template **Vuexy** como base.

## 🎨 Design System

* **Framework CSS**: Bootstrap 5.
* **Interatividade**: [Livewire 3](https://livewire.laravel.com/) para componentes dinâmicos (formulários reativos, tabelas simples).
* **DataTables**: Para listagens complexas com milhares de registros, utilizamos o plugin jQuery DataTables integrado via Ajax para performance otimizada.

## 🧱 Componentes Padrão

### 1. DataTables (AJAX Mode)

Localizados em `resources/js/laravel-*.js`.

* Suporte a ordenação no servidor.
* Busca global e filtros avançados.
* Renderização customizada de badges e ações (Edit/Delete).

### 2. Offcanvas Forms

A maioria das criações/edições rápidas ocorre em painéis laterais (Offcanvas) para não tirar o usuário do contexto da listagem atual.

### 3. SweetAlert2

Utilizado para confirmações críticas (ex: "Tem certeza que deseja excluir esta OS?") e notificações de sucesso/erro.

---

## 🏗️ Estrutura de Views (Blade)

* `layouts/layoutMaster.blade.php`: O layout principal que carrega a sidebar, navbar e scripts base.
* `content/pages/`: Pasta contendo as telas específicas de cada módulo.
* `resources/assets/`: Contém os plugins específicos (JS/CSS) que são injetados sob demanda via `@vite`.

---

## 🔄 Fluxo de Desenvolvimento de UI

Ao criar uma nova tela, o desenvolvedor deve seguir este padrão:

1. **Blade Route**: Definir a rota no `web.php`.
2. **Breadcrumbs**: Configurar os breadcrumbs no controller para navegação.
3. **Vite Assets**: Incluir os estilos e scripts necessários na seção `@section('vendor-style')`.
4. **JS Modular**: Criar o arquivo JS específico em `resources/js` para gerenciar a lógica de front daquela página.

---

## ⚡ Livewire vs JS Puro

* **Livewire**: Usado para componentes que exigem estado compartilhado com o servidor sem recarregar a página (ex: Seleção de peças em tempo real na OS).
* **jQuery/JS**: Usado para manipulação pesada de DOM em tabelas e plugins legados do template Vuexy.
