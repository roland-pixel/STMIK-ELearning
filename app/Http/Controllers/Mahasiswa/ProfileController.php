<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        return Inertia::render('Mahasiswa/Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'peran' => $user->peran,
            ],
            'mahasiswa' => [
                'id' => $user->mahasiswa->id,
                'uuid' => $user->mahasiswa->uuid,
                'nim' => $user->mahasiswa->nim,
                'angkatan' => $user->mahasiswa->angkatan,
                'status' => $user->mahasiswa->status,
                'jenis_program' => $user->mahasiswa->jenis_program,
                'status_masuk' => $user->mahasiswa->status_masuk,
                'jurusan' => $user->mahasiswa->jurusan?->nama_jurusan,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        // 🔥 nama_lengkap dihapus dari validasi
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
        ]);

        // upload avatar baru
        if ($request->hasFile('avatar')) {

            // hapus avatar lama
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request
                ->file('avatar')
                ->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        // 🔥 hanya email + avatar yang diupdate
        $user->update($validated);

        return back()->with(
            'success',
            'Profil berhasil diperbarui.'
        );
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar berhasil dihapus.');
    }
}
