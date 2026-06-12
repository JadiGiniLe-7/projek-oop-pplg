<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['kopi'] = [];
    header("Location: soal.php");
    exit();
}

if (!isset($_SESSION['kopi'])) {
    $_SESSION['kopi'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $namaMenu = $_POST['nama_menu'] ?? '';
    $ukuran = $_POST['ukuran'] ?? '';
    $kodeVoucher = $_POST['kode_voucher'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $_SESSION['jml'] = $jumlah;

    $hargaMenu = [
        'Americano' => 15000,
        'Latte' => 18000,
        'Matcha' => 17000,
        'Chocolate' => 16000
    ];

    $harga = isset($hargaMenu[$namaMenu]) ? $hargaMenu[$namaMenu] : 0;
    
    $pesanan = new Pesanan();
    $pesanan->tambahPesanan($nama, $namaMenu, $harga, $ukuran, $jumlah);
    
    if ($kodeVoucher != '') {
        $pesanan->gunakanVoucher($kodeVoucher);
    }
    
    $totalHarga = $pesanan->hitungTotal();
    $poinMember = floor($totalHarga / 10000) * 10;

    $waktu = date('H.i.s');
    $_SESSION['kopi'][] = [
        'nama' => $nama,
        'nama_menu' => $namaMenu,
        'ukuran' => $ukuran,
        'kode_voucher' => $kodeVoucher,
        'harga_awal' => $pesanan->daftarMenu[0]['harga'],
        'total_bayar' => $totalHarga,
        'poin_member' => $poinMember,
        'jumlah' => $jumlah,
        'waktu' => $waktu
    ];

    header("Location: soal.php");
    exit();
}

class User {
    public $nama;
    private $poinMember;

    public function __construct($nama = '') {
        $this->nama = $nama;
    }

    public function getPoinMember() {
        return $this->poinMember;
    }

    public function setPoinMember($poinMember) {
        $this->poinMember = $poinMember;
        return $this;
    }
}

class Menu {
    public $namaMenu;
    public $harga;

    

    public function __construct($namaMenu, $harga) {
        $this->namaMenu = $namaMenu;
        $this->harga = $harga;
    }

    public function tampilMenu() {
        echo "Menu: " . $this->namaMenu . "<br>" . "Harga: " . $this->harga . "<br>";
    }

}

class Kopi extends Menu {
    public $ukuran;


}

class NonKopi extends Menu {
    public $rasa;
}

class Pesanan {
    public $daftarMenu = [];
    public $diskon = 0;
    public $jumlah;
    public $kodeVoucher = '';
    private $voucherValid = ['123456' => 10]; // kode => persentase diskon

    public function tambahPesanan($nama, $namaMenu, $harga, $ukuran, $jumlah) {
        if ($ukuran == 'Small') {
            $harga = floor($harga * 0.9);
        } elseif ($ukuran == 'Large') {
            $harga = floor($harga * 1.1);
        }

        $this->daftarMenu[] = [
            'nama' => $nama,
            'nama_menu' => $namaMenu,
            'ukuran' => $ukuran,
            'harga' => $harga
        ];

        return $this;
    }

    public function gunakanVoucher($kodeVoucher) {
        if (isset($this->voucherValid[$kodeVoucher])) {
            $this->kodeVoucher = $kodeVoucher;
            return true;
        }
        return false;
    }

    public function hitungTotal() {
        $total = 0;
        foreach ($this->daftarMenu as $menu) {
            $total += $menu['harga'] * $_SESSION['jml'];
        }

        if ($this->kodeVoucher != '') {
            $this->diskon = floor($total * 0.1);
        }

        return max(0, $total - $this->diskon);
    }

    public function hargaAkhir($harga) {
        return $harga - $this->diskon;
    }
}

class Voucher {
    public $kodeVoucher;
    public $diskon;
}

$nama1 = new Menu("Americano", 15000);
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <title>KopiNesia</title>
        <style>
            * {
                font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            }
            .container {
                display: flex;
                gap: 20px;
            }
            .left {
                flex: 1;
            }
            .right {
                flex: 1;
            }
            input, select, button {
                margin-bottom: 15px;
                display: block;
                border-radius: 8px;
            }

            button {
                background-color: #1F150C;
                color: white;
            }

            .card {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 20px;
                background-color: #E1DCC9;
            }
        </style>
    </head>
    <body>

        <h2>Coffee System</h2>

        <div class="container">
            <div class="left">
                <div id="order" class="card">
            <form method="post" action="">
                <input type="text" name="nama" placeholder="Nama Customer" style="width: 75%;height:30px">
                <select name="rasa" id="opsi" onchange="pilihOpsi()" style="width: 75%;height:30px">
                    <option value="Kopi">Kopi</option>
                    <option value="Nonkopi">Nonkopi</option>
                </select>
                <select name="nama_menu" id="nama-menu" style="width: 75%;height:30px">
                    <option value="Americano" class="kopi">Americano</option>
                    <option value="Latte" class="kopi">Latte</option>
                    <option value="Matcha" class="nonkopi" style="display:none;">Matcha</option>
                    <option value="Chocolate" class="nonkopi" style="display:none;">Chocolate</option>
                </select>
                <select name="ukuran" id="ukuran" style="width: 75%;height:30px">
                    <option value="Reguler">Reguler</option>
                    <option value="Small">Small</option>
                    <option value="Large">Large</option>
                </select>
                <input type="text" name="kode_voucher" placeholder="Voucher (opsional)" maxlength="6" style="width: 75%;height:30px">
                <input type="number" name="jumlah" placeholder="Jumlah Pesanan" style="width: 75%;height:30px">
                <button type="submit" style="width: 75%;height:30px">Order Now</button>
            </form>
                </div>

                <script>
                    function pilihOpsi() {
                        const opsi = document.getElementById('opsi').value;
                        const menuOptions = document.querySelectorAll('#nama-menu option');
                        
                        menuOptions.forEach(option => {
                            if (opsi === 'Kopi') {
                                if (option.classList.contains('kopi')) {
                                    option.style.display = 'block';
                                } else if (option.classList.contains('nonkopi')) {
                                    option.style.display = 'none';
                                }
                            } else if (opsi === 'Nonkopi') {
                                if (option.classList.contains('nonkopi')) {
                                    option.style.display = 'block';
                                } else if (option.classList.contains('kopi')) {
                                    option.style.display = 'none';
                                }
                            }
                        });
                        
                        // Reset pilihan menu ke pilihan pertama yang terlihat
                        document.getElementById('nama-menu').value = opsi === 'Kopi' ? 'Americano' : 'Matcha';
                    }
                    
                </script>
            </div>

            <div class="right">
                <div id="summary" class="card">
                    <h4>Order Summary</h4>
                    <?php if (!empty($_SESSION['kopi'])): ?>
                        <?php foreach($_SESSION['kopi'] as $data): ?>
                            <p>Nama: <?= $data['nama'] ?></p><br>
                            <p>Menu: <?= $data['nama_menu'] ?></p><br>
                            <p>Harga Awal: Rp <?= $data['harga_awal'] ?></p><br>
                            <p>Total Bayar: Rp <?= $data['total_bayar'] ?></p><br>
                            <p>Voucher: <?= $data['kode_voucher'] ?></p><br>
                            <p>Jumlah: <?= $data['jumlah'] ?></p>
                            <p>Poin: <?= $data['poin_member'] ?></p><br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada data.</p>
                    <?php endif; ?>
                </div>

                <div id="riwayat" class="card">
                    <h4>Riwayat Transaksi</h4>
                    <a href="?action=clear">Hapus Riwayat</a>
                    <?php if (!empty($_SESSION['kopi'])): ?>
                        <?php foreach($_SESSION['kopi'] as $transaksi): ?>
                            <p><?= $transaksi['waktu'] ?> - <?= $transaksi['nama_menu'] ?> - Rp <?= $transaksi['total_bayar'] ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada riwayat transaksi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </body>
</html>