# 04 - Aplicativo Móvel (Mobile)

O aplicativo móvel do Ghotme é desenvolvido com **Expo** e focado na operação de campo (oficina, salão, atendimento externo).

## 🚀 Tecnologias

* **Expo SDK 54**: Framework para desenvolvimento nativo cross-platform.
* **Expo Router**: Sistema de roteamento baseado em arquivos (semelhante ao Next.js).
* **Reanimated 4**: Animações de alta performance a 60 FPS.
* **Lucide React Native**: Conjunto de ícones premium.

---

## 🏗️ Gerenciamento de Estado (Contexts)

Utilizamos a **API de Contexto do React** para gerenciar o estado global:

* **AuthContext**: Gerencia o token JWT, login biométrico e dados do usuário logado.
* **NicheContext**: Similar ao backend, adapta as strings do app conforme o nicho da empresa logada.
* **DeviceContext**: Gerencia o pareamento com impressoras térmicas Bluetooth e maquininhas de cartão.
* **ThemeContext**: Suporte a modo claro (light) e escuro (dark) automático.

---

## 📶 Integrações Nativas

### 1. Bluetooth (Printers & POS)

* **Impressão**: Suporte a impressoras térmicas 58mm/80mm para recibos de OS e comprovantes de pagamento.
* **Pagamento**: Integração com maquininhas via Bluetooth (ex: PagSeguro/PlugPag).

### 2. Biometria e Segurança

* **FaceID/Fingerprint**: Uso de `expo-local-authentication` para login rápido após o primeiro acesso.
* **SecureStore**: Armazenamento criptografado de credenciais sensíveis.

### 3. Câmera e Scanner

* Utilizado para escanear `QR Codes` em Ordens de Serviço e códigos de barras de produtos no inventário.

---

## 📩 Notificações Push

* Implementado via `expo-notifications`.
* O backend envia alertas de novas OS, lembretes de estoque baixo e mensagens de chat diretamente para o dispositivo do colaborador ou proprietário.

---

## 📂 Estrutura de Pastas

* `/app/(auth)`: Telas de Login e Recuperação de Senha.
* `/app/(app)`: Área logada (Dashboard, OS, Clientes, Inventário).
* `/components`: Componentes reutilizáveis (Input, Card, Button).
* `/services`: Camada de comunicação com a API (Axios).
* `/assets`: Ícones, splash screens e fontes customizadas.
