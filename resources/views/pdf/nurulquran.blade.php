<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        /* CSS BODY ASLI (TIDAK DIUBAH SESUAI PERMINTAAN) */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            transform: scale(1.3) translate(0px, -33px);
            transform-origin: top center;
        }

        .id-card {
            position: relative;
            width: 638px;
            height: 1004px;
            overflow: hidden;
            page-break-after: always;
            background: #fff;
            margin: 0 auto;
        }

        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 638px;
            height: 1004px;
            object-fit: cover;
            z-index: 1;
        }

        /* FOTO PROFIL (CSS disederhanakan karena tidak lagi menggunakan flexbox untuk placeholder teks) */
        .photo {
            position: absolute;
            top: 233px;
            left: 189px;
            width: 260px;
            height: 320px;
            border-radius: 12px;
            object-fit: cover;
            z-index: 2;
            /* Properti Flexbox dihilangkan */
        }

        /* NIS */
        .nis {
            position: absolute;
            top: 590px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #000;
            z-index: 2;
            letter-spacing: 1px;
        }

        /* Nama */
        .name {
            position: absolute;
            top: 630px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 31px;
            font-weight: 900;
            color: #000;
            z-index: 2;
            line-height: 1.2;
        }

        /* TTL (Pemusatan Mutlak) */
        .ttl {
            position: absolute;
            top: 690px;
            text-align: center;
            font-size: 19px;
            font-weight: 500;
            color: #000;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }

        /* Alamat (Pemusatan Mutlak) */
        .alamat {
            position: absolute;
            top: 720px;
            text-align: center;
            font-size: 15px;
            font-weight: 400;
            color: #000;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%);
        }

        /* QR Code */
        .qrcode {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 160px;
            height: 160px;
            object-fit: contain;
            z-index: 2;
        }
    </style>
</head>

<body>
    @foreach($santri as $s)
    {{-- SISI DEPAN --}}
    <div class="id-card">
        <img src="{{ public_path('images/idcard/nurulquran_depan.png') }}" class="bg" alt="Background Depan">

        {{-- LOGIKA FOTO PROFIL SESUAI PERMINTAAN --}}
        @php
            $fotoPath = ($s->foto_profil && file_exists(public_path(parse_url($s->foto_profil, PHP_URL_PATH))))
                ? public_path(parse_url($s->foto_profil, PHP_URL_PATH))
                : public_path('images/default.png');
        @endphp

        <img src="{{ $fotoPath }}" class="photo" alt="Foto Santri">

        <div class="nis">NIS: {{ $s->nis }}</div>
        <div class="name">{{ $s->nama }}</div>

        <div class="ttl">
            {{ $s->tempat_lahir }}, {{ \Carbon\Carbon::parse($s->tanggal_lahir)->translatedFormat('d F Y') }}
        </div>

        <div class="alamat">
            {{ $s->nama_kecamatan ?? '' }} - {{ $s->nama_kabupaten ?? '' }} - {{ $s->nama_provinsi ?? '' }}
        </div>

        @if(isset($s->qrcode_path) && file_exists(public_path($s->qrcode_path)))
            <img src="{{ public_path($s->qrcode_path) }}" class="qrcode" alt="QR Code">
        @endif
    </div>
    @endforeach

    {{-- SISI BELAKANG --}}
    <div class="id-card">
        <img src="{{ public_path('images/idcard/nurulquran_belakang.png') }}" class="bg" alt="Background Belakang">
    </div>
</body>

</html>
