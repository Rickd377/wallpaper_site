<?php
include './db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];

    // Check if the input is a username or email
    $sql = "SELECT * FROM wallo_users WHERE Username='$usernameOrEmail' OR Email='$usernameOrEmail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            session_start();
            $_SESSION['ID'] = $row['ID'];
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['is_admin'] = $row['is_admin'];

            // Debugging statements
            error_log("User ID: " . $_SESSION['ID']);
            error_log("Username: " . $_SESSION['Username']);
            error_log("Is Admin: " . $_SESSION['is_admin']);

            if ($row['is_admin']) {
                header("Location: ./admin.php");
            } else {
                header("Location: ../index.php#home-page");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username or email.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <?php include './components/fontawesome.php'; ?>
    <link rel="stylesheet" href="../styles/dist/css/style.css">
    <title>Login</title>
</head>
<body id="register-page">
  <a class="icon-back" href="../index.php#home-page"><i class="fa-light fa-arrow-left"></i></a>
  <form method="POST" action="login.php">
      <h1>Login</h1>
      <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>
      <div class="input-wrapper">
        <input type="text" id="username" name="username" autocomplete="off" required placeholder="Username or Email">
      </div>
      <div class="input-wrapper">
        <input type="password" id="password" class="password-input" name="password" required placeholder="Password">
        <i class="fa-light fa-eye-slash toggle-password"></i>
      </div>
      <button type="submit">Login</button>
      <p class="account-text">Don't have an account? <a href="./register.php">Register</a></p>
    </form>

    <script src="../js/password.js"></script>
</body>
</html>