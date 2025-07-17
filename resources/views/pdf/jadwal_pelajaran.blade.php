<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #000;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header h4 {
            margin: 5px 0;
            font-size: 14px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info td {
            padding: 4px 8px;
        }

        .day-title {
            font-weight: bold;
            margin-top: 25px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: auto;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 5px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>JADWAL PELAJARAN {{ strtoupper($meta['semester']) }} {{ $meta['tahun_ajaran'] }}</h2>
        <h4>
            Lembaga: {{ $meta['lembaga'] }}<br>
            Jurusan: {{ $meta['jurusan'] }}<br>
            Kelas: {{ $meta['kelas'] }} {{ $meta['rombel'] }}
        </h4>
    </div>

    @foreach ($data as $hari => $jadwals)
        <div class="day-title">{{ strtoupper($hari) }} :</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 15%">Kode Mapel</th>
                    <th style="width: 30%">Mata Pelajaran</th>
                    <th style="width: 25%">Pengajar</th>
                    <th style="width: 10%">Jam Ke</th>
                    <th style="width: 15%">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwals as $i => $jadwal)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $jadwal['kode_mapel'] ?? '-' }}</td>
                        <td>{{ $jadwal['nama_mapel'] }}</td>
                        <td>{{ $jadwal['nama_pengajar'] ?? '-' }}</td>
                        <td>{{ $jadwal['jam_ke'] }}</td>
                        <td>{{ $jadwal['jam_mulai'] }} s/d {{ $jadwal['jam_selesai'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        <p>Probolinggo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <br><br><br>
        <p><strong>Kepala Sekolah</strong></p>
        <p style="margin-top:60px;"><strong>( ______________________ )</strong></p>
    </div>

</body>
</html>
