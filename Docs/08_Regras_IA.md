# 🤖 Regras Estritas para Agentes de IA (AI Agents Rules)

> **ATENÇÃO AGENTE DE IA**: Ao interagir com este projeto, você **DEVE OBRIGATORIAMENTE** ler e seguir as regras abaixo em todos os códigos e refatorações que produzir. Nenhuma exceção será tolerada.

## 1. 🌍 Dinamismo e Internacionalização (i18n) Obrigatória

* **PROIBIDO HARDCODING PESADO**: Nunca coloque textos, títulos ou mensagens fixas (hardcoded) diretamente no HTML/JSX.
* **Backend (Laravel)**: Utilize a função de tradução nativa `__('Meu Texto')` em todas as strings visíveis para o usuário.
* **Mobile (React Native/Expo)**: Utilize o contexto de linguagem (`LanguageContext`) ou a biblioteca de internacionalização definida (ex: `i18n-js`) para todas as view strings. Se não existir, prepare o arquivo abstraindo strings em dicionários, preparando o terreno para uma futura troca de idioma.

## 2. 🎯 Separação Estrita de Nichos (Tenancy/ContextLogic)

* **O sistema é Multi-Nicho**. Não misture regras do nicho *Automotivo* (Placa, Chassi) com regras do nicho *Pet* (Raça, Pelagem) ou *Food Service* (Mesa, Comanda).
* **Backend**: Use o `NicheHelper.php` e `niche_translate()` sempre que a palavra se referir a uma entidade variante (Veículo, Paciente, Mesa, Animal).
* **Frontend/Mobile**: Leia do `NicheContext` (no Mobile) ou das configurações enviadas pelo backend (`labels` e `icons`).
* O banco de dados e as variáveis devem comportar abstrações (ex: usar `entity_name` ou o helper, e não engessar o sistema com colunas fixas chamadas `dog_name` se estivermos na tabela principal).

## 3. 🎨 Ícones Dinâmicos por Nicho

* Ícones **NÃO DEVEM** ser chumbados (hardcoded) nas sidebars ou botões que representam entidades do nicho.
* Ao invés de colocar `<Icon name="car" />` na listagem de "Veículos", busque o ícone do `config/niche.php` (no Laravel) ou do `NicheContext` (no Mobile), renderizando-o dinamicamente: `<Icon name={niche.icons.entity} />`.

## 4. 💡 Proatividade em Implementações e Melhorias

* Se o usuário solicitar uma funcionalidade e você (Agente de IA) prever que isso causará problemas (arquiteturais, segurança, vazamento de dados de Tenant) ou **tiver uma ideia que gere muito mais valor/benefício**, você **DEVE PARAR e AVISAR O USUÁRIO** antes de prosseguir cegamente.
* Exemplo: Se o usuário pedir para deletar um serviço, sugira implementar "Soft Deletes" para não perder o histórico financeiro. Explique o benefício de forma técnica e objetiva.

## 5. 📱💻 Paridade Web/Mobile e Qualidade da Interface (UX/UI)

* **Sincronia de Features**: Ao implementar qualquer funcionalidade nova ou alteração substancial solicitada pelo usuário, você **DEVE** garantir que ela seja replicada adequadamente nas duas frentes do sistema: se for feita na Web (Laravel/Livewire), deve ser analisada a viabilidade de atualização no Mobile (React Native/Expo) ou vice-versa.
* **Verificação Proativa de Layout**: O agente deve verificar/solicitar validação se os componentes de interface (botões, cards, etc.) não sofreram quebras estruturais (overflow, cor quebrada, posições indevidas).
* **Consistência Visual de Nicho**: Após alterar menus ou dashboards, certifique-se meticulosamente de que ícones e cores de nichos não estão se cruzando visualmente ou induzindo o usuário de um nicho ao erro (Ex: botões de UI com hardcode alusivos a carros exibidos para o dono de um restaurante).

---
**Agente**: Ao ler isso, aplique essas diretrizes imediatamente no seu contexto de raciocínio.
