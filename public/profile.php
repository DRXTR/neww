<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Configurações do banco de dados
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'social_media';

// Conectar ao banco de dados
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Verificar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consultar informações do usuário
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Lidar com o envio do formulário de atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $username = $_POST['username'];
    
    // Gerenciar upload de imagem
    $profile_picture = $user['profile_picture'];
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = __DIR__ . "/profile_img/"; // Pasta separada para imagens de perfil
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Verificar se é uma imagem real
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            echo "<p>File is not an image.</p>";
            $uploadOk = 0;
        }

        // Permitir certos formatos de arquivo
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<p>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
            $uploadOk = 0;
        }

        // Tentar fazer o upload da imagem
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($_FILES["profile_picture"]["name"]); // Apenas o nome do arquivo
            } else {
                echo "<p>Sorry, there was an error uploading your file.</p>";
            }
        }
    }

    // Atualizar nome e foto de perfil no banco de dados
    $stmt = $conn->prepare("UPDATE users SET username = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $profile_picture, $user_id);
    $stmt->execute();
    $stmt->close();

    echo "<p>Profile updated successfully!</p>";
}

// Fechar conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <style>
        /* Estilos Globais */
        body {
            font-family: 'Roboto', sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
            color: #333;
            overflow-x: hidden;
        }

        /* Container Principal */
        .profile-container {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .profile-container::before {
            content: "";
            top: -10%;
            left: -10%;
            width: 120%;
            height: 120%;
            background: radial-gradient(circle at top left, rgba(0, 0, 0, 0.05), transparent);
            z-index: 1;
            filter: blur(15px);
        }

        .profile-header {
            text-align: center;
            position: relative;
            z-index: 1;
            margin-bottom: 30px;
        }

        .profile-header img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 6px solid #007bff;
            object-fit: cover;
            transition: transform 0.4s ease, border-color 0.4s ease;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .profile-header img:hover {
            transform: scale(1.1);
            border-color: #0056b3;
        }

        .profile-header h2 {
            margin-top: 15px;
            font-size: 32px;
            font-weight: 700;
            color: #333;
            transition: color 0.3s ease;
        }

        .profile-header h2:hover {
            color: #007bff;
        }

        /* Formulário de Edição */
        .profile-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1;
        }

        .profile-form label {
            font-size: 18px;
            margin-bottom: 10px;
            color: #555;
            text-align: left;
            width: 100%;
            max-width: 500px;
        }

        .profile-form input[type="text"],
        .profile-form input[type="file"] {
            width: 100%;
            max-width: 500px;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: #fafafa;
        }

        .profile-form input[type="text"]:focus,
        .profile-form input[type="file"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
            background: #fff;
        }

        .profile-form button {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .profile-form button:hover {
            background: linear-gradient(45deg, #0056b3, #007bff);
            transform: translateY(-2px);
        }

        .profile-form button:active {
            transform: translateY(1px);
        }

        /* Animação de Brilho no Botão */
        @keyframes glow {
            0% { box-shadow: 0 0 5px rgba(0, 123, 255, 0.6); }
            50% { box-shadow: 0 0 20px rgba(0, 123, 255, 0.6); }
            100% { box-shadow: 0 0 5px rgba(0, 123, 255, 0.6); }
        }

        .profile-form button:focus {
            animation: glow 1s infinite;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo 'profile_img/' . htmlspecialchars($user['profile_picture'] ?? 'default-profile.png'); ?>" alt="Profile Picture">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
        </div>
        <form class="profile-form" action="profile.php" method="post" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" name="profile_picture" id="profile_picture">
            <button type="submit" name="update">Update Profile</button>
        </form>
    </div>
</body>
</html>
