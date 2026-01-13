<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run()
    {
        $clients = [
            ['name' => 'Maria Oliveira',   'cpf' => '329.458.621-03', 'email' => 'maria.oliveira@gmail.com',   'phone' => '(51) 99123-4567'],
            ['name' => 'Carlos Mendes',    'cpf' => '871.204.539-67', 'email' => 'carlos.mendes@hotmail.com',  'phone' => '(11) 98765-3210'],
            ['name' => 'Ana Paula Souza',  'cpf' => '054.817.293-41', 'email' => 'anapaula.souza@gmail.com',   'phone' => '(21) 97654-8901'],
            ['name' => 'Roberto Lima',     'cpf' => '632.975.018-85', 'email' => 'roberto.lima@outlook.com',   'phone' => '(41) 96543-7890'],
            ['name' => 'Fernanda Castro',  'cpf' => '748.361.205-29', 'email' => 'fernanda.castro@gmail.com',  'phone' => '(31) 95432-6789'],
            ['name' => 'Lucas Pereira',    'cpf' => '193.582.467-14', 'email' => 'lucas.pereira@yahoo.com.br', 'phone' => '(85) 94321-5678'],
            ['name' => 'Juliana Rocha',    'cpf' => '516.724.839-58', 'email' => 'juliana.rocha@gmail.com',    'phone' => '(71) 93210-4567'],
            ['name' => 'Paulo Ferreira',   'cpf' => '267.945.103-76', 'email' => 'paulo.ferreira@hotmail.com', 'phone' => '(62) 92109-3456'],
            ['name' => 'Camila Barbosa',   'cpf' => '834.617.250-32', 'email' => 'camila.barbosa@gmail.com',   'phone' => '(47) 91098-2345'],
            ['name' => 'Rafael Goncalves', 'cpf' => '491.083.762-90', 'email' => 'rafael.goncalves@gmail.com', 'phone' => '(48) 90987-1234'],
        ];

        foreach ($clients as $data) {
            Client::create($data);
        }
    }
}
