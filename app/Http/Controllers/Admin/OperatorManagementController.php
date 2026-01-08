<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class OperatorManagementController extends Controller
{
    public function index()
    {
        // Get all users with Operator-Vidcon role
        $operators = User::whereHas('roles', function($query) {
            $query->where('name', 'Operator-Vidcon');
        })->with('roles')->orderBy('name')->paginate(20);

        return view('admin.operators.index', compact('operators'));
    }

    public function create()
    {
        // Get all users who don't have Operator-Vidcon role yet
        $users = User::whereDoesntHave('roles', function($query) {
            $query->where('name', 'Operator-Vidcon');
        })->orderBy('name')->get();

        return view('admin.operators.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $operatorRole = Role::where('name', 'Operator-Vidcon')->first();

        if ($operatorRole && !$user->roles->contains($operatorRole->id)) {
            $user->roles()->attach($operatorRole->id);
        }

        return redirect()->route('admin.operators.index')
            ->with('success', 'Operator berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        $operatorRole = Role::where('name', 'Operator-Vidcon')->first();

        if ($operatorRole) {
            $user->roles()->detach($operatorRole->id);
        }

        return redirect()->route('admin.operators.index')
            ->with('success', 'Operator berhasil dihapus.');
    }
}
