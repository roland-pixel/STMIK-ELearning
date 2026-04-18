<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>KHS - {{ $mahasiswa->nim }}</title>
    <style>
        @page {
            margin: 0.5in 0.7in;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background-color: #fff;
        }

        /* Kop Surat */
        .kop-surat {
            width: 100%;
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 5px;
        }

        .kop-surat h1 {
            font-size: 16pt;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }

        .kop-surat p {
            font-size: 9pt;
            margin: 2px 0;
            font-style: italic;
            text-align: center;
        }

        .title-doc {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* Informasi Mahasiswa */
        .info-section {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-section td {
            padding: 2px 0;
            font-size: 10pt;
            vertical-align: top;
        }

        /* Tabel Nilai */
        .table-khs {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-khs th {
            border: 1px solid #000;
            background-color: #eee;
            padding: 8px 4px;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .table-khs td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 9pt;
        }

        .text-left {
            text-align: left !important;
            padding-left: 8px !important;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Ringkasan & IPK */
        .ip-container {
            border: 2px solid #000;
            padding: 10px;
            text-align: center;
            width: 220px;
            margin-top: 10px;
            float: right;
        }

        /* Tanda Tangan */
        .ttd-section {
            width: 100%;
            margin-top: 40px;
            clear: both;
        }

        .ttd-table {
            width: 100%;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 10pt;
        }

        .space-signature {
            height: 60px;
        }

        .name-line {
            text-decoration: underline;
            font-weight: bold;
            display: block;
        }
    </style>
</head>

<body>
    <div class="kop-surat">
        <h1>{{ config('app.name', 'STMIK E-Learning') }}</h1>
        <p>Palangkaraya, Central Kalimantan</p>
        <p>Website: {{ config('app.url') }}</p>
    </div>

    <div class="title-doc">KARTU HASIL STUDI (KHS)</div>

    <table class="info-section">
        <tr>
            <td width="15%">NIM</td>
            <td width="2%">:</td>
            <td width="33%" class="font-bold">{{ $mahasiswa->nim }}</td>
            <td width="15%">Semester</td>
            <td width="2%">:</td>
            <td width="33%">{{ $semester->nama_semester }}</td>
        </tr>
        <tr>
            <td>Nama Mahasiswa</td>
            <td>:</td>
            <td class="font-bold">{{ strtoupper($mahasiswa->user->nama_lengkap) }}</td>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($semester->tanggal_mulai)->format('Y') }} /
                {{ \Carbon\Carbon::parse($semester->tanggal_selesai)->format('Y') }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</td>
            <td>Tgl Cetak</td>
            <td>:</td>
            <td>{{ $tanggal_cetak }}</td>
        </tr>
    </table>

    <table class="table-khs">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Kode</th>
                <th width="33%">Mata Kuliah</th>
                <th width="5%">SKS</th>
                <th width="7%">Tgs</th>
                <th width="7%">UTS</th>
                <th width="7%">UAS</th>
                <th width="8%">Angka</th>
                <th width="8%">Huruf</th>
                <th width="8%">Indeks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($khs_data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_mk }}</td>
                    <td class="text-left">{{ $item->nama_mk }}</td>
                    <td class="font-bold">{{ $item->sks }}</td>
                    {{-- Tampilkan angka hanya jika matkul Umum --}}
                    <td>{{ $item->jenis_mk === 'Spesial' ? '-' : number_format($item->total_tugas ?? 0, 0) }}</td>
                    <td>{{ $item->jenis_mk === 'Spesial' ? '-' : number_format($item->total_uts ?? 0, 0) }}</td>
                    <td>{{ $item->jenis_mk === 'Spesial' ? '-' : number_format($item->total_uas ?? 0, 0) }}</td>
                    <td class="font-bold">{{ number_format($item->nilai_akhir_angka ?? 0, 2) }}</td>
                    <td class="font-bold">{{ $item->nilai_huruf ?? '-' }}</td>
                    <td>{{ number_format($item->nilai_indeks ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding: 20px;">Data nilai belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9;">
                <td colspan="3" class="font-bold" style="text-align: right;">TOTAL SKS</td>
                <td class="font-bold">{{ $total_sks }}</td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 15px;">
        <div style="float: left; width: 50%;">
            <div style="font-size: 8pt; margin-top: 10px;">
                <strong>Keterangan:</strong><br>
                SKS : Satuan Kredit Semester<br>
                IPS : Indeks Prestasi Semester<br>
                Matakuliah dengan jenis 'Spesial' (Skripsi/PKL) tidak memiliki rincian Tgs/UTS/UAS.
            </div>
        </div>
        <div class="ip-container">
            <span style="font-size: 10pt; display: block; margin-bottom: 5px;">INDEKS PRESTASI SEMESTER (IPS)</span>
            <span style="font-size: 22pt; font-weight: bold;">{{ number_format($ipk, 2) }}</span>
        </div>
    </div>

    <div class="ttd-section">
        <table class="ttd-table">
            <tr>
                <td>
                    Mahasiswa,<br><br>
                    <div class="space-signature"></div>
                    <span class="name-line">{{ strtoupper($mahasiswa->user->nama_lengkap) }}</span>
                    NIM. {{ $mahasiswa->nim }}
                </td>
                <td>
                    Palangkaraya, {{ $tanggal_cetak }}<br>
                    Admin Akademik,<br><br>
                    <div class="space-signature"></div>
                    <span class="name-line">KABAG. AKADEMIK, M.Kom.</span>
                    NIP. 19680101 200812 1 001
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
