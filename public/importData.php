<?php 
// Load the database configuration file 
include_once '../config/dbConfig.php';  // Pastikan path ke dbConfig.php benar

// Include PhpSpreadsheet library autoloader 
require_once '../vendor/autoload.php';  // Pastikan path ke vendor/autoload.php benar
use PhpOffice\PhpSpreadsheet\Reader\Xlsx; 

// Cek jika tombol hapus data diklik
if (isset($_POST['deleteData'])) { 
    // Query untuk menghapus seluruh data dalam tabel
    $deleteQuery = "DELETE FROM tcare_service"; 

    if ($db->query($deleteQuery) === TRUE) {
        // Redirect dengan status sukses
        header("Location: index.php?status=deleted");
        exit; // Pastikan script berhenti setelah redirect
    } else {
        // Redirect dengan status error
        header("Location: index.php?status=err");
        exit;
    }
}

if(isset($_POST['importSubmit'])){ 
    // Allowed mime types 
    $excelMimes = array('text/xls', 'text/xlsx', 'application/excel', 'application/vnd.msexcel', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 

    // Validate whether selected file is an Excel file 
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $excelMimes)){ 

        // If the file is uploaded 
        if(is_uploaded_file($_FILES['file']['tmp_name'])){ 
            $reader = new Xlsx(); 
            $spreadsheet = $reader->load($_FILES['file']['tmp_name']); 
            $worksheet = $spreadsheet->getActiveSheet();  
            $worksheet_arr = $worksheet->toArray(); 

            // Remove header row 
            unset($worksheet_arr[0]); 

            foreach ($worksheet_arr as $row) {
                // Properly match the Excel columns to variables
                $no_rangka = $row[0]; 
                $spk = $row[1]; 
                $spk_date = $row[2]; 
                $model = $row[3]; 
                $type = $row[4]; 
                $type_tam = $row[5];  
                $nama_pelanggan = $row[6]; 
                $alamat_pelanggan = $row[7]; 
                $tgl_dec = $row[8]; 
                $kota_ktp = $row[9];  
                $no_hp = $row[10]; 
                $email = $row[11]; 
                $cabang_tam = $row[12]; 
                $cabang_pembelian = $row[13]; 
                $cabang_service_retention = $row[14]; 
                $om_pembelian = $row[15]; 
                $om_service_retention = $row[16];
                $cabang_receiver = $row[17]; 
            
                // Cek apakah tanggal valid
                $spk_date_obj = DateTime::createFromFormat('d-m-Y', $spk_date);
                $tgl_dec_obj = DateTime::createFromFormat('d-m-Y', $tgl_dec);
            
                // Cek apakah objek DateTime berhasil dibuat
                if ($spk_date_obj && $spk_date_obj->format('d-m-Y') == $spk_date) {
                    $spk_date = $spk_date_obj->format('Y-m-d');
                } else {
                    $spk_date = date('Y-m-d');  // Jika invalid, set ke waktu sekarang
                }
            
                if ($tgl_dec_obj && $tgl_dec_obj->format('d-m-Y') == $tgl_dec) {
                    $tgl_dec = $tgl_dec_obj->format('Y-m-d');
                } else {
                    $tgl_dec = date('Y-m-d');  // Jika invalid, set ke waktu sekarang
                }
            
                // Check whether data already exists in the database with the same no_rangka
                $prevQuery = "SELECT id FROM tcare_service WHERE no_rangka = '".$no_rangka."'"; 
                $prevResult = $db->query($prevQuery); 
            
                if($prevResult->num_rows > 0){ 
                    // Update existing data
                    $db->query("UPDATE tcare_service SET 
                        no_spk = '".$spk."', 
                        tgl_spk = '".$spk_date."', 
                        model = '".$model."', 
                        type = '".$type."', 
                        type_tam = '".$type_tam."', 
                        nama_pelanggan = '".$nama_pelanggan."', 
                        alamat_pelanggan = '".$alamat_pelanggan."', 
                        tgl_dec = '".$tgl_dec."', 
                        kota_ktp = '".$kota_ktp."', 
                        no_hp = '".$no_hp."', 
                        email = '".$email."', 
                        cabang_tam = '".$cabang_tam."', 
                        cabang_pembelian = '".$cabang_pembelian."', 
                        cabang_service_retention = '".$cabang_service_retention."', 
                        om_pembelian = '".$om_pembelian."', 
                        om_service_retention = '".$om_service_retention."',
                        cabang_receiver = '".$cabang_receiver."'  
                    WHERE no_rangka = '".$no_rangka."'"); 
                } else { 
                    // Insert new data into the database
                    $db->query('INSERT INTO tcare_service (no_rangka, no_spk, tgl_spk, model, type, type_tam, nama_pelanggan, alamat_pelanggan, tgl_dec, kota_ktp, no_hp, email, cabang_tam, cabang_pembelian, cabang_service_retention, om_pembelian, om_service_retention, cabang_receiver) 
                    VALUES ("'.$no_rangka.'", "'.$spk.'", "'.$spk_date.'", "'.$model.'", "'.$type.'", "'.$type_tam.'", "'.$nama_pelanggan.'", "'.$alamat_pelanggan.'", "'.$tgl_dec.'", "'.$kota_ktp.'", "'.$no_hp.'", "'.$email.'", "'.$cabang_tam.'", "'.$cabang_pembelian.'", "'.$cabang_service_retention.'", "'.$om_pembelian.'", "'.$om_service_retention.'", "'.$cabang_receiver.'"  
                    )'); 
                } 
            }

            // Redirect with success status
            $qstring = '?status=succ'; 
        } else { 
            $qstring = '?status=err'; 
        } 
    } else { 
        $qstring = '?status=invalid_file'; 
    } 
} 

// Redirect to the listing page with status message
header("Location: index.php".$qstring); 
?>
