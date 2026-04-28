<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\AsaasService;
use App\Models\User;

class TestAsaas extends Command {
    protected $signature = 'test:asaas';
    public function handle(AsaasService $asaas) {
        $user = User::first();
        try {
            $customer = $asaas->getOrCreateCustomer($user);
            $this->info("Customer: " . $customer);
            
            $result = $asaas->createSubscription($customer, 'PIX', 149.00, 'Test subs', null, $user);
            $this->info("Subs Result: " . json_encode($result));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
