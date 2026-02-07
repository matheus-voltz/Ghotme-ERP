<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

// Mock user login
$user = User::where('company_id', 1)->first();
Auth::login($user);

$data = [
    'type' => 'PF',
    'cpf' => '09460447910' // This CPF already exists in ID 2 and 4
];

$id = 2; // Testing update for account ID 2 (which HAS this CPF)

// Rule should ignore ID 2, but find ID 4
$rules = [
    'cpf' => [
        Rule::unique('clients', 'cpf')->ignore($id)->where('company_id', Auth::user()->company_id)
    ]
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "Validation FAILED (Correct behavior - found ID 4)\n";
    print_r($validator->errors()->all());
} else {
    echo "Validation PASSED (WRONG behavior - should have found ID 4)\n";
}

$id = 1; // Testing update for account ID 1 (which DOES NOT have this CPF)
$rules = [
    'cpf' => [
        Rule::unique('clients', 'cpf')->ignore($id)->where('company_id', Auth::user()->company_id)
    ]
];
$validator = Validator::make($data, $rules);
if ($validator->fails()) {
    echo "Validation FAILED for ID 1 (Correct behavior - found ID 2/4)\n";
} else {
    echo "Validation PASSED for ID 1 (WRONG behavior)\n";
}
