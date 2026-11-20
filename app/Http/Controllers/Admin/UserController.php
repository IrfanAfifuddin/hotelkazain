<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $receptionists = User::where('role', 'Receptionist')->latest()->get();

        return view('admin.users.index', compact('receptionists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:64', Rule::unique('users', 'username')],
            'user_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
        ]);

        User::create([
            'username' => $validated['username'],
            'user_name' => $validated['user_name'],
            'password' => Hash::make($validated['password']),
            'role' => 'Receptionist',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun resepsionis berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:64', Rule::unique('users', 'username', $user->user_id, 'user_id')],
            'user_name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'max:128'],
        ]);

        $data = [
            'username' => $validated['username'],
            'user_name' => $validated['user_name'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Akun resepsionis berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun resepsionis berhasil dihapus.');
    }
}
