<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            transform: scale(4.2) translate(0px, -34px);
            transform-origin: top center;
        }

        .id-card {
            position: relative;
            width: 324px;
            height: 203px;
            overflow: hidden;
            page-break-after: always;
            background: #fff;
            border: 1px solid #ccc;
            margin: 0 auto;
        }

        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 324px;
            height: 203px;
            object-fit: cover;
            z-index: 1;
        }

        /* foto */
        .photo {
            position: absolute;
            top: 54px;
            left: 25px;
            width: 70px;
            height: 90px;
            border: 2px solid #4CAF50;
            border-radius: 6px;
            object-fit: cover;
            background: #f0f0f0;
            z-index: 2;
        }

        /* biodata */
        .info {
            position: absolute;
            top: 55px;
            left: 125px;
            right: 15px;
            font-size: 10px;
            line-height: 1.4em;
            color: #000;
            z-index: 2;
            max-height: 120px;
            overflow: hidden;
            word-wrap: break-word;
        }

        .info p {
            margin: 0 0 4px;
        }

        .info strong {
            display: inline-block;
            width: 50px;
        }

        /* belakang (aturan kartu) */
        .rules {
            position: absolute;
            top: 40px;
            left: 20px;
            right: 20px;
            font-size: 11px;
            line-height: 1.5em;
            color: #000;
            z-index: 2;
        }

        .rules ol {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>

<body>
    @foreach($santri as $s)
    {{-- Kartu Depan --}}
    <div class="id-card">
        <img src="{{ public_path('images/idcard/depan.jpg') }}" class="bg" alt="Background Depan">

        @php
        $fotoPath = ($s->foto_profil && file_exists(public_path(parse_url($s->foto_profil, PHP_URL_PATH))))
        ? public_path(parse_url($s->foto_profil, PHP_URL_PATH))
        : public_path('images/default.png');
        @endphp
        <img src="{{ $fotoPath }}" class="photo" alt="Foto">


        <div class="info">
            <p><strong>Nama</strong>: {{ $s->nama }}</p>
            <p><strong>NIS</strong>: {{ $s->nis }}</p>
            <p><strong>TTL</strong>: {{ $s->tempat_lahir }}, {{ \Carbon\Carbon::parse($s->tanggal_lahir)->format('d M Y') }}</p>
            <p><strong>Alamat</strong>: {{ $s->jalan }}, {{ $s->nama_kecamatan }}, {{ $s->nama_kabupaten }}, {{ $s->nama_provinsi }}</p>
        </div>
    </div>

    @endforeach
    <!-- {{-- Kartu Belakang --}} -->
    <div class="id-card">
        <img src="{{ public_path('images/idcard/belakang.png') }}" class="bg" >
        <!-- <div class="rules">
            <ol>
                <li>Kartu ini adalah Kartu Identitas Santri</li>
                <li>Kartu ini digunakan untuk keperluan administrasi di Pesantren</li>
                <li>Apabila kartu ini hilang, segera hubungi kantor pesantren dengan mengganti biaya administrasi</li>
                <li>Kartu ini hanya berlaku di PP. Ar-Rofiyyah dan hanya bisa digunakan oleh pemiliknya selama masih aktif menjadi santri</li>
            </ol>
        </div> -->
    </div>
</body>

</html>
