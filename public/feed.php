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

// Lidar com o envio do formulário de postagem
if (isset($_POST['submit'])) {
    $description = $_POST['description'];

    // Gerenciar upload de imagem
    $target_dir = __DIR__ . "/uploads/";
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
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Tentar fazer o upload da imagem
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Inserir post no banco de dados
            $stmt = $conn->prepare("INSERT INTO posts (user_id, image_path, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, basename($_FILES["image"]["name"]), $description);
            $stmt->execute();
            $stmt->close();
            echo "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Consultar posts
$query = "SELECT posts.id, users.username, users.profile_picture, posts.image_path, posts.description, posts.created_at, posts.user_id, 
(SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
(SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count
FROM posts 
JOIN users ON posts.user_id = users.id 
ORDER BY posts.created_at DESC";
$result = $conn->query($query);

// Consultar usuários para sugestões
$suggested_users_query = "SELECT id, username, profile_picture FROM users ORDER BY RAND() LIMIT 5";
$suggested_users_result = $conn->query($suggested_users_query);

// Lidar com curtida
if (isset($_POST['like'])) {
    $post_id = $_POST['post_id'];
    
    // Verificar se a curtida já existe
    $check_like_query = "SELECT id FROM likes WHERE post_id = ? AND user_id = ?";
    $check_like_stmt = $conn->prepare($check_like_query);
    $check_like_stmt->bind_param("ii", $post_id, $user_id);
    $check_like_stmt->execute();
    $check_like_result = $check_like_stmt->get_result();

    if ($check_like_result->num_rows === 0) {
        // Se não existe, inserir nova curtida
        $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Curtida já existe, remover a curtida
        $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: feed.php'); // Redireciona para atualizar o feed
    exit;
}

// Lidar com comentário
if (isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $comment_text = $_POST['comment_text'];
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment_text);
    $stmt->execute();
    $stmt->close();
    header('Location: feed.php'); // Redireciona para atualizar o feed
    exit;
}

// Lidar com seguir
if (isset($_POST['follow'])) {
    $follow_user_id = $_POST['follow_user_id'];

    // Verificar se já está seguindo
    $check_follow_query = "SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?";
    $check_follow_stmt = $conn->prepare($check_follow_query);
    $check_follow_stmt->bind_param("ii", $user_id, $follow_user_id);
    $check_follow_stmt->execute();
    $check_follow_result = $check_follow_stmt->get_result();

    if ($check_follow_result->num_rows === 0) {
        // Se não está seguindo, adicionar o seguimento
        $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $follow_user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Se já está seguindo, remover o seguimento
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $user_id, $follow_user_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: feed.php'); // Redireciona para atualizar o feed
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        /* Estilos Globais */
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f5f5f5;
            padding: 20px;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 200%;
            max-width: 1900px;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
        }
        .header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
        }
        .header .search-box {
            position: relative;
            width: 300px;
        }
        .header .search-box input {
            width: 100%;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 0 40px 0 20px;
            font-size: 16px;
        }
        .header .search-box .search-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            font-size: 20px;
            color: #888;
        }
        .header form {
            display: flex;
            align-items: center;
        }
        .header button {
            background-color: #664AFF;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }

        /* Main Container */
        .main-container {
            display: flex;
            width: 100%;
            max-width: 1900px;
            margin-top: 70px; /* Adjusted for header height */
        }
        .posts-container, .suggestions-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 0 10px;
        }
        .posts-container {
            width: 80%;
        }
        .suggestions-container {
            width: 20%;
        }
        .post {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .post img {
            max-width: 100%;
            border-radius: 10px;
        }
        .post .post-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .post .post-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .post .post-info .username {
            font-weight: bold;
        }
        .post .post-info .timestamp {
            color: #888;
            font-size: 14px;
        }
        .post .post-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .post .post-actions button {
            border: none;
            background: none;
            cursor: pointer;
            color: #664AFF;
            font-size: 16px;
        }
        .post .post-actions .like-count, .post .post-actions .comment-count {
            color: #888;
        }

        /* Sugestões de Usuários */
        .suggestion {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .suggestion img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .suggestion .suggestion-info {
            flex-grow: 1;
        }
        .suggestion button {
            border: none;
            background-color: #664AFF;
            color: #fff;
            border-radius: 20px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .suggestion button:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }

        /* Formulários */
        .post-form, .comment-form {
            margin-bottom: 20px;
        }
        .post-form input, .post-form textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .post-form input[type="file"] {
            border: none;
            padding: 0;
        }
        .post-form button, .comment-form button {
            background-color: #664AFF;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .post-form button:hover, .comment-form button:hover {
            background-color: #5a3aef;
        }
    </style>
</head>
<body>
    <div class="header">
    <img src="profile_img/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" onclick="window.location.href='profile.php'">
    <div class="search-box">
        <form action="search.php" method="get">
            <input type="text" name="query" placeholder="Buscar..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </form>
    </div>
    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</div>


    <div class="main-container">
        <div class="posts-container">
            <form class="post-form" action="feed.php" method="post" enctype="multipart/form-data">
                <textarea name="description" placeholder="O que você está pensando?" required></textarea>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="submit">Postar</button>
            </form>

            <?php while ($post = $result->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-info">
                        <img src="profile_img/<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="Profile Picture">
                        <div>
                            <span class="username"><?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="timestamp"><?php echo htmlspecialchars($post['created_at']); ?></span>
                        </div>
                    </div>
                    <img src="uploads/<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image">
                    <p><?php echo htmlspecialchars($post['description']); ?></p>
                    <div class="post-actions">
                        <form action="feed.php" method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <button type="submit" name="like">
                                <i class="fas fa-heart"></i> 
                                <span class="like-count"><?php echo htmlspecialchars($post['like_count']); ?></span>
                            </button>
                        </form>
                        <span class="comment-count"><?php echo htmlspecialchars($post['comment_count']); ?> Comentários</span>
                    </div>

                    <!-- Comentários -->
                    <form class="comment-form" action="feed.php" method="post">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                        <input type="text" name="comment_text" placeholder="Adicionar um comentário...">
                        <button type="submit" name="comment">Comentar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="suggestions-container">
            <h2>Seguir Usuários</h2>
            <?php while ($suggested_user = $suggested_users_result->fetch_assoc()): ?>
                <div class="suggestion">
                    <img src="profile_img/<?php echo htmlspecialchars($suggested_user['profile_picture']); ?>" alt="Profile Picture">
                    <div class="suggestion-info">
                        <span><?php echo htmlspecialchars($suggested_user['username']); ?></span>
                    </div>
                    <form action="feed.php" method="post" style="display:inline;">
                        <input type="hidden" name="follow_user_id" value="<?php echo htmlspecialchars($suggested_user['id']); ?>">
                        <button type="submit" name="follow">
                            <?php
                            // Verificar se já está seguindo
                            $check_follow_query = "SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?";
                            $check_follow_stmt = $conn->prepare($check_follow_query);
                            $check_follow_stmt->bind_param("ii", $user_id, $suggested_user['id']);
                            $check_follow_stmt->execute();
                            $check_follow_result = $check_follow_stmt->get_result();
                            ?>
                            <?php echo ($check_follow_result->num_rows > 0) ? 'Seguindo' : 'Seguir'; ?>
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
