<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API di Blade</title>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('/api/santri') // Ganti dengan endpoint API yang kamu buat
                .then(response => response.json())
                .then(responseData => {
                    // Akses data yang ada di dalam response.data
                    let santriData = responseData.data.data; // Memilih data dari response

                    let output = '';
                    santriData.forEach((santri, index) => {
                        output += `<tr>
                            <td>${index + 1}</td>
                            <td>${santri.nis}</td>
                            <td>${santri.jenjang_pendidikan_terakhir}</td>
                            <td>${santri.smartcard}</td>
                          </tr>`;
                    });
                    document.getElementById('data-body').innerHTML = output;
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</head>

<body>
    <h2>Data Santri</h2>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIS</th>
                <th>Kelas</th>
            </tr>
        </thead>
        <tbody id="data-body">
            <!-- Data dari API akan masuk di sini -->
        </tbody>
    </table>
</body>

</html>
