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
            'emailid' => '1',
            'admin' => true
        ]);

        DB::table('systems')->insert([
            'host' => 'stationx.hermes.radio',
            'allowfile' => 'admin',
            'allowhmp' => 'users'
        ]);

        $this->seedFrequencies();
    }

    private function seedFrequencies(){
        $command = "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
		$output = exec_cli($command);
		$sysalias = explode("\n", $output);

        foreach ($sysalias as $key => $value) {
           $this->insertNewFrequency($value);
        }
    }

    private function insertNewFrequency($alias){
        if(empty($alias))
            return;

        DB::table('frequency')->insert([
            'alias' => $alias,
            'nickname' => null,
            'frequency' => 0,
            'mode' => 'LSB',
            'enable' => false
        ]);
    }
}
