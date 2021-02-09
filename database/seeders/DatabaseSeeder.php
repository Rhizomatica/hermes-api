<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'email' => 'postmaster',
            'name' => 'admin',
            'password' => hash('sha256', 'secret'),
            'location' => 'local',
            'admin' => true
        ]);

        for($a=1; $a<=5; $a++){
            DB::table('messages')->insert([
                'name' => 'test message '.Str::random(10),
                'dest' => 'local',
                'orig' => 'local',
                'file' => null,
                'text' => 'lorem ipsum',
                'draft' => true
            ]);
        }

        /*
                $this->call('UserSeeder');

        Quote::create([
            'text' => 'Success is going from failure to failure without losing your enthusiasm',
            'author' => 'Winston Churchill',
            'background' => '1.jpg'
        ]);

        Quote::create([
            'text' => 'Dream big and dare to fail',
            'author' => 'Norman Vaughan',
            'background' => '2.jpg'
        ]);

        Quote::create([
            'text' => 'It does not matter how slowly you go as long as you do not stop',
            'author' => 'Confucius',
            'background' => '3.jpg'
        ]);
        */
    }
}
