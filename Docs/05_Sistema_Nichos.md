# 05 - Sistema de Nichos (Niche Management)

O **Ghotme ERP** é um camaleão. Ele utiliza um sistema de "Nichos" para adaptar toda a experiência do usuário sem precisar de múltiplos codebases.

## 🎯 O Conceito

Em vez de ter um "ERP de Oficinas" e um "ERP de Pet Shops" separados, o Ghotme centraliza a lógica e altera apenas a **Contextualização**.

### Exemplos de Transformação

| Elemento | Nicho: Automotive | Nicho: Pet Shop | Nicho: Food Service |
| :--- | :--- | :--- | :--- |
| **Entidade Principal** | Veículo | Pet | Mesa / Comanda |
| **Labels** | Placa, Modelo | Raça, Peso | Capacidade, Setor |
| **Ícone** | `tabler-car` | `tabler-dog` | `tabler-utensils` |
| **Serviço** | Troca de Óleo | Banho e Tosa | Preparo |

---

## 🛠️ Como Funciona (Backend)

### 1. NicheHelper (`app/Helpers/NicheHelper.php`)

Este helper fornece funções globais para tradução dinâmica:

* `niche_translate($string)`: Substitui termos genéricos por termos do nicho (ex: Troca "Veículo" por "Animal").
* `get_current_niche()`: Identifica o nicho ativo da empresa ou do usuário atual.
* `niche_icon($key)`: Retorna o ícone correto definido no arquivo de configuração.

### 2. Configuração (`config/niche.php`)

Centraliza as definições de cada nicho. Exemplo simplificado:

```php
'niches' => [
    'pet' => [
        'labels' => [
            'entity' => 'Pet',
            'entities' => 'Pets',
            'inventory_items' => 'Produtos',
        ],
        'icons' => [
            'entity' => 'tabler-dog',
        ]
    ]
]
```

---

## 📱 Como Funciona (Mobile)

No aplicativo, o `NicheContext` consome o nicho da API no momento do login e armazena em cache local.
Todas as telas utilizam a variável `labels` vinda deste contexto para exibir os textos corretos.

```tsx
const { labels } = useNiche();
return <Text>Cadastrar {labels.entity}</Text>; // Exibe "Cadastrar Veículo" ou "Cadastrar Pet"
```

---

## 🔧 Adicionando um Novo Nicho

Para adicionar um novo setor ao ERP:

1. Adicione as chaves de labels e ícones em `config/niche.php`.
2. Atualize o `NicheHelper` caso existam termos específicos de URL.
3. Garanta que o `NicheContext` no Mobile reconheça o novo slug vindos do backend.
