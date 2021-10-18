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
            'email' => 'root',
            'name' => 'admin',
            'password' => hash('sha256', 'caduceu'),
            'location' => 'local',
            'admin' => true
        ]);

        DB::table('systems')->insert([
            'host' => 'stationx.hermes.radio',
            'allowfile' => 'users'
        ]);

        DB::table('messages')->insert([
            'name' => 'send test message ',
            'dest' => 'local',
            'orig' => 'local',
            'file' => null,
            'text' => 'lorem ipsum',
            'draft' => false 
        ]);

		db::table('messages')->insert([
			'name' => 'bienvenido ',
			'dest' => 'local',
			'orig' => 'local',
			'file' => null,
			'text' => 'Bienvenido ao sistema Hermes',
			'draft' => false ,
			'inbox' => true
		]);

        for($a=1; $a<=5; $a++){
            DB::table('messages')->insert([
                'name' => 'stuck test message '.Str::random(10),
                'dest' => 'local',
                'orig' => 'local',
                'file' => null,
                'text' => 'lorem ipsum',
                'draft' => true
            ]);
        }
        for($a=1; $a<=5; $a++){
            DB::table('messages')->insert([
                'name' => 'send message seeded'.Str::random(0),
                'dest' => 'local',
                'orig' => 'local',
                'file' => null,
                'text' => 'lorem ipsum',
                'draft' => false
            ]);
        }
    }
}
