<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $receptionists = User::where('role', 'Receptionist')->latest()->get();
        return view('admin.users.index', compact('receptionists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'user_name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'username' => $request->username,
            'user_name' => $request->user_name,
            'password' => Hash::make($request->password),
            'role' => 'Receptionist',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun resepsionis berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username,' . $user->user_id . ',user_id',
            'user_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'username' => $request->username,
            'user_name' => $request->user_name,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
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
