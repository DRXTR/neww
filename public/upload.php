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

// Lidar com o envio do formulário
if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $description = $_POST['description'];

    // Gerenciar upload de imagem
    $target_dir = "/uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;

    // Verificar se é uma imagem real
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Verificar se o arquivo já existe
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Permitir certos formatos de arquivo
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Tentar fazer o upload da imagem
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Inserir post no banco de dados
            $stmt = $conn->prepare("INSERT INTO posts (user_id, image_path, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $target_file, $description);
            $stmt->execute();
            $stmt->close();
            echo "The file ". htmlspecialchars(basename($_FILES["image"]["name"])). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Consultar posts para exibir no feed
$query = "SELECT users.username, posts.image_path, posts.description, posts.created_at 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          ORDER BY posts.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <style>
        .feed-container {
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }
        .post {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        .post img {
            max-width: 20%;
            max-height: 20%;
            display: block;
            margin: 0 auto;
        }
        .post .description {
            margin-top: 10px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="feed-container">
        <h2>Publicar Imagem</h2>
        <form action="feed.php" method="post" enctype="multipart/form-data">
            <input type="file" name="image" required>
            <textarea name="description" placeholder="Descrição" required></textarea>
            <button type="submit" name="submit">Publicar</button>
        </form>

        <h2>Feed de Publicações</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($row['username']); ?></strong> - <?php echo htmlspecialchars($row['created_at']); ?></p>
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Post Image">
                <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
// Fechar conexão
$conn->close();
?>
