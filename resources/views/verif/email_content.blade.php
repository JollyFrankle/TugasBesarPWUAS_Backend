<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Selamat</h2>
    <p>Akun dengan nama {{ $content["nama"] }} berhasil didaftarkan dan menunggu verifikasi.</p>
    <p>Untuk melanjutkan proses verifikasi silahkan klik link berikut ini.</p>
    <p><a href="http://10.53.4.164:8000/verif/{{ $content['token'] }}">Verifikasi</a></p>
    <p>Terima kasih</p>
</body>

</html>
