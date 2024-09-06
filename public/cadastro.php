<?php
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'social_media';

// Connect to database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Registration
if (isset($_POST['register'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $username = $_POST['username'];

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address';
  } else {
    // Check if email already exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
      $error = 'Email address already exists';
    } else {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      // Insert user into database
      $query = "INSERT INTO users (email, password, username) VALUES ('$email', '$hashed_password', '$username')";
      if ($conn->query($query) === TRUE) {
        $success = 'Registration successful!';
      } else {
        $error = 'Error registering user';
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <img src="https://images.pexels.com/photos/1907785/pexels-photo-1907785.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
    <div class="container">
        <div class="register-wrapper">
            <div class="register-header">
                <h2>Create Your Account</h2>
                <p>Fill in the details to register</p>
            </div>
            <form class="register-form" method="post">
                <div class="input-group">
                  <input type="email" id="email" name="email" required>
                  <label for="email">Email</label>
                </div>
                <div class="input-group">
                  <input type="password" id="password" name="password" required>
                  <label for="password">Password</label>
                </div>
                <div class="input-group">
                  <input type="text" id="username" name="username" required>
                  <label for="username">Username</label>
                </div>
                <button type="submit" class="register-btn" id="register-btn" name="register">Register</button>
                <a href="login.php">Já Possuí Conta? Entrar</a>
              </form>

    <script>
        // Função para alternar a visibilidade da senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        }
    </script>
</body>
</html>
