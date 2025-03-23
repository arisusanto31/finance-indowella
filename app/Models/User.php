<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mainRole(){
        return $this->getRoleNames()[0];
    }


    public static function createPermission($name){
        Permission::create(['name'=>$name]);
    }

    public static function createRole($name){
        Role::create(['name'=>$name]);
        
    }

    public static function givePermissionToRole($permission,$rolename){
        $role= Role::findByName($rolename);
        $role->givePermissionTo($permission);
    }
    public function giveRole($name){
        $this->assignRole($name);
    }


//     @if(auth()->user()->hasRole('admin'))
//     <p>Anda adalah Admin</p>
// @endif

// @if(auth()->user()->can('edit articles'))
//     <p>Anda bisa mengedit artikel</p>
// @endif
    
   

// Role::create(['name' => 'admin']);
// Role::create(['name' => 'user']);

// Permission::create(['name' => 'edit articles']);
// Permission::create(['name' => 'delete articles']);

// $admin = Role::findByName('admin');
// $admin->givePermissionTo('edit articles');
}
