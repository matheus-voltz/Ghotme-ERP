<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DefaultStatusSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'grafit933@gmail.com'],
            [
                'name' => 'matheus',
                'password' => bcrypt('Kvothe1995@.'),
            ]
        );
        $this->call(ClientsSeeder::class);
        $this->call(ClientFieldsSeeder::class);

        $this->call(DefaultStatusSeeder::class);

        $this->call([
            VeiculosSeeder::class,
        ]);
    }
}
