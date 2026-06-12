<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('USERS')->insert([
            'EMAIL'         => 'admin@careerbridge.com',
            'PASSWORD_HASH' => Hash::make('Admin@12345'),
            'ROLE'          => 'admin',
            'IS_ACTIVE'     => 1,
        ]);
    }
}