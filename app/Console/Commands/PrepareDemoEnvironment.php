<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;
use App\Models\Clients;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PrepareDemoEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:prepare {--force : Executar sem pedir confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepara um ambiente de demonstração limpando dados (protegendo is_master = true) e inserindo dados fictícios realistas.';

    /**
     * Niches to generate
     */
    private $niches = [
        'oficina_mecanica' => ['name' => 'Oficina Mecânica', 'domain' => 'oficina', 'services' => ['Troca de Óleo', 'Revisão Geral', 'Alinhamento e Balanceamento', 'Troca de Pastilhas']],
        'clinica' => ['name' => 'Clínica Médica', 'domain' => 'clinica', 'services' => ['Consulta Geral', 'Exame de Sangue', 'Raio-X', 'Ultrassom']],
        'varejo' => ['name' => 'Loja de Roupas', 'domain' => 'roupas', 'services' => ['Ajuste de Bainha', 'Customização']],
        'food_service' => ['name' => 'Restaurante', 'domain' => 'restaurante', 'services' => ['Taxa de Entrega', 'Reserva de Mesa']],
        'autopeças' => ['name' => 'Loja de Autopeças', 'domain' => 'autopecas', 'services' => ['Instalação Básica']],
        'servicos' => ['name' => 'Serviços Técnicos', 'domain' => 'servicos', 'services' => ['Manutenção Condicionador de Ar', 'Instalação de Rede', 'Formatação de PC']],
        'mercado' => ['name' => 'Mercado', 'domain' => 'mercado', 'services' => ['Entrega a Domicílio']]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('app.debug') && !$this->option('force')) {
            $this->warn('Você está prestes a apagar dados em um ambiente onde o debug está desativado (potencialmente PRODUÇÃO).');
            if (!$this->confirm('TEM CERTEZA ABSOLUTA QUE DESEJA CONTINUAR? ISSO APAGARÁ TODAS AS EMPRESAS E USUÁRIOS EXCETO OS MASTERS (is_master = true).')) {
                $this->info('Operação cancelada.');
                return;
            }
        }

        $this->info('Iniciando preparação do ambiente de demonstração...');
        $faker = \Faker\Factory::create('pt_BR');

        // ==== PASSO 1: LIMPEZA ====
        if ($this->cleanDatabase() === false) {
            return; // Aborta se a limpeza falhar na segurança do master
        }

        // ==== PASSO 2 ao 5: CRIAÇÃO POR NICHO ====
        foreach ($this->niches as $nicheSlug => $nicheData) {
            $this->info("Gerando dados para o nicho: {$nicheData['name']}...");
            $company = $this->createCompany($faker, $nicheSlug, $nicheData);
            $this->createUsers($faker, $company, $nicheData['domain']);

            $clients = $this->createClients($faker, $company);
            $suppliers = $this->createSuppliers($faker, $company);
            $products = $this->createProducts($faker, $company, $nicheSlug);

            // Simulações Financeiras e Operacionais
            $this->simulateOperations($faker, $company, $clients, $products, $nicheData);
        }

        $this->info('Ambiente de demonstração preparado com sucesso!');
        $this->info('Relatório: 7 empresas criadas. Verifique os acessos.');
    }

    private function cleanDatabase()
    {
        $this->warn('Limpando banco de dados (protegendo usuários is_master = true)...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Protege os usuários master (is_master = true)
        $protectedUserIds = User::where('is_master', true)->pluck('id')->toArray();

        // Verifica se encontrou algum master. Se não, abortar por segurança.
        if (empty($protectedUserIds)) {
            $this->error('Nenhum usuário Master encontrado (is_master = true). Abortando limpeza para evitar exclusão total!');
            return false;
        }

        // Protege a empresa vinculada aos usuários protegidos
        $protectedCompanyIds = User::whereIn('id', $protectedUserIds)->pluck('company_id')->filter()->toArray();

        // Apagar Operações Dependentes
        FinancialTransaction::whereNotIn('company_id', $protectedCompanyIds)->delete();
        OrdemServico::whereNotIn('company_id', $protectedCompanyIds)->delete();
        InventoryItem::whereNotIn('company_id', $protectedCompanyIds)->delete();
        Supplier::whereNotIn('company_id', $protectedCompanyIds)->delete();
        Clients::whereNotIn('company_id', $protectedCompanyIds)->delete();

        // Apagar Usuários e Empresas não protegidas
        User::whereNotIn('id', $protectedUserIds)->delete();
        Company::whereNotIn('id', $protectedCompanyIds)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info('Limpeza concluída.');
        return true;
    }

    private function createCompany($faker, $nicheSlug, $nicheData)
    {
        return Company::create([
            'name' => $faker->company . " " . $nicheData['name'],
            'niche' => $nicheSlug,
            'document_number' => $faker->cnpj(false),
            'email' => "contato@" . strtolower(str_replace(' ', '', $faker->word)) . ".com.br",
            'phone' => $faker->cellphoneNumber,
            'address' => $faker->streetName,
            'city' => $faker->city,
            'state' => $faker->stateAbbr,
            'zip_code' => $faker->postcode,
            'is_active' => true,
        ]);
    }

    private function createUsers($faker, $company, $domain)
    {
        $roles = [
            'admin' => 'Administrador',
            'financial' => 'Financeiro',
            'seller' => 'Vendedor',
            'attendant' => 'Atendimento',
            'stock' => 'Estoque'
        ];

        foreach ($roles as $role => $title) {
            User::create([
                'name' => $title . " " . $faker->firstName,
                'email' => "{$role}.{$company->id}@{$domain}.demo.com",
                'password' => Hash::make('12345678'), // Senha padrão para demo
                'company_id' => $company->id,
                'role' => $role === 'admin' ? 'admin' : 'employee', // Supondo que a role seja simplificada no seu banco
                'is_active' => true,
                'email_verified_at' => now(),
                'plan' => $role === 'admin' ? 'enterprise' : 'free'
            ]);
        }
    }

    private function createClients($faker, $company)
    {
        $clients = [];
        for ($i = 0; $i < 15; $i++) {
            $isPJ = $faker->boolean(20);
            $clients[] = Clients::create([
                'company_id' => $company->id,
                'type' => $isPJ ? 'PJ' : 'PF',
                'name' => $isPJ ? $faker->company : $faker->name,
                'cpf' => $isPJ ? null : $faker->cpf(false),
                'cnpj' => $isPJ ? $faker->cnpj(false) : null,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->cellphoneNumber,
                'whatsapp' => $faker->cellphoneNumber,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
            ]);
        }
        return $clients;
    }

    private function createSuppliers($faker, $company)
    {
        $suppliers = []; // Limita o loop para n suppliers por empresa. Como você pediu 5 vamos ajustar se falhar.
        for ($i = 0; $i < 5; $i++) {
            $suppliers[] = Supplier::create([
                'company_id' => $company->id,
                'name' => $faker->company . " Distribuidora",
                'document' => $faker->cnpj(false),
                'contact_name' => $faker->name,
                'email' => $faker->unique()->companyEmail,
                'phone' => substr($faker->phoneNumber, 0, 20), // Trunca em caso de número mto longo
                'is_active' => true,
            ]);
        }
        return $suppliers;
    }

    private function createProducts($faker, $company, $nicheSlug)
    {
        $products = [];
        for ($i = 0; $i < 20; $i++) {
            $cost = $faker->randomFloat(2, 10, 500);
            $products[] = InventoryItem::create([
                'company_id' => $company->id,
                'name' => ucfirst($faker->word) . " (" . ucfirst($nicheSlug) . ")",
                'sku' => strtoupper($faker->bothify('???-####')),
                'cost_price' => $cost,
                'selling_price' => $cost * $faker->randomFloat(2, 1.3, 2.5),
                'quantity' => $faker->numberBetween(5, 100),
                'min_quantity' => 10,
                'is_active' => true,
                'is_for_sale' => true,
            ]);
        }
        return $products;
    }

    private function simulateOperations($faker, $company, $clients, $products, $nicheData)
    {
        $admin = User::where('company_id', $company->id)->first(); // Fallback user

        // Financeiro: Contas a Pagar
        for ($i = 0; $i < 10; $i++) {
            FinancialTransaction::create([
                'company_id' => $company->id,
                'description' => "Pagamento Fornecedor " . $faker->company,
                'amount' => $faker->randomFloat(2, 100, 2000),
                'type' => 'out',
                'status' => $faker->randomElement(['pending', 'paid']),
                'due_date' => Carbon::now()->addDays($faker->numberBetween(-10, 30)),
                'paid_at' => $faker->boolean(70) ? Carbon::now()->subDays($faker->numberBetween(1, 10)) : null,
            ]);
        }

        // Simular OS ou Vendas Rápidas
        for ($i = 0; $i < 15; $i++) {
            $client = collect($clients)->random(); // Pega um cliente aleatório da coleção

            $os = OrdemServico::create([
                'company_id' => $company->id,
                'client_id' => $client->id,
                'user_id' => $admin->id,
                'status' => $faker->randomElement(['aberto', 'em_andamento', 'concluido']),
                'description' => "Serviço fictício: " . collect($nicheData['services'])->random(),
                'created_at' => Carbon::now()->subDays($faker->numberBetween(1, 30)),
            ]);

            // Se concluída, gerar receita no financeiro
            if ($os->status === 'concluido') {
                FinancialTransaction::create([
                    'company_id' => $company->id,
                    'description' => "Recebimento OS #" . $os->id,
                    'amount' => $faker->randomFloat(2, 50, 1500),
                    'type' => 'in',
                    'status' => 'paid',
                    'client_id' => $client->id,
                    'due_date' => $os->created_at,
                    'paid_at' => $os->created_at,
                    'related_type' => OrdemServico::class,
                    'related_id' => $os->id,
                ]);
            }
        }
    }
}
