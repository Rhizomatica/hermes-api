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
            'orig' => ['local'],
            'text' => 'lorem ipsum',
            'draft' => false 
        ]);

		db::table('messages')->insert([
			'name' => 'bienvenido ',
			'dest' => 'local',
			'orig' => 'local',
			'text' => 'Bienvenido ao sistema Hermes',
			'draft' => false,
			'inbox' => true
		]);

        DB::table('messages')->insert([
            'name' => 'stuck test message '.Str::random(10),
            'dest' => 'local',
            'orig' => 'local',
            'text' => 'lorem ipsum',
            'draft' => true
        ]);
        for($a=1; $a<=2; $a++){
            DB::table('messages')->insert([
                'name' => 'send message seeded'.Str::random(0),
                'dest' => 'local',
                'orig' => 'local',
                'text' => 'lorem ipsum',
                'draft' => false
            ]);
        }

		DB::table('caller')->insert([
			'title' => 'default',
			'stations' => '["local"]',
			'starttime' => '00:00:00',
			'stoptime' => '24:00:00',
			'enable' => false
		]);


    }
}
