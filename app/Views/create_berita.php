<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah</title>
</head>

<body>
    <h5>Form Tambah berita</h5>
    <form action="<?= base_url('berita/create') ?>" method="POST">
        <?= csrf_field() ?>
        <table>
            <tr>
                <td>Judul</td>
                <td>
                    <input type="text" name="judul" value="" placeholder="Ketikkan Judul">
                </td>
            </tr>
            <tr>
                <td>Isi Berita</td>
                <td>
                    <input type="text" name="isi" value="" placeholder="Ketikkan Isi berita">
                </td>
            </tr>
            <tr>
                <td>Gambar</td>
                <td>
                    <input type="text" name="gambar" value="" placeholder="Tambah Gambar">
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" name="simpan" value="Simpan">
                </td>
            </tr>
        </table>
    </form>
</body>

</html>