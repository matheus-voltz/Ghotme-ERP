🚀 Guia: Criando um Novo Módulo do Zero (Exemplo: Estoque)

php artisan make:model Inventory -m
```

No arquivo da migration (ex: database/migrations/xxxx_create_inventories_table.php), defina os campos:

```php
public function up() {
    Schema::create('inventories', function (Blueprint $table) {
        $table->id();
        $table->string('product_name');
        $table->integer('quantity');
        $table->decimal('price', 10, 2);
        $table->string('sku')->unique();
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}
```

No Model (app/Models/Inventory.php), adicione o $fillable:

```php
protected $fillable = ['product_name', 'quantity', 'price', 'sku', 'active'];
```

Passo 2: O Controller
Crie o controller:

```bash
php artisan make:controller InventoryController
```

Dentro do controller, implemente os métodos seguindo o padrão do seu projeto:

```php
public function index() {
    return view('content.pages.inventory.inventory-index');
}

public function dataBase(Request $request) {
    // 1. Defina as colunas para ordenação (deve bater com a ordem do HTML)
    $columns = [0 => 'id', 1 => 'id', 2 => 'product_name', 3 => 'quantity', 4 => 'price', 5 => 'sku', 6 => 'active', 7 => 'id'];

    $totalData = Inventory::count();
    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    // 2. Busca
    $query = Inventory::query();
    if ($search = $request->input('search.value')) {
        $query->where('product_name', 'LIKE', "%{$search}%")->orWhere('sku', 'LIKE', "%{$search}%");
    }

    $totalFiltered = $query->count();
    $items = $query->offset($start)->limit($limit)->orderBy($order, $dir)->get();

    // 3. Formatação para o JS
    $data = [];
    foreach ($items as $item) {
        $data[] = [
            'fake_id' => ++$start,
            'id' => $item->id,
            'product_name' => $item->product_name,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'sku' => $item->sku,
            'active' => $item->active,
            'action' => '' // Gerado pelo JS
        ];
    }

    return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data
    ]);
}
```

Passo 3: Rotas
Adicione as rotas em routes/web.php:

```php
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
Route::get('/inventory-list', [InventoryController::class, 'dataBase'])->name('inventory-list');
Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::delete('/inventory/destroy/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
```

Passo 4: View (HTML)
Crie a view principal (ex: resources/views/content/pages/inventory/inventory-index.blade.php):

```html
@extends('layouts/layoutMaster')

@section('title', 'Estoque')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
       'resources/assets/vendor/libs/select2/select2.scss',
       'resources/assets/vendor/libs/animate-css/animate.scss',
       'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js',
       'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
       'resources/assets/vendor/libs/select2/select2.js',
       'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
       'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/js/laravel-inventory.js'])
@endsection

@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Estoque</h5>
      <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddInventory" aria-controls="offcanvasAddInventory">
        Adicionar Item
      </button>
    </div>
  </div>
  <div class="card-datatable">
    <table class="datatables-inventory table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Produto</th>
          <th>SKU</th>
          <th>Quantidade</th>
          <th>Preço</th>
          <th>Ativo</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Offcanvas de Adicionar/Editar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddInventory" aria-labelledby="offcanvasAddInventoryLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddInventoryLabel" class="offcanvas-title">Adicionar Item</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="add-new-inventory pt-0" id="addNewInventoryForm">
      @csrf
      <input type="hidden" name="id" id="inventory_id">
      
      <div class="mb-6 form-control-validation">
        <label class="form-label" for="add-inventory-product">Nome do Produto</label>
        <input type="text" class="form-control" id="add-inventory-product" name="product_name" placeholder="Ex: Óleo Lubrificante" />
      </div>
      
      <div class="mb-6 form-control-validation">
        <label class="form-label" for="add-inventory-sku">SKU</label>
        <input type="text" class="form-control" id="add-inventory-sku" name="sku" placeholder="SKU-123" />
      </div>
      
      <div class="mb-6 form-control-validation">
        <label class="form-label" for="add-inventory-quantity">Quantidade</label>
        <input type="number" class="form-control" id="add-inventory-quantity" name="quantity" placeholder="0" />
      </div>
      
      <div class="mb-6 form-control-validation">
        <label class="form-label" for="add-inventory-price">Preço Unitário</label>
        <input type="text" class="form-control" id="add-inventory-price" name="price" placeholder="0,00" />
      </div>
      
      <div class="mb-6">
        <label class="form-label" for="inventory-status">Ativo</label>
        <select id="inventory-status" class="form-select" name="active">
          <option value="1">Sim</option>
          <option value="0">Não</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-primary me-3 data-submit">Salvar</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
    </form>
  </div>
</div>

@endsection
```

O JavaScript (resources/js/inventory-management.js)
Este é o cérebro da página. Use o arquivo 
laravel-vehicles.js
 como base e faça as seguintes trocas:

Seletor da Tabela: const dt_inventory_table = document.querySelector('.datatables-inventory').
URLs de AJAX: Mude para baseUrl + 'inventory-list'.
Colunas (columns): Ajuste para bater com o JSON do Controller.
Renderizadores (columnDefs):
targets: 2: Renderize o nome do produto.
targets: 6: Renderize o badge de "Ativo/Inativo".
targets: 7: Renderize o botão de Editar (edit-record) e Excluir (delete-record).
Mapeamento da Edição: No fetch(.../edit), mapeie os dados do JSON para os IDs dos inputs do seu novo formulário.
Validação: Ajuste o FormValidation.formValidation com os nomes dos seus novos campos.
