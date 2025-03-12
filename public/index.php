<?php
// Memulai sesi untuk pengecekan login
session_start(); 

// Cek apakah pengguna sudah login
// if (!isset($_SESSION['username'])) {
//     // Jika tidak login, arahkan ke halaman login
//     header("Location: login.php");
//     exit; // Pastikan tidak ada kode lain yang dieksekusi setelah redirect
// }

// Load the database configuration file 
include_once '../config/dbConfig.php';  // Pastikan path ini benar

// Get status message
if (!empty($_GET['status'])) {
    switch ($_GET['status']) {
        case 'succ':
            $statusType = 'alert-success';
            $statusMsg = 'Data berhasil diimpor ke MySQL!';
            break;
        case 'err':
            $statusType = 'alert-danger';
            $statusMsg = 'Terjadi kesalahan, coba lagi.';
            break;
        case 'invalid_file':
            $statusType = 'alert-danger';
            $statusMsg = 'File yang diunggah tidak valid.';
            break;
        case 'deleted':
            $statusType = 'alert-success';
            $statusMsg = 'Semua data telah berhasil dihapus.';
            break;
        default:
            $statusType = '';
            $statusMsg = '';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & Import Excel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Path ke file CSS yang benar -->
</head>
<body>

<div class="container">
    <!-- Display Status Message -->
    <?php if (!empty($statusMsg)) { ?>
        <div class="alert <?php echo $statusType; ?>" role="alert">
            <?php echo $statusMsg; ?>
        </div>
    <?php } ?>

    <!-- Button for Upload Form -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>EXCEL TO PDF</h2>
        <button class="btn btn-success" onclick="formToggle('importFrm')">+ Import Excel</button>
    </div>

    <!-- Form for Upload -->
    <div id="importFrm" style="display: none;">
        <form action="importData.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileInput" class="form-label">Pilih File Excel</label>
                <input type="file" class="form-control" name="file" id="fileInput" required>
            </div>
            <button type="submit" name="importSubmit" class="btn btn-primary">Import</button>
        </form>
    </div>

    <!-- Button Hapus Semua Data -->
    <form action="importData.php" method="post">
        <button type="submit" name="deleteData" class="btn btn-danger">Hapus Semua Data</button>
    </form>

    <!-- Button Print All PDF -->
    <form action="generate_all_pdfs.php" method="post">
        <button type="submit" class="btn btn-warning">Print All PDF</button>
    </form>

    <!-- Data Table -->
    <h3>Data Tabel</h3>
    <div class="table-wrapper">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Aksi</th> <!-- Print Button Column -->
                    <th>No Rangka</th>
                    <th>SPK</th>
                    <th>Model</th>
                    <th>Type</th>
                    <th>Nama Pelanggan</th>
                    <th>Alamat Pelanggan</th>
                    <th>Tanggal DEC</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th>Cabang TAM</th>
                    <th>Cabang Pembelian</th>
                    <th>Cabang Service Retention</th>
                    <th>OM Pembelian</th>
                    <th>OM Service Retention</th>
                    <th>Cabang Receiver</th> <!-- Added the new column -->
                </tr>
            </thead>
            <tbody>
            <?php
            // Query untuk mengambil data dari tabel tcare_service
            $query = "SELECT * FROM tcare_service"; 
            $result = $db->query($query);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td>
                        <!-- Action Button for Print -->
                        <a href="../src/generatePDF.php?no_rangka=<?php echo $row['no_rangka']; ?>" target="_blank" class="btn btn-info btn-sm">Print</a>
                    </td>
                    <td><?php echo $row['no_rangka']; ?></td>
                    <td><?php echo $row['no_spk']; ?></td>
                    <td><?php echo $row['model']; ?></td>
                    <td><?php echo $row['type']; ?></td>
                    <td><?php echo $row['nama_pelanggan']; ?></td>
                    <td><?php echo $row['alamat_pelanggan']; ?></td>
                    <td><?php echo $row['tgl_dec']; ?></td>
                    <td><?php echo $row['no_hp']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['cabang_tam']; ?></td> 
                    <td><?php echo $row['cabang_pembelian']; ?></td>
                    <td><?php echo $row['cabang_service_retention']; ?></td>
                    <td><?php echo $row['om_pembelian']; ?></td>
                    <td><?php echo $row['om_service_retention']; ?></td>
                    <td><?php echo $row['cabang_receiver']; ?></td> <!-- Displaying the new column -->
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='16'>Tidak ada data yang ditemukan.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
// Toggle the visibility of the import form
function formToggle(ID) {
    var element = document.getElementById(ID);
    if (element.style.display === "none") {
        element.style.display = "block";
    } else {
        element.style.display = "none";
    }
}
</script>

</body>
</html>
