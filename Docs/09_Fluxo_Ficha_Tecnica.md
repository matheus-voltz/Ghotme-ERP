# 📖 Guia: Cadastro de Produtos e Ficha Técnica (Food Service)

Este documento detalha o processo de criação de insumos (ingredientes), montagem de produtos finais e a gestão automática de estoque (baixa por produção) no **Ghotme ERP**.

---

## 1. Cadastro de Insumos (Ingredientes)

Os insumos são os itens que compõem o seu produto final. Eles não são vendidos diretamente, mas são descontados do estoque quando um produto é vendido.

### Passo a Passo

1. Acesse o menu **Estoque** > **Insumos/Ingredientes**.
2. Clique em **Adicionar Item**.
3. Preencha os dados básicos:
   - **Nome**: Ex: Pão de Hambúrguer.
   - **Custo**: O valor pago pelo item.
   - **Quantidade**: Estoque atual.
   - **Unidade**: UN, KG, etc.
4. **Campo Importante**: Ative a flag `is_ingredient` (Insumo) para indicar que este item faz parte de fichas técnicas.
5. Salve o item.

---

## 2. Cadastro de Produto Final e Ficha Técnica

O produto final é o item que aparece no seu PDV (Menu). A Ficha Técnica vincula esse produto aos seus insumos.

### Passo a Passo

1. Acesse **Gestão de Cardápio** ou **Produtos**.
2. Clique em **Novo Produto**.
3. No formulário de cadastro, localize a aba ou seção **Ficha Técnica (Receita)**.
4. Selecione os insumos previamente cadastrados.
5. Defina a **quantidade** de cada insumo necessária para 1 unidade do produto final.
   - *Ex: Para 1 Hot Dog -> 1 Pão, 1 Salsicha.*
6. Salve o produto. O sistema agora sabe que a venda deste item "consome" os insumos listados.

---

## 3. Monitoramento Automático de Estoque

O Ghotme ERP automatiza a baixa de estoque em dois momentos (configurável):

- **Na Produção (Status: running)**: Assim que o pedido é enviado para a cozinha, o estoque de insumos é reservado/baixado.
- **Na Venda (Status: finalized)**: Se não houver baixa prévia, o sistema garante o desconto no encerramento.

### Benefícios

- **Custo Real**: O sistema calcula o custo do produto somando os insumos.
- **Alerta de Estoque Mínimo**: Se um ingrediente (ex: Pão) acabar, o sistema avisa antes de você tentar vender o lanche.
- **Relatórios**: Visão clara de quanto foi gasto de matéria-prima.

---

## 4. Exemplo Prático (Ghotme Dog)

| Item | Tipo | Ingredientes | Resultado |
| :--- | :--- | :--- | :--- |
| **Pão Brioche** | Insumo | - | Estoque -1 por venda |
| **Salsicha** | Insumo | - | Estoque -1 por venda |
| **Ghotme Dog** | Final | Pão (1) + Salsicha (1) | Venda gera baixa nos dois acima |

---
> [!TIP]
> Use a **IA Consultora** do Ghotme para analisar o desperdício de insumos com base na sua ficha técnica vs. vendas reais!
