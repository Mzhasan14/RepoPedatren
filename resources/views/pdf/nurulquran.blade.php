<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        /* CSS BODY ASLI (TIDAK DIUBAH) */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            transform: scale(4.1) translate(0px, -33px);
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

        .photo {
            position: absolute;
            top: 268px;
            left: 185px;
            width: 268px;
            height: 336px;
            border-radius: 18px;
            object-fit: cover;
            z-index: 2;
        }

        .nis {
            position: absolute;
            top: 650px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #000;
            z-index: 2;
            letter-spacing: 1px;
        }

        .name {
            position: absolute;
            top: 695px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 40px;
            font-weight: 900;
            color: #000;
            z-index: 2;
            line-height: 1.2;
        }

        .ttl {
            position: absolute;
            top: 760px;
            text-align: center;
            font-size: 19px;
            font-weight: 500;
            color: #000;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }

        .alamat {
            position: absolute;
            top: 790px;
            text-align: center;
            font-size: 15px;
            font-weight: 400;
            color: #000;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
</head>

<body>
    @foreach($santri as $s)
    {{-- SISI DEPAN --}}
    <div class="id-card">
        <img src="{{ public_path('images/idcard/nurulquran_depan.png') }}" class="bg" alt="Background Depan">

        @php
        /* LOGIKA FOTO DIJAGA */
        $fotoPath = ($s->foto_profil && file_exists(public_path(parse_url($s->foto_profil, PHP_URL_PATH))))
        ? public_path(parse_url($s->foto_profil, PHP_URL_PATH))
        : public_path('images/default.png'); // Pastikan ini adalah placeholder yang sesuai
        @endphp

        <img src="{{ $fotoPath }}" class="photo" alt="Foto Santri">

        <div class="nis">NIS: {{ $s->nis }}</div>
        <div class="name">{{ $s->nama }}</div>

        <div class="ttl">
            {{ $s->tempat_lahir }}, {{ \Carbon\Carbon::parse($s->tanggal_lahir)->translatedFormat('d F Y') }}
        </div>

        <div class="alamat">
            {{ $s->alamat_lengkap ?? $s->nama_kecamatan }} - {{ $s->nama_kabupaten }} - {{ $s->nama_provinsi }}
        </div>


    </div>
    @endforeach

    {{-- SISI BELAKANG --}}
    <div class="id-card">
        <img src="{{ public_path('images/idcard/nurulquran_belakang.png') }}" class="bg" alt="Background Belakang">
    </div>
</body>

</html>
