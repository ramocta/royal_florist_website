<?php
require_once 'koneksi.php';

// Jika user sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            // Mencari berdasarkan kolom email di database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $storedPassword = $user['password'] ?? '';
                
                // Verifikasi password (mendukung teks biasa atau hash)
                if ($password === $storedPassword || password_verify($password, $storedPassword)) {
                    $_SESSION['user_id']         = $user['id'] ?? 1;
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['nama']            = $user['nama'] ?? 'Admin';
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Email atau password salah, atau akun bukan admin.';
                }
            } else {
                $error = 'Email atau password salah, atau akun bukan admin.';
            }
        } catch (PDOException $e) {
            $error = 'Email atau password salah, atau akun bukan admin.';
        }
    } else {
        $error = 'Harap isi semua kolom!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Florist - Masuk</title>
    <!-- Font Poppins & Cormorant Garamond -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            /* Latar belakang soft pink sesuai referensi */
            background-color: #f6ebeb; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 45px 35px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(235, 220, 220, 0.5);
        }
        .brand-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 700;
            color: #7b2c3b;
            text-align: center;
            margin-bottom: 2px;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #555;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 400;
        }
        .alert {
            background-color: #fdf2f2;
            color: #993333;
            border: 1px solid #f5c6cb;
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 12.5px;
            margin-bottom: 22px;
            line-height: 1.4;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #444;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e0d0d0;
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        .form-group input:focus {
            border-color: #7b2c3b;
            box-shadow: 0 0 0 3px rgba(123, 44, 59, 0.08);
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            /* Tombol dengan sentuhan gradasi marun */
            background: linear-gradient(135deg, #7b2c3b 0%, #63222f 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
            transition: opacity 0.2s ease;
            box-shadow: 0 4px 12px rgba(123, 44, 59, 0.2);
        }
        .btn-submit:hover {
            opacity: 0.92;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-title">Royal Florist</div>
        <div class="brand-subtitle">Masuk ke Dashboard Admin</div>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="" required>
            </div>

            <button type="submit" class="btn-submit">Masuk</button>
        </form>
    </div>

</body>
</html>
