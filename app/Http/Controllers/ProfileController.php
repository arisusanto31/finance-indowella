<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);
        $permission = Permission::create(['name' => $request->input('name')]);
        return [
            'status' => 1,
            'msg' => $permission
        ];
    }

    function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);
        $role = \Spatie\Permission\Models\Role::create(['name' => $request->input('name')]);
        return [
            'status' => 1,
            'msg' => $role
        ];
    }

    function createUser(Request $request)
    {
        try {
            $request = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string'
            ]);
            $request['password'] = Hash::make('123456');
            $user = User::create($request);
            return [
                'status' => 1,
                'msg' => $user
            ];
        } catch (ValidationException $v) {
            return [
                'status' => 0,
                'msg' => getErrorValidation($v)
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }

    function getGivePermissionRole()
    {
        $role = getInput('role');
        $permission = getInput('permission');
        $role = \Spatie\Permission\Models\Role::findByName($role);
        if ($role) {
            $role->givePermissionTo($permission);
            return [
                'status' => 1,
                'msg' => 'permission added'
            ];
        } else {
            return [
                'status' => 0,
                'msg' => 'role not found'
            ];
        }
    }

    function addPermissionRole(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|integer',
            'role_id' => 'required|integer'
        ]);
        $role = \Spatie\Permission\Models\Role::find($request->input('role_id'));
        if ($role) {
            $permission = Permission::find($request->input('permission_id'));
            if (!$permission) {
                return [
                    'status' => 0,
                    'msg' => 'permission not found'
                ];
            }
            $role->givePermissionTo($permission->name);
            return [
                'status' => 1,
                'msg' => 'permission added'
            ];
        } else {
            return [
                'status' => 0,
                'msg' => 'role not found'
            ];
        }
    }

    function addRoleUser(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);
        $user_id = $request->input('user_id');
        $role = \Spatie\Permission\Models\Role::find($request->input('role_id'));
        if (!$role) {
            return [
                'status' => 0,
                'msg' => 'role not found'
            ];
        }
        $user = User::find($user_id);
        if ($user) {
            $user->assignRole($role->name);
            return [
                'status' => 1,
                'msg' => 'role added'
            ];
        } else {
            return [
                'status' => 0,
                'msg' => 'user not found'
            ];
        }
    }

    function getRole()
    {
        $role = \Spatie\Permission\Models\Role::with('permissions')->get();
        return [
            'status' => 1,
            'msg' => $role
        ];
    }

    function getPermission()
    {
        $permission = Permission::all();
        return [
            'status' => 1,
            'msg' => $permission
        ];
    }

    function getUser()
    {
        $user = User::all()->map(function ($val) {
            $val['role'] = $val->getAllRoles();
            return $val;
        });
        return [
            'status' => 1,
            'msg' => $user
        ];
    }

    function getItemPermission()
    {
        $searchs = explode(' ', getInput('search'));
        $permission = Permission::query();
        foreach ($searchs as $search) {
            $permission = $permission->where('name', 'like', '%' . $search . '%');
        }
        $permission = $permission->select('id', DB::raw('name as text'))->get();
        return [
            'results' => $permission
        ];
    }

    function getItemRole()
    {
        $searchs = explode(' ', getInput('search'));
        $role = \Spatie\Permission\Models\Role::query();
        foreach ($searchs as $search) {
            $role = $role->where('name', 'like', '%' . $search . '%');
        }
        $role = $role->select('id', DB::raw('name as text'))->get();
        return [
            'results' => $role
        ];
    }
}
