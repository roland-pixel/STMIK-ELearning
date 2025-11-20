<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = Upload::with('user')
            ->latest()
            ->get();

        return Inertia::render('Admin/Uploads/Index', [
            'uploads' => $uploads
        ]);
    }

    /**
     * Menyimpan file upload baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // max 20MB
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public'); // disimpan di storage/app/public/uploads

        $upload = Upload::create([
            'user_id'    => auth()->id(),
            'nama_file'  => $file->getClientOriginalName(),
            'path'       => $path,
            'tipe'       => explode('/', $file->getMimeType())[0], // contoh: image/video/application
            'ekstensi'   => $file->getClientOriginalExtension(),
            'ukuran'     => $file->getSize(),
            'mime_type'  => $file->getMimeType(),
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'File berhasil diupload!');
    }

    /**
     * Menghapus file upload.
     */
    public function destroy(Upload $upload)
    {
        // Pastikan hanya user pemilik file yang bisa menghapus
        if ($upload->user_id !== auth()->id()) {
            abort(403, 'Tidak diizinkan.');
        }

        // Hapus file fisik
        Storage::disk('public')->delete($upload->path);

        // Hapus record database
        $upload->delete();

        return redirect()->back()->with('success', 'File berhasil dihapus!');
    }
}
