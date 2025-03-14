<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/auth-style.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">

        <?php 
            // Mulai sesi dan cek status login
            session_start();
            
            // Koneksi ke database
            include_once '../config/dbConfig.php';

            if (isset($_POST['register'])) {
                $username = mysqli_real_escape_string($db, $_POST['username']);
                $email = mysqli_real_escape_string($db, $_POST['email']);
                $password = mysqli_real_escape_string($db, $_POST['password']);
                $confirmPassword = mysqli_real_escape_string($db, $_POST['confirmPassword']);

                // Cek apakah password dan konfirmasi password cocok
                if ($password != $confirmPassword) {
                    $error = "Password dan konfirmasi password tidak cocok!";
                } else {
                    // Enkripsi password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Cek apakah email sudah ada di database
                    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
                    $result = $db->query($query);

                    if ($result->num_rows > 0) {
                        $error = "Email sudah digunakan!";
                    } else {
                        // Simpan data pengguna ke database
                        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";
                        if ($db->query($query)) {
                            // Redirect ke halaman login setelah registrasi berhasil
                            header("Location: login.php");
                            exit;
                        } else {
                            $error = "Terjadi kesalahan, coba lagi.";
                        }
                    }
                }
            }
        ?>

            <!-- Form Registrasi -->
            <header>Sign Up</header>
            <form action="" method="post">
                <?php 
                if (isset($error)) {
                    echo "<div class='message'><p>$error</p></div><br>";
                }
                ?>
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" name="confirmPassword" id="confirmPassword" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="register" value="Register">
                </div>

                <div class="links">
                    Already a member? <a href="login.php">Sign In</a>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
