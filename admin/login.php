<?php
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        redirect("dashboard.php");

    } else {
        showAlert("danger", "Invalid username or password");
    }
}
?>

<?php include "../includes/header.php"; ?>

<div style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">

    <div style="max-width: 450px; width: 100%; padding: 20px;">

        <div style="background: white; border-radius: 14px; padding: 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08);">

            <!-- HEADER -->
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; margin: 0 auto 15px;">-</div>
                <h2 class="page-title" style="margin: 0;">Admin Portal</h2>
                <p style="color: #666; margin-top: 8px; font-size: 14px;">Secure access required</p>
            </div>

            <?php displayAlert(); ?>

            <form method="POST">

                <!-- USERNAME -->
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <!-- LOGIN BUTTON -->
                <button type="submit" class="btn btn-primary btn-main" style="background: #6f42c1; margin-top: 10px;">
                    Login
                </button>

            </form>

            <!-- BACK LINK -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="../index.php" style="color: #0d6efd; text-decoration: none; font-size: 14px;">
                    Back to Home
                </a>
            </div>

        </div>

    </div>

</div>

<?php include "../includes/footer.php"; ?>
