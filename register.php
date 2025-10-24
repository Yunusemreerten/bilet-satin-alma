<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'includes/db.php'; 
$error_message = ''; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

        if (empty($full_name) || empty($email) || empty($password)) {
        $error_message = "Lütfen tüm alanları doldurun.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Şifreler uyuşmuyor.";
    } else {
                $stmt = $db->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = "Bu e-posta adresi zaten kullanılıyor.";
        } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
                                    $user_id = uniqid('user_', true);

            $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            $result = $stmt->execute([$user_id, $full_name, $email, $hashed_password]);

            if ($result) {
                                                header("Location: login.php?message=register_success");
                exit;
            } else {
                $error_message = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Bilet Platformu</title>
    <link href="https:</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">OtobüsBilet</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Kayıt Ol</h3>
                    </div>
                    <div class="card-body">
                        <!-- Eğer bir hata mesajı varsa, burada gösteriyoruz -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Zaten bir hesabın var mı? <a href="login.php">Giriş Yap</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>