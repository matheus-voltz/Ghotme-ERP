# 07 - Guia de Desenvolvimento (Development Guide)

Este guia define o padrão ouro para criar novas funcionalidades no ecossistema Ghotme ERP.

## 🛠️ Criando um Novo Módulo (Padrão Laravel)

Suponha que desejamos criar um módulo de **Inventário**:

### 1. Model & Migration

Execute o comando abaixo para gerar o modelo e a migração de banco:

```bash
php artisan make:model InventoryItem -m
```

Na **Migration**, defina campos padrão e o `company_id` para multi-tenancy:

```php
public function up() {
    Schema::create('inventory_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained(); // Essencial
        $table->string('name');
        $table->integer('stock')->default(0);
        $table->decimal('price', 15, 2);
        $table->timestamps();
        $table->softDeletes();
    });
}
```

No **Model**, adicione as propriedades e o Trait de Tenant:

```php
use App\Traits\BelongsToCompany; // Exemplo de Trait

protected $fillable = ['company_id', 'name', 'stock', 'price'];
```

---

### 2. O Controller (Standard Patterns)

Para listagens AJAX (DataTables) no painel admin:

1. **Index**: Retorna a View Blade principal.
2. **DataBase (Ajax)**: Retorna o JSON paginado e filtrado conforme os parâmetros do Datatables.

---

### 3. Rotas (`routes/web.php` e `routes/api.php`)

* **Web**: Para o acesso via navegador no painel administrativo.
* **API**: Registre a rota no `routes/api.php` dentro do middleware `auth:sanctum` para que o Mobile possa consumir o novo módulo.

---

## 📱 Desenvolvimento Mobile (Expo)

Ao expor o novo módulo para o app, siga esta ordem:

1. **API Endpoint**: Garanta que o backend retorna os dados filtrados.
2. **Service (Mobile)**: Crie o arquivo em `mobile/services/inventory.ts` usando o cliente Axios.
3. **New Screen**: Use o roteamento do **Expo Router** criando o arquivo em `mobile/app/(app)/inventory/index.tsx`.
4. **Haptic/Feedback**: Ao salvar dados no mobile, sempre utilize `Haptics` para melhorar a experiência do usuário.

---

## 🚦 Boas Práticas e Padrões de Código

### ✍️ Commits

Utilize Mensagens de Commit Semânticas:

* `feat`: Nova funcionalidade.
* `fix`: Correção de bug.
* `docs`: Mudanças na documentação (como esta aqui).
* `style`: Formatação de código.
* `refactor`: Melhorias no código sem alterar comportamento.

### 🧪 Testes

Sempre que possível, escreva testes de funcionalidade:

```bash
php artisan make:test InventoryTest
```

O Ghotme utiliza **PHPUnit** como runner padrão para testes de backend.

### 🏗️ Ambiente Docker (Laravel Sail)

Para rodar o ambiente completo no Mac:

```bash
./vendor/bin/sail up -d
```

Isso sobe o PHP 8.2+, MySQL 8, Redis e Meilisearch automaticamente.
