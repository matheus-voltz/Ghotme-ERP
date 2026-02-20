<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Company;
use App\Services\NicheInitializerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'company_name' => ['required', 'string', 'max:255'],
            'niche' => ['required', 'string', 'in:' . implode(',', array_keys(config('niche.niches')))],
            'cnpj' => ['required', 'string', 'regex:/^\d{14}$|^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/', 'unique:companies,document_number'], // Valida formato e unicidade
            'contact_number' => ['required', 'string', 'max:20'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Remove pontuação do CNPJ
            $cnpj = preg_replace('/[^0-9]/', '', $input['cnpj']);

            // 1. Cria a Empresa com o Nicho
            $company = Company::create([
                'name' => $input['company_name'],
                'document_number' => $cnpj,
                'niche' => $input['niche'],
            ]);

            // 2. Inicializa os padrões do nicho (serviços, checklists, etc)
            app(NicheInitializerService::class)->initialize($company->id, $input['niche']);

            // 3. Cria o Usuário vinculado a essa empresa
            return User::create([
                'company_id' => $company->id, // Assumindo que você tem essa coluna na migration de users ou vai adicionar
                'name' => $input['name'],
                'email' => $input['email'],
                'contact_number' => $input['contact_number'],
                'password' => Hash::make($input['password']),
                'role' => 'admin', // Define como administrador da empresa
                'permission' => 'all', // Permissão total
                'trial_ends_at' => now()->addDays(30),
            ]);
        });
    }
}
