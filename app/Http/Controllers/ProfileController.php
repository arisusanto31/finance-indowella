<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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

    function givePermissionToRole(Request $request)
    {
        $request->validate([
            'permission' => 'required|string',
            'role' => 'required|string'
        ]);
        $role = \Spatie\Permission\Models\Role::findByName($request->input('role'));
        if ($role) {
            $role->givePermissionTo($request->input('permission'));
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
            'role' => 'required|string',
            'user_id' => 'required|integer'
        ]);
        $user_id = $request->input('user_id');
        $role = $request->input('role');
        $user = User::find($user_id);
        if ($user) {
            $user->assignRole($role);
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
}
