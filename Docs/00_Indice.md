# 📚 Documentação do Sistema Ghotme ERP

Bem-vindo à documentação técnica oficial do **Ghotme ERP**. Este sistema é uma solução modular de gestão empresarial de alto desempenho, projetada para atender múltiplos nichos (Food Service, Oficinas, Prestadores de Serviço, etc.) através de uma arquitetura flexível e escalável.

## 🗂️ Estrutura da Documentação

Para facilitar a navegação, a documentação foi dividida nos seguintes módulos:

### 1. [Arquitetura e Visão Geral](./01_Arquitetura_Visao_Geral.md)

* Stack Tecnológica (Laravel 12 + Expo/React Native).
* Estrutura de pastas e padrões de código.
* Conceito de Multi-Nicho e Tenancy.

### 2. [Backend (Laravel)](./02_Backend_Laravel.md)

* Modelos principais (OS, Financeiro, Itens, CRM).
* Autenticação e Permissões (Jetstream + Sanctum).
* Serviços e Helpers customizados.
* Broadcasting com Laravel Reverb.

### 3. [Frontend Web (Livewire + Vuexy)](./03_Frontend_Web.md)

* Uso do template Vuexy.
* Padrões de DataTables e Offcanvas.
* Integração com Assets e Vite.

### 4. [Aplicativo Móvel (Expo)](./04_Aplicativo_Mobile.md)

* Navegação com Expo Router.
* Integrações nativas (Bluetooth, Biometria, Push Notifications).
* Sincronização de dados offline/online.

### 5. [Sistema de Nichos e Customização](./05_Sistema_Nichos.md)

* Como funciona a troca dinâmica de labels e regras de negócio.
* `NicheHelper` e lógica de contextualização.

### 6. [Banco de Dados](./06_Banco_de_Dados.md)

* Esquema das tabelas principais.
* Relacionamentos e integridade.

### 7. [Guia de Desenvolvimento](./07_Guia_Desenvolvimento.md)

* Como criar um novo módulo do zero.
* Padrões de commit e workflow.
* Ambiente Docker (Sail).

---

> [!NOTE]
> Esta documentação é mantida de forma contínua. Para dúvidas técnicas, consulte o time de arquitetura.
