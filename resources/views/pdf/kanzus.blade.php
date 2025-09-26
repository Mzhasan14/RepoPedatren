<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            transform: scale(4.2) translate(0px, -33px);
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

        /* foto */
        .photo {
            position: absolute;
            top: 390px;
            /* atur biar pas */
            left: 210px;
            /* tengah */
            width: 220px;
            height: 280px;
            border: 3px solid #000;
            object-fit: cover;
            z-index: 2;
        }

        /* nama */
        .name {
            position: absolute;
            top: 700px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 32px;
            /* agak lebih besar */
            font-weight: 900;
            /* super bold */
            font-family: 'Poppins', sans-serif;
            /* bisa juga pakai Montserrat */
            z-index: 2;
        }

        .name-line {
            position: absolute;
            top: 740px;
            /* pas di bawah nama */
            left: 150px;
            width: 340px;
            height: 2px;
            background-color: #000;
            z-index: 2;
        }

        /* nis */
        .nis {
            font-family: 'Poppins', sans-serif;
            position: absolute;
            top: 750px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            z-index: 2;
        }

        /* ttl */
        .ttl {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            position: absolute;
            top: 790px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 18px;
            z-index: 2;
        }

        /* alamat */
        .alamat {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            position: absolute;
            top: 830px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 16px;
            line-height: 1.4em;
            padding: 0 20px;
            z-index: 2;
        }
    </style>
</head>

<body>
    @foreach($santri as $s)
    <div class="id-card">
        <img src="{{ public_path('images/idcard/kanzus_template.jpg') }}" class="bg" alt="Background ID Card">

        @php
        $fotoPath = ($s->foto_profil && file_exists(public_path(parse_url($s->foto_profil, PHP_URL_PATH))))
        ? public_path(parse_url($s->foto_profil, PHP_URL_PATH))
        : public_path('images/default.png');
        @endphp

        <img src="{{ $fotoPath }}" class="photo" alt="Foto Santri">

        <div class="name">{{ $s->nama }}</div>
        <div class="name-line"></div>
        <div class="nis">NIS : {{ $s->nis }}</div>
        <div class="ttl">{{ $s->tempat_lahir }}, {{ \Carbon\Carbon::parse($s->tanggal_lahir)->translatedFormat('d F Y') }}</div>
        <div class="alamat">
            {{ $s->jalan }}, {{ $s->nama_kecamatan }}, {{ $s->nama_kabupaten }}, {{ $s->nama_provinsi }}
        </div>

    </div>
    @endforeach
    <div class="id-card">
        <img src="{{ public_path('images/idcard/kanzus_belakang.jpg') }}" class="bg" alt="Background Belakang">
    </div>
</body>

</html>
