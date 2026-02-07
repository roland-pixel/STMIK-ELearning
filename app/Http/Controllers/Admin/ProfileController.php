<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // max 2MB
        ]);

        // update basic fields
        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->email = $validated['email'];

        // update password jika diisi
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // upload avatar jika ada
        if ($request->hasFile('avatar')) {
            // hapus avatar lama (kalau ada)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // simpan avatar baru
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path; // contoh: avatars/namafile.webp
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
