<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MateriController extends Controller
{
    private function ensureMember(Request $request, Kelas $kelas): void
    {
        $user = $request->user();

        abort_if(!$user || $user->peran !== 'mahasiswa', 403, 'Akun bukan mahasiswa.');
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $mahasiswaId = (int) $user->mahasiswa->id;

        $isMember = $kelas->anggotaKelases()
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();

        abort_if(!$isMember, 403, 'Kamu tidak terdaftar di kelas ini.');
    }

    public function index(Request $request, Kelas $kelas)
    {
        $this->ensureMember($request, $kelas);

        $openId = $request->query('open'); // ?open=123 (optional)

        $query = Materi::query()
            ->where('kelas_id', $kelas->id)
            ->latest();

        if (!empty($openId)) {
            $query->where('id', $openId);
        }

        $externalUrl = config('filesystems.disks.s3.url'); 
        $bucket = config('filesystems.disks.s3.bucket');

        // S3 Client menggunakan URL eksternal (Cloudflare Tunnel)
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
                    
                    // Mapping Mime-Type aman berdasarkan string nama file
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

                    // Generate link bypass rahasia berdurasi 60 menit
                    $presignedRequest = $externalS3Client->createPresignedRequest($command, '+60 minutes');
                    $downloadUrl = (string) $presignedRequest->getUri();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Mahasiswa gagal generate URL presigned: ' . $e->getMessage());
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

        return Inertia::render('Mahasiswa/Kelas/Tugas/Materi/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama_kelas' => $kelas->nama_kelas,
            ],
            'materis' => $materis,
            'open' => !empty($openId) ? (int) $openId : null,
        ]);
    }
}