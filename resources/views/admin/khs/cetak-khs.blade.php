<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>KHS - {{ $mahasiswa->nim }} - {{ $semester->nama_semester }}</title>
    <style>
        @page {
            margin: 0.5in 0.5in;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
        }

        /* Kop Surat */
        .kop-surat {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 2px;
            padding-bottom: 5px;
        }

        .kop-surat-border {
            border-bottom: 1px solid #000;
            margin-bottom: 20px;
        }

        .kop-surat h1 {
            font-size: 16pt;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop-surat p {
            font-size: 8pt;
            margin: 1px 0;
        }

        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Informasi Mahasiswa */
        .info-section {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .info-section td {
            padding: 2px 0;
            font-size: 9pt;
            vertical-align: top;
        }

        /* Tabel Nilai */
        .table-khs {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-khs th {
            border: 0.5pt solid #000;
            background-color: #f2f2f2;
            padding: 8px 4px;
            font-size: 8.5pt;
            text-transform: uppercase;
        }

        .table-khs td {
            border: 0.5pt solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 8.5pt;
        }

        .text-left {
            text-align: left !important;
            padding-left: 8px !important;
        }

        .font-bold {
            font-weight: bold;
        }

        .italic {
            font-style: italic;
        }

        /* Ringkasan & IPK */
        .summary-box {
            margin-top: 20px;
            width: 100%;
        }

        .ip-container {
            border: 1.5pt solid #000;
            padding: 8px;
            text-align: center;
            width: 220px;
        }

        .predikat-box {
            font-size: 9pt;
            margin-top: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Tanda Tangan */
        .ttd-section {
            width: 100%;
            margin-top: 30px;
        }

        .ttd-table {
            width: 100%;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 9pt;
        }

        .space-signature {
            height: 60px;
        }

        .name-line {
            text-decoration: underline;
            font-weight: bold;
            display: block;
        }

        .footer-note {
            font-size: 7pt;
            margin-top: 50px;
            color: #555;
            border-top: 0.5pt solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <table class="kop-surat">
        <tr>
            <td style="text-align: center;">
                <h1>{{ config('app.name', 'UNIVERSITAS TERBUKA') }}</h1>
                <p>Alamat Kampus No. 123, Kota Anda, Kode Pos 54321</p>
                <p>Telp: (021) 1234567 | Website: www.universitas-anda.ac.id</p>
            </td>
        </tr>
    </table>
    <div class="kop-surat-border"></div>

    <div class="title-doc">KARTU HASIL STUDI (KHS)</div>

    <table class="info-section">
        <tr>
            <td width="18%">NIM</td>
            <td width="2%">:</td>
            <td width="30%" class="font-bold">{{ $mahasiswa->nim }}</td>
            <td width="18%">Semester</td>
            <td width="2%">:</td>
            <td width="30%">{{ $semester->nama_semester }}</td>
        </tr>
        <tr>
            <td>Nama Mahasiswa</td>
            <td>:</td>
            <td class="font-bold">{{ strtoupper($mahasiswa->user->nama_lengkap) }}</td>
            <td>Periode</td>
            <td>:</td>
            <td>{{ $semester->periode }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</td>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ $tanggal_cetak }}</td>
        </tr>
    </table>

    <table class="table-khs">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Kode MK</th>
                <th width="32%">Mata Kuliah</th>
                <th width="5%">SKS</th>
                <th width="8%">Tugas</th>
                <th width="8%">UTS</th>
                <th width="8%">UAS</th>
                <th width="8%">Akhir</th>
                <th width="7%">Huruf</th>
                <th width="8%">Indeks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($khs_data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_tampil }}</td>
                    <td class="text-left">
                        {{ $item->nama_mk }}
                        @if ($item->jenis_mk === 'Spesial')
                            <br><small class="italic">(Mata Kuliah Spesial)</small>
                        @endif
                    </td>
                    <td class="font-bold">{{ $item->sks }}</td>

                    {{-- Logika kolom nilai komponen --}}
                    @if ($item->jenis_mk === 'Spesial')
                        <td colspan="3" class="italic" style="color: #666; font-size: 8pt;">Nilai Terpadu</td>
                    @else
                        <td>{{ number_format($item->total_tugas ?? 0, 2) }}</td>
                        <td>{{ number_format($item->total_uts ?? 0, 2) }}</td>
                        <td>{{ number_format($item->total_uas ?? 0, 2) }}</td>
                    @endif

                    <td class="font-bold">{{ number_format($item->nilai_akhir_angka ?? 0, 2) }}</td>
                    <td class="font-bold">{{ $item->nilai_huruf ?? '-' }}</td>
                    <td>{{ number_format($item->nilai_indeks ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding: 30px;">Belum ada data nilai yang diinputkan untuk semester ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2;">
                <td colspan="3" class="font-bold" style="text-align: right; padding-right: 10px;">TOTAL SKS DIAMBIL
                </td>
                <td class="font-bold">{{ $total_sks }}</td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>

    <table width="100%" style="margin-top: 15px;">
        <tr>
            <td width="55%" style="vertical-align: top;">
                <div style="font-size: 8pt;">
                    <strong>Keterangan:</strong><br>
                    1. Nilai Akhir merupakan hasil akumulasi komponen nilai.<br>
                    2. <strong>SKS:</strong> Satuan Kredit Semester.<br>
                    3. <strong>IP Semester:</strong> Indeks Prestasi per Semester.<br>
                    4. Mata Kuliah Spesial (Skripsi/PKL/Bimbingan) menggunakan penilaian terpadu.
                </div>
            </td>
            <td width="45%" align="right">
                <div class="ip-container">
                    <span style="font-size: 9pt; display: block; margin-bottom: 5px;">INDEKS PRESTASI SEMESTER
                        (IPS)</span>
                    <span style="font-size: 22pt; font-weight: bold;">{{ number_format($ipk, 2) }}</span>
                    <div class="predikat-box">
                        Predikat:
                        @if ($ipk >= 3.51)
                            Cumlaude
                        @elseif($ipk >= 3.0)
                            Sangat Memuaskan
                        @elseif($ipk >= 2.75)
                            Memuaskan
                        @else
                            -
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="ttd-section">
        <table class="ttd-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Orang Tua/Wali Mahasiswa,
                    <div class="space-signature"></div>
                    <div style="border-bottom: 1px dotted #000; width: 150px; margin: 0 auto;"></div>
                </td>
                <td>
                    Kota Anda, {{ $tanggal_cetak }}<br>
                    Admin Akademik,
                    <div class="space-signature"></div>
                    <span class="name-line">{{ auth()->user()->nama_lengkap ?? 'Admin Akademik, M.Kom' }}</span>
                    NIP. 19820301 201001 1 002
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Dokumen ini diterbitkan secara elektronik melalui Sistem Informasi Akademik {{ config('app.name') }}.
        Keaslian dokumen dapat dikonfirmasi ke bagian administrasi kampus.
    </div>

</body>

</html>
