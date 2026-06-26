<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleCompanySeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('USERS')->insertGetId([
            'EMAIL'         => 'techcorp@example.com',
            'PASSWORD_HASH' => Hash::make('Password123'),
            'ROLE'          => 'company',
            'IS_ACTIVE'     => 1,
        ], 'USER_ID');

        DB::table('COMPANIES')->insert([
            'USER_ID'        => $userId,
            'COMPANY_NAME'   => 'TechCorp Solutions',
            'INDUSTRY'       => 'Software Development',
            'COMPANY_SIZE'   => '51-200',
            'LOCATION'       => 'Dhaka, Bangladesh',
            'WEBSITE'        => 'https://techcorp.example.com',
            'DESCRIPTION'    => 'A growing software company focused on web and mobile solutions.',
            'CONTACT_PERSON' => 'HR Manager',
            'CONTACT_EMAIL'  => 'hr@techcorp.example.com',
        ]);
    }
}