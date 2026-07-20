<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MateriController extends Controller
{
    private function ensureOwner(Kelas $kelas): void
    {
        $dosenId = Auth::user()?->dosen?->id;

        abort_if(!$dosenId, 403, 'Akun dosen tidak valid.');
        abort_if((int) $kelas->dosen_id !== (int) $dosenId, 403, 'Tidak berhak mengakses kelas ini.');
    }

    private function ensureMateriInKelas(Kelas $kelas, Materi $materi): void
    {
        abort_if((int) $materi->kelas_id !== (int) $kelas->id, 404, 'Materi tidak ditemukan di kelas ini.');
    }

    public function index(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $openId = $request->query('open');

        $query = Materi::query()
            ->where('kelas_id', $kelas->id)
            ->latest();

        if (!empty($openId)) {
            $query->where('id', $openId);
        }

        $externalUrl = config('filesystems.disks.s3.url');
        $bucket = config('filesystems.disks.s3.bucket');

        $externalS3Client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => config('filesystems.disks.s3.region', 'us-east-1'),
            'endpoint' => $externalUrl,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $materis = $query->get()->map(function ($m) use ($externalS3Client, $bucket) {
            $downloadUrl = null;

            if ($m->file_path) {
                try {
                    $fileName = basename($m->file_path);
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $mimeTypes = [
                        'pdf'  => 'application/pdf',
                        'png'  => 'image/png',
                        'jpg'  => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif'  => 'image/gif',
                        'webp' => 'image/webp',
                        'mp4'  => 'video/mp4',
                        'webm' => 'video/webm',
                        'mp3'  => 'audio/mpeg',
                        'txt'  => 'text/plain',
                    ];

                    $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

                    $command = $externalS3Client->getCommand('GetObject', [
                        'Bucket'                     => $bucket,
                        'Key'                        => $m->file_path,
                        'ResponseContentDisposition' => 'inline',
                        'ResponseContentType'        => $contentType,
                    ]);

                    $presignedRequest = $externalS3Client->createPresignedRequest($command, '+60 minutes');
                    $downloadUrl = (string) $presignedRequest->getUri();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal generate URL presigned: ' . $e->getMessage());
                    $downloadUrl = null;
                }
            }

            return [
                'id' => $m->id,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi,
                'link_url' => $m->link_url,
                'file_path' => $m->file_path,
                'file_name' => $m->file_path ? basename($m->file_path) : null,
                'download_url' => $downloadUrl,
                'created_at' => optional($m->created_at)->toIso8601String(),
            ];
        });

        if (!empty($openId) && $materis->count() === 0) {
            abort(404, 'Materi tidak ditemukan di kelas ini.');
        }

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama_kelas ?? $kelas->nama ?? null,
            ],
            'materis' => $materis,
            'open' => !empty($openId) ? (int) $openId : null,
        ]);
    }

    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Create', [
            'kelas' => $kelas,
        ]);
    }

    /**
     * Endpoint 1: Menginisialisasi Multipart Upload & Generate Presigned URL untuk tiap Chunk
     */
    public function initiateMultipart(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $request->validate([
            'filename' => ['required', 'string'],
            'total_chunks' => ['required', 'integer', 'min:1'],
        ]);

        $bucket = config('filesystems.disks.s3.bucket');

        $cleanName = time() . '_' . str_replace(' ', '_', $request->input('filename'));
        $key = 'materi/' . $cleanName;

        try {
            $internalEndpoint = config('filesystems.disks.s3.endpoint');

            $internalS3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region', 'us-east-1'),
                'endpoint' => $internalEndpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => false,
                ]
            ]);

            // PERBAIKAN: Tebak tipe file berdasarkan ekstensi nama file secara aman tanpa memicu crash 500
            $extension = strtolower(pathinfo($request->input('filename'), PATHINFO_EXTENSION));

            $mimeTypes = [
                'pdf'  => 'application/pdf',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'mp4'  => 'video/mp4',
                'webm' => 'video/webm',
                'mp3'  => 'audio/mpeg',
                'txt'  => 'text/plain',
            ];

            $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

            $result = $internalS3Client->createMultipartUpload([
                'Bucket'      => $bucket,
                'Key'         => $key,
                'ContentType' => $contentType,
            ]);

            $uploadId = $result['UploadId'];
            $externalUrl = config('filesystems.disks.s3.url');

            $externalS3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region', 'us-east-1'),
                'endpoint' => $externalUrl,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $urls = [];

            for ($i = 1; $i <= $request->input('total_chunks'); $i++) {
                $command = $externalS3Client->getCommand('UploadPart', [
                    'Bucket'     => $bucket,
                    'Key'        => $key,
                    'UploadId'   => $uploadId,
                    'PartNumber' => $i,
                ]);

                $presignedRequest = $externalS3Client->createPresignedRequest($command, '+30 minutes');
                $urls[$i] = (string) $presignedRequest->getUri();
            }

            return response()->json([
                'upload_id' => $uploadId,
                'key'       => $key,
                'urls'      => $urls
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('S3 Chunk Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal inisiasi storage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint 2: Menyuruh MinIO menggabungkan potongan yang sudah sukses diupload frontend
     */
    public function completeMultipart(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $request->validate([
            'upload_id'          => ['required', 'string'],
            'key'                => ['required', 'string'],
            'parts'              => ['required', 'array'],
            'parts.*.PartNumber' => ['required', 'integer'],
            'parts.*.ETag'       => ['required', 'string'],
        ]);

        $disk = Storage::disk('s3');
        /** @var \Aws\S3\S3Client $s3Client */
        $s3Client = $disk->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        try {
            $s3Client->completeMultipartUpload([
                'Bucket'          => $bucket,
                'Key'             => $request->input('key'),
                'UploadId'        => $request->input('upload_id'),
                'MultipartUpload' => [
                    'Parts' => $request->input('parts'),
                ],
            ]);

            return response()->json([
                'status'    => true,
                'file_path' => $request->input('key'),
                'message'   => 'File berhasil digabungkan langsung di MinIO!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menggabungkan file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'file_path' => ['nullable', 'string'],
        ]);

        if (empty($data['file_path']) && empty($data['link_url'])) {
            return back()->withErrors([
                'file_path' => 'Isi minimal salah satu: upload file atau sertakan link.',
            ]);
        }

        Materi::create([
            'kelas_id' => $kelas->id,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'link_url' => $data['link_url'] ?? null,
        ]);

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Edit', [
            'kelas' => $kelas,
            'materi' => $materi,
        ]);
    }

    public function update(Request $request, Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'file_path' => ['nullable', 'string'],
            'remove_file' => ['nullable', 'boolean'],
            'remove_link' => ['nullable', 'boolean'],
        ]);

        $removeFile = (bool) ($data['remove_file'] ?? false);
        $removeLink = (bool) ($data['remove_link'] ?? false);

        $filePath = $materi->file_path;
        $linkUrl  = $materi->link_url;

        if ($removeLink) {
            $linkUrl = null;
        } elseif ($request->has('link_url')) {
            $linkUrl = $data['link_url'] ?? null;
        }

        if ($request->filled('file_path')) {
            if ($materi->file_path) {
                Storage::disk('s3')->delete($materi->file_path);
            }
            $filePath = $data['file_path'];
        } elseif ($removeFile) {
            if ($materi->file_path) {
                Storage::disk('s3')->delete($materi->file_path);
            }
            $filePath = null;
        }

        if (!$filePath && !$linkUrl) {
            return back()->withErrors([
                'file_path' => 'Isi minimal salah satu: upload file atau sertakan link.',
            ]);
        }

        $materi->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $filePath,
            'link_url' => $linkUrl,
        ]);

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        if ($materi->file_path) {
            Storage::disk('s3')->delete($materi->file_path);
        }

        $materi->delete();

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
