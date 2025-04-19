<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        User::createRole('editor-journal');
        User::createRole('reader-journal');

        User::createPermission('edit_data_journal');
        User::createPermission('delete_data_journal');
        User::givePermissionToRole('edit_data_journal','editor-journal');
        User::givePermissionToRole('delete_data_journal','editor-journal');
        
        $users= User::all();
        foreach($users as $user){
            $user->giveRole('editor-journal');
            info($user->can('edit_data_journal'));
        }
    }
}
