<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display list of all roles
     */
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show form to create new role
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-zA-Z0-9_-]+$/',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'name.regex' => 'Nama role hanya boleh mengandung huruf, angka, underscore, dan dash',
            'display_name.required' => 'Nama tampilan harus diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "' . $role->display_name . '" berhasil dibuat.');
    }

    /**
     * Show form to edit role
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing system roles
        if (in_array($role->name, ['Admin', 'Operator-Vidcon'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit.');
        }

        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing system roles
        if (in_array($role->name, ['Admin', 'Operator-Vidcon'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name,' . $id . '|regex:/^[a-zA-Z0-9_-]+$/',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'name.regex' => 'Nama role hanya boleh mengandung huruf, angka, underscore, dan dash',
            'display_name.required' => 'Nama tampilan harus diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "' . $role->display_name . '" berhasil diupdate.');
    }

    /**
     * Delete role
     */
    public function destroy($id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        // Prevent deleting system roles
        if (in_array($role->name, ['Admin', 'User', 'Operator-Vidcon'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat dihapus.');
        }

        // Check if role has users
        if ($role->users_count > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role "' . $role->display_name . '" tidak dapat dihapus karena masih digunakan oleh ' . $role->users_count . ' user.');
        }

        $displayName = $role->display_name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "' . $displayName . '" berhasil dihapus.');
    }
}
