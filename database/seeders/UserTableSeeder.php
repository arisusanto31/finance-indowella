<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::createRole('admin');
        User::createPermission('read_hpp');
        User::givePermissionToRole('read_hpp','admin');
        DB::table('users')->insert([
            [
                'name' => 'iqom',
                'email' => 'iqom@indowella.com',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'ari',
                'email' => 'ari@indowella.com',
                'password' => Hash::make('123456'),
            ],
        ]);

        $users= User::all();
        foreach($users as $user){
            $user->giveRole('admin');
            info($user->can('read_hpp'));

        }
    }
}
