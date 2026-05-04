<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Transkrip Akademik - {{ $mahasiswa->nim }}</title>
    <style>
        @page {
            margin: 0.4in 0.5in;
            size: A4 portrait;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 8.5pt;
            line-height: 1.2;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
        }

        .header h2 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }

        /* Branding Logo (Opsional jika ada logo B.A.A) */
        .logo-placeholder {
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            border: 1px dashed #ccc;
            /* Ganti dengan tag img jika ada file logo */
            text-align: center;
            font-size: 7pt;
        }

        /* Biodata Section */
        .biodata {
            width: 100%;
            margin-bottom: 15px;
        }

        .biodata td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* Table Transkrip Style */
        .table-container {
            width: 100%;
        }

        .table-transkrip {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .table-transkrip th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            padding: 4px 2px;
            font-size: 8pt;
            text-align: center;
        }

        .table-transkrip td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        /* Summary Section */
        .summary-box {
            margin-top: 10px;
            width: 100%;
        }

        .summary-content {
            float: right;
            width: 300px;
            border: 1px solid #000;
            padding: 5px;
        }

        .summary-content table {
            width: 100%;
        }

        .summary-content td {
            font-weight: bold;
            font-size: 9pt;
        }

        /* Footer / Tanda Tangan */
        .footer-section {
            margin-top: 20px;
            width: 100%;
            clear: both;
        }

        .ttd-table {
            width: 100%;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .space {
            height: 60px;
        }

        .catatan {
            font-size: 7pt;
            font-style: italic;
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>TRANSKRIP AKADEMIK SEMENTARA</h2>
    </div>

    <table class="biodata">
        <tr>
            <td width="15%">NAMA</td>
            <td width="2%">:</td>
            <td width="33%"><strong>{{ strtoupper($mahasiswa->user->nama_lengkap) }}</strong></td>
            <td width="15%">PROGRAM</td>
            <td width="2%">:</td>
            <td width="33%">STRATA 1 (S1)</td>
        </tr>
        <tr>
            <td>TPT/TGL. LAHIR</td>
            <td>:</td>
            <td>- / {{ \Carbon\Carbon::parse($mahasiswa->tanggal_lahir ?? now())->format('d-m-Y') }}</td>
            <td>JURUSAN</td>
            <td>:</td>
            <td>{{ strtoupper($mahasiswa->jurusan->nama_jurusan ?? '-') }}</td>
        </tr>
        <tr>
            <td>N.R.P / NIM</td>
            <td>:</td>
            <td>{{ $mahasiswa->nim }}</td>
            <td>NO. IJAZAH</td>
            <td>:</td>
            <td>-</td>
        </tr>
    </table>

    <table class="table-transkrip">
        <thead>
            <tr>
                <th width="3%">NO.</th>
                <th width="8%">KODE</th>
                <th width="28%">MATA KULIAH</th>
                <th width="5%">SKS</th>
                <th width="6%">NILAI</th>

                <th width="3%">NO.</th>
                <th width="8%">KODE</th>
                <th width="28%">MATA KULIAH</th>
                <th width="5%">SKS</th>
                <th width="6%">NILAI</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leftColumn as $index => $left)
                @php
                    $right = $rightColumn->get($leftColumn->count() + $index);
                @endphp
                <tr>
                    {{-- Kolom Kiri --}}
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $left->kode_tampil }}</td>
                    <td class="text-left">{{ strtoupper($left->nama_mk) }}</td>
                    <td>{{ $left->sks }}</td>
                    <td>{{ $left->huruf }}</td>

                    {{-- Kolom Kanan --}}
                    @if ($right)
                        <td>{{ $leftColumn->count() + $index + 1 }}</td>
                        <td>{{ $right->kode_tampil }}</td>
                        <td class="text-left">{{ strtoupper($right->nama_mk) }}</td>
                        <td>{{ $right->sks }}</td>
                        <td>{{ $right->huruf }}</td>
                    @else
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-content">
            <table>
                <tr>
                    <td width="60%">TOTAL KREDIT (BOBOT)</td>
                    <td width="5%">:</td>
                    <td align="right">{{ number_format($totalKredit, 2) }}</td>
                </tr>
                <tr>
                    <td>TOTAL S.K.S Ambil</td>
                    <td>:</td>
                    <td align="right">{{ $totalSks }}</td>
                </tr>
                <tr>
                    <td>I.P.K (Indeks Prestasi)</td>
                    <td>:</td>
                    <td align="right">{{ number_format($ipk, 2) }}</td>
                </tr>
                <tr>
                    <td>PREDIKAT</td>
                    <td>:</td>
                    <td align="right">
                        @if ($ipk >= 3.76)
                            DENGAN PUJIAN
                        @elseif($ipk >= 3.51)
                            SANGAT MEMUASKAN
                        @elseif($ipk >= 3.0)
                            MEMUASKAN
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer-section">
        <table class="ttd-table">
            <tr>
                <td>
                    {{-- Kosong untuk WAKET --}}
                </td>
                <td>
                    Palangkaraya, {{ $tanggal_cetak }}<br>
                    <strong>KETUA,</strong>
                    <div class="space"></div>
                    <strong><u>NAMA KETUA KAMPUS, M.Kom</u></strong><br>
                    NIP. 19700101 200003 1 001
                </td>
            </tr>
        </table>
    </div>

    <div class="catatan">
        Catatan:<br>
        - Nilai A = BAIK SEKALI, B = BAIK, C = CUKUP, D = KURANG, E = GAGAL<br>
        - Transkrip ini adalah salinan sementara untuk keperluan akademik internal.
    </div>

</body>

</html>
