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

        // Example: create role and permission
        $role = Role::create(['name' => 'writer']);
        $permission = Permission::create(['name' => 'edit articles']);

        // Assign permissions to roles and vice versa
        $role->givePermissionTo($permission);
        $permission->assignRole($role);

        $roles = $role->permissions; // roles collection (example)
        $permissions = $permission->roles; // permissions collection (example)

        $role->syncPermissions([$permission]);
        $permission->syncRoles([$role]);

        $role->revokePermissionTo($permission);
        $permission->removeRole($role);

        // get a list of all permissions directly assigned to the user
        $user = auth()->user();
        if ($user) {
            $permissionNames = $user->getPermissionNames();
            $permissions = $user->permissions;
            $permissions = $user->getDirectPermissions();
            $permissions = $user->getPermissionsViaRoles();
            $permissions = $user->getAllPermissions();
            $roles = $user->getRoleNames();
        }

        return view('content.authentications.auth-login-cover', ['pageConfigs' => $pageConfigs]);
    }
}
