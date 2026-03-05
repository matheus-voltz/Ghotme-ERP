# Guia de Auditoria de UI por Nicho

## Exemplos de "UI Ruim" (Poluída) vs "UI Boa" (Sanitizada)

### 🍔 Nicho: Food Service
- **❌ Ruim:** Botão "Cadastrar Veículo" nas ações rápidas.
- **✅ Bom:** Botão "Novo Pedido" ou "Abrir Mesa".
- **❌ Ruim:** Rótulo da tabela de ordens como "Ordem de Serviço (OS)".
- **✅ Bom:** Rótulo da tabela de ordens como "Pedidos".
- **❌ Ruim:** Campo "Placa" ou "IMEI" no formulário de venda rápida.
- **✅ Bom:** Campo "Mesa/Nome" ou "Senha do Pedido".

### 🚗 Nicho: Workshop (Oficina)
- **❌ Ruim:** Botão "Lançar Item do Cardápio".
- **✅ Bom:** Botão "Cadastrar Peça" ou "Vistoria Visual".
- **❌ Ruim:** Ícone de hambúrguer na lista de entidades.
- **✅ Bom:** Ícone de carro (`ti-car`).

### 📱 Nicho: Electronics (Assistência Técnica)
- **❌ Ruim:** Unidade de medida como "KM".
- **✅ Bom:** Unidade de medida como "Armazenamento (GB)".
- **❌ Ruim:** Campo "Placa" no cadastro do dispositivo.
- **✅ Bom:** Campo "Número de Série" ou "Modelo".

## Onde Encontrar as Condicionais?

- **Blade (Layouts/Menu):**
  `resources/views/layouts/sections/menu/verticalMenu.blade.php`
- **Menu JSON (Configuração):**
  `resources/menu/verticalMenu.json`
- **Helper Central:**
  `app/Helpers/NicheHelper.php`
- **Mobile Actions:**
  `mobile/app/(app)/(tabs)/actions.tsx`
- **Home/Dashboard:**
  `app/Http/Controllers/dashboard/Analytics.php` ou components Livewire equivalentes.
