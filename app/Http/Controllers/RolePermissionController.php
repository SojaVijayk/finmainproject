<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        $role = Role::create(['name' => 'writer']);

        $role->givePermissionTo($permission);
        $permission->assignRole($role);

        $role->syncPermissions($permissions);
        $permission->syncRoles($roles);

        $role->revokePermissionTo($permission);
        $permission->removeRole($role);
        $permission = Permission::create(['name' => 'edit articles']);

        // get a list of all permissions directly assigned to the user
        $permissionNames = $user->getPermissionNames(); // collection of name strings
        $permissions = $user->permissions; // collection of permission objects

        // get all permissions for the user, either directly, or from roles, or from both
        $permissions = $user->getDirectPermissions();
        $permissions = $user->getPermissionsViaRoles();
        $permissions = $user->getAllPermissions();

        // get the names of the user's roles
        $roles = $user->getRoleNames(); // Returns a collection

        return view('content.authentications.auth-login-cover', ['pageConfigs' => $pageConfigs]);
    }
}
