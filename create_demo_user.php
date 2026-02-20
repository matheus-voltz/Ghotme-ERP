<?php
require __DIR__.'/vendor/autoload.php';
\$app = require_once __DIR__.'/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Services\NicheInitializerService;
use Illuminate\Support\Facades\Hash;

\$email = 'teste.rdo@exemplo.com';
User::where('email', \$email)->forceDelete();

\$company = Company::create([
    'name' => 'Construtora Exemplo RDO',
    'niche' => 'construction',
    'document_number' => '12345678000199',
]);

app(NicheInitializerService::class)->initialize(\$company->id, 'construction');

\$user = User::create([
    'company_id' => \$company->id,
    'name' => 'Matheus Engenheiro',
    'email' => \$email,
    'password' => Hash::make('senha123'),
    'role' => 'admin',
    'plan' => 'free',
    'trial_ends_at' => now()->addDays(3),
    'email_verified_at' => now(),
]);

echo "Usuário de teste criado com sucesso!
";
