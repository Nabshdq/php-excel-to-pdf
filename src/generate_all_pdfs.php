<?php
ob_start();
require_once '../vendor/autoload.php';  // Path yang benar ke autoload.php dari vendor
include_once '../config/dbConfig.php';  // Path yang benar ke dbConfig.php

use TCPDF;

// Ensure 'downloads' folder exists and is accessible
$downloads_dir = __DIR__ . '/../downloads/';  // Path relatif yang benar ke folder 'downloads'
if (!is_dir($downloads_dir)) {
    // If the folder does not exist, create it
    mkdir($downloads_dir, 0777, true);
}

// Hapus semua file PDF lama sebelum generate baru
$files = glob($downloads_dir . '*.pdf');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

// Hapus file ZIP lama
$zipPath = $downloads_dir . 'all_pdfs.zip';
if (file_exists($zipPath)) {
    unlink($zipPath);
}

// Name for the ZIP file that will package all PDFs
$zipFileName = 'all_pdfs.zip';
$zip = new ZipArchive();
$zip->open($downloads_dir . $zipFileName, ZipArchive::CREATE);

// Query to fetch data
$query = "SELECT * FROM tcare_service";
$result = $db->query($query);

if ($result->num_rows > 0) {

    // Define the function to add boxes with text (defined outside of loop to avoid redeclaration)
    function addBoxWithText($pdf, $x, $y, $width, $height, $label, $value) {
        $pdf->Rect($x, $y, $width, $height);  // Draw the box

        // Add label
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($width, 10, $label, 0, 'L', 0, 1);

        // Add value
        $pdf->SetXY($x + 5, $y + 5); // Adjust position for value
        $pdf->MultiCell($width, 10, $value, 0, 'L', 0, 1);
    }

    while ($row = $result->fetch_assoc()) {
        $no_rangka = $row['no_rangka'];
        $cabang_tam = $row['cabang_tam'];

        // Create TCPDF object for each PDF
        $pdf = new TCPDF();
        $pdf->AddPage();

        // Add border to the page
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(5, 5, 200, 287);

        // Add title text
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 4, 'FORM TCARE/SERVICE RETENTION FOLLOW UP TRANSFER INFORMATION', 0, 1, 'L');
        $pdf->Ln(10);

        // Table headers
        $pdf->SetLineWidth(0.25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(95, 10, 'DATA SPK', 1, 0, 'C', 0);
        $pdf->Cell(95, 10, 'DATA KENDARAAN', 1, 1, 'C', 0);

        // Coordinates for the first box
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Data for boxes
        $boxes = [
            ['label' => 'No SPK :', 'value' => $row['no_spk'], 'x' => $x, 'y' => $y],
            ['label' => 'Model :', 'value' => $row['model'], 'x' => $x + 95, 'y' => $y],
            ['label' => 'Tanggal SPK :', 'value' => $row['tgl_spk'], 'x' => $x, 'y' => $y + 13],
            ['label' => 'Type :', 'value' => $row['type_tam'], 'x' => $x + 95, 'y' => $y + 13],
            ['label' => 'Nama Pelanggan (sesuai SPK) :', 'value' => $row['nama_pelanggan'], 'x' => $x, 'y' => $y + 26],
            ['label' => 'No. Rangka :', 'value' => $row['no_rangka'], 'x' => $x + 95, 'y' => $y + 26],
            ['label' => 'Alamat Pelanggan (sesuai KTP) :', 'value' => $row['alamat_pelanggan'], 'x' => $x, 'y' => $y + 39],
            ['label' => 'Tanggal DEC Plan :', 'value' => $row['tgl_dec'], 'x' => $x + 95, 'y' => $y + 39],
        ];

        // Add boxes to the PDF
        foreach ($boxes as $box) {
            addBoxWithText($pdf, $box['x'], $box['y'], 95, 13, $box['label'], $box['value']);
        }

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 9);
        $html = 'Bersedia untuk memberikan data berikut agar dapat di-<i>follow up</i> terkait dengan fasilitas T-Care dan juga <i>Service Retention</i>';
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, 'No HP (aktif)           : ' . $row['no_hp'], 0, 1, 'L');

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, 'Alamat Email (aktif) : ' . $row['email'], 0, 1, 'L');

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 9);
        $html = '*Dealer <i>Receiver</i> T-Care atau Service Retention harus berada di dalam 1 FOA City dengan alamat pelanggan.';
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(10);

        // Tanda tangan Kepala Cabang Dealer
        // Menambahkan gambar tanda tangan berdasarkan nama dari kolom cabang_pembelian
        $signatureCabangPembelian = strtolower(str_replace(' ', '_', $row['cabang_pembelian'])); // Mengubah nama menjadi huruf kecil dan mengganti spasi dengan _
        $signaturePathCabangPembelian = '../uploads/tandaTangan/' . $signatureCabangPembelian . '.jpg'; // Path gambar untuk cabang_pembelian
        if (file_exists($signaturePathCabangPembelian)) {
            $pdf->Image($signaturePathCabangPembelian, 30, 140, 60, 30, 'JPG'); // Menambahkan gambar tanda tangan cabang_pembelian
        }
        // Menambahkan gambar tanda tangan berdasarkan nama dari kolom cabang_service_retention
        $signatureCabangService = strtolower(str_replace(' ', '_', $row['cabang_service_retention'])); // cabang_service_retention
        $signaturePathCabangService = '../uploads/tandaTangan/' . $signatureCabangService . '.jpg'; // Path gambar untuk cabang_service_retention
        if (file_exists($signaturePathCabangService)) {
            $pdf->Image($signaturePathCabangService, 120, 140, 60, 30, 'JPG'); // Menambahkan gambar tanda tangan cabang_service_retention
        }

        // bagian jabatan kepala cabang dealer
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(90, 4, 'Kepala Cabang', 0, 0, 'C');
        $pdf->Cell(95, 4, 'Kepala Cabang', 0, 1, 'C');
        $pdf->Cell(100, 4, 'Dealer Pembelian', 0, 0, 'C');
        $pdf->Cell(85, 4, 'Dealer Service / Retention*', 0, 1, 'C');
        $pdf->Ln(20);

        // garis pemisah
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->Cell(90, 5, '______________________________', 0, 0, 'C');
        $pdf->Cell(95, 5, '______________________________', 0, 1, 'C');

        // Nama Kepala Cabang Dealer 
        // Nama Kepala Cabang Dealer Pembelian
        $pdf->SetXY(15, $pdf->GetY());
        $cabangPembelian = !empty($row['cabang_pembelian']) ? strtoupper($row['cabang_pembelian']) : 'Nama Jelas'; // Jika kosong, tampilkan 'Nama Tidak Tersedia'
        $pdf->Cell(90, 5, $cabangPembelian, 0, 0, 'C'); // Menggunakan nama dinamis atau nilai default

        // Nama Kepala Cabang Dealer Service / Retention
        $pdf->SetXY(105, $pdf->GetY());
        $cabangService = !empty($row['cabang_service_retention']) ? strtoupper($row['cabang_service_retention']) : ''; // Jika kosong, tampilkan 'Nama Tidak Tersedia'
        $pdf->Cell(95, 5, $cabangService, 0, 1, 'C');
        $pdf->Ln(10);

        // -----------------
        // Tanda tangan OM / RBH / COO Dealer
        // Menambahkan gambar tanda tangan berdasarkan nama dari kolom om_pembelian
        $signatureOmPembelian = strtolower(str_replace(' ', '_', $row['om_pembelian'])); // om_pembelian
        $signaturePathOmPembelian = '../uploads/tandaTangan/' . $signatureOmPembelian . '.jpg'; // Path gambar untuk om_pembelian
        if (file_exists($signaturePathOmPembelian)) {
            $pdf->Image($signaturePathOmPembelian, 35, 185, 45, 30, 'JPG'); // Menambahkan gambar tanda tangan om_pembelian
        }
        // Menambahkan gambar tanda tangan berdasarkan nama dari kolom om_service_retention
        $signatureOmService = strtolower(str_replace(' ', '_', $row['om_service_retention'])); // om_service_retention
        $signaturePathOmService = '../uploads/tandaTangan/' . $signatureOmService . '.jpg'; // Path gambar untuk om_service_retention
        if (file_exists($signaturePathOmService)) {
            $pdf->Image($signaturePathOmService, 125, 185, 45, 30, 'JPG'); // Menambahkan gambar tanda tangan om_service_retention
        }

        // bagian jabatan OM / RBH / COO
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->Cell(90, 5, 'OM / RBH / COO', 0, 0, 'C');
        $pdf->Cell(95, 5, 'OM / RBH / COO', 0, 1, 'C');
        $pdf->Cell(100, 4, 'Dealer Pembelian', 0, 0, 'C');
        $pdf->Cell(85, 4, 'Dealer Service / Retention*', 0, 1, 'C');
        $pdf->Ln(20);

        // garis pemisah
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->Cell(90, 5, '______________________________', 0, 0, 'C');
        $pdf->Cell(95, 5, '______________________________', 0, 1, 'C');

        // Nama OM / RBH / COO Dealer Pembelian
        $pdf->SetXY(15, $pdf->GetY());
        $omPembelian = !empty($row['om_pembelian']) ? strtoupper($row['om_pembelian']) : 'Nama Jelas'; // Jika kosong, tampilkan 'Nama Tidak Tersedia'
        $pdf->Cell(90, 5, $omPembelian, 0, 0, 'C'); // Menggunakan nama dinamis atau nilai default

        // Nama OM / RBH / COO Dealer Service / Retention
        $omService = !empty($row['om_service_retention']) ? strtoupper($row['om_service_retention']) : ''; // Jika kosong, tampilkan 'Nama Tidak Tersedia'
        $pdf->Cell(95, 5, $omService, 0, 1, 'C');

        $pdf->Ln(20);
        $pdf->SetXY(15, $pdf->GetY());

        // Set font to italic for Cabang Pembelian
        $pdf->SetFont('helvetica', 'I', 9);  // 'I' sets the font to italic

        // Ganti garis dengan teks dinamis untuk Cabang Pembelian
        $pdf->Cell(90, 5, 'Cabang Pembelian: ' . strtoupper($row['cabang_tam']), 0, 0, 'C');

        // Set font to italic for Cabang Receiver
        $pdf->SetFont('helvetica', 'I', 9);  // 'I' sets the font to italic

        // Ganti garis dengan teks dinamis untuk Cabang Receiver
        $pdf->Cell(95, 5, 'Cabang Receiver: ' . strtoupper($row['cabang_receiver']), 0, 1, 'C');

        // Membuat nama file PDF sesuai dengan no_rangka
        // $file_name = $no_rangka . '.pdf';

        // // membuat nama file PDF "T-care Form Pelwil Eksternal Kalla Toyota xxxxxx.pdf"
        // $file_name = 'T-care Form Pelwil Eksternal Kalla Toyota ' . $no_rangka . '.pdf';

        // Membuat nama file PDF sesuai dengan no_rangka dan cabang_tam
        $file_name = 'T-care Form Pelwil Eksternal Kalla Toyota ' . $no_rangka . '.pdf';

        // Save the PDF to the 'downloads' folder
        $file_path = $downloads_dir . $file_name;
        $pdf->Output($file_path, 'F'); // Save the file instead of displaying

        // Add the PDF to the ZIP file
        $zip->addFile($file_path, $file_name);
    }

    // Close the ZIP file after finishing
    $zip->close();

    // Provide a download link for the ZIP file containing all PDFs
    echo "Semua PDF telah berhasil dibuat dan dikemas ke dalam file ZIP. <a href='../downloads/$zipFileName' download>Unduh Semua PDF</a>";
} else {
    echo "Tidak ada data ditemukan.";
}
?>
