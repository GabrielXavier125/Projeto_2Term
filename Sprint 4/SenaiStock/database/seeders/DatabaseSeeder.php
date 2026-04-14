<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name'     => 'Almoxarife',
            'email'    => 'almoxarife@senai.br',
            'password' => Hash::make('senai123'),
            'role'     => 'almoxarife',
        ]);

        User::create([
            'name'     => 'Coordenador 1',
            'email'    => 'coordenador1@senai.br',
            'password' => Hash::make('senai123'),
            'role'     => 'coordenador',
        ]);

        User::create([
            'name'     => 'Coordenador 2',
            'email'    => 'coordenador2@senai.br',
            'password' => Hash::make('senai123'),
            'role'     => 'coordenador',
        ]);

    }
}
