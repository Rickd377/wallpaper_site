<?php
include './db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    $sql = "SELECT * FROM wallo_users WHERE Username='$username' OR Email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $error = "Username or email already in use.";
    } else {
        $sql = "SELECT ID FROM wallo_users ORDER BY ID";
        $result = $conn->query($sql);
        $lowestAvailableId = 1;

        if ($result->num_rows > 0) {
            $ids = [];
            while ($row = $result->fetch_assoc()) {
                $ids[] = $row['ID'];
            }
            for ($i = 1; $i <= max($ids) + 1; $i++) {
                if (!in_array($i, $ids)) {
                    $lowestAvailableId = $i;
                    break;
                }
            }
        }

        $sql = "INSERT INTO wallo_users (ID, Username, Password, Email) VALUES ('$lowestAvailableId', '$username', '$password', '$email')";

        if ($conn->query($sql) === TRUE) {
            header("Location: ./login.php");
            exit();
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
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
    <title>Register</title>
</head>
<body id="register-page">
  <a class="icon-back" href="../index.php#home-page"><i class="fa-light fa-arrow-left"></i></a>
  <form method="POST" action="register.php">
      <h1>Register</h1>
      <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>
      <div class="input-wrapper">
        <input type="text" id="username" name="username" autocomplete="off" required placeholder="Username">
      </div>
      <div class="input-wrapper">
        <input type="email" id="email" name="email" required placeholder="Email" autocomplete="off">
      </div>
      <div class="input-wrapper">
        <input type="password" id="password" class="password-input" name="password" required placeholder="Password">
        <i class="fa-light fa-eye-slash toggle-password"></i>
      </div>
      <div class="input-wrapper">
        <input type="password" id="confirm-password" class="password-input" name="confirm-password" required placeholder="Confirm Password">
        <i class="fa-light fa-eye-slash toggle-password"></i>
      </div>
      <div class="requirements">
        <p>Password must contain:</p>
        <ul>
          <li id="length">At least 8 characters</li>
          <li id="uppercase">At least one uppercase letter</li>
          <li id="number">At least one number</li>
          <li id="special">At least one special character</li>
          <li id="match">Passwords must match</li>
        </ul>
      </div>
      <button type="submit">Register</button>
      <p class="account-text">Already have an account? <a href="./login.php">Login</a></p>
    </form>

    <script src="../js/password.js"></script>
</body>
</html>