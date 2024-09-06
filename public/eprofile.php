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

// Recuperar o ID do perfil
$profile_id = $_GET['id'] ?? 0;

// Recuperar informações do perfil
$profile_query = "SELECT username, profile_picture FROM users WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $profile_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
$profile_stmt->close();

// Consultar seguidores e seguidos
$followers_query = "SELECT COUNT(*) AS count FROM followers WHERE followed_id = ?";
$followers_stmt = $conn->prepare($followers_query);
$followers_stmt->bind_param("i", $profile_id);
$followers_stmt->execute();
$followers_result = $followers_stmt->get_result();
$followers_count = $followers_result->fetch_assoc()['count'];
$followers_stmt->close();

$following_query = "SELECT COUNT(*) AS count FROM followers WHERE follower_id = ?";
$following_stmt = $conn->prepare($following_query);
$following_stmt->bind_param("i", $profile_id);
$following_stmt->execute();
$following_result = $following_stmt->get_result();
$following_count = $following_result->fetch_assoc()['count'];
$following_stmt->close();

// Verificar se está seguindo
$check_follow_query = "SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?";
$check_follow_stmt = $conn->prepare($check_follow_query);
$check_follow_stmt->bind_param("ii", $_SESSION['user_id'], $profile_id);
$check_follow_stmt->execute();
$check_follow_result = $check_follow_stmt->get_result();
$is_following = $check_follow_result->num_rows > 0;
$check_follow_stmt->close();

// Lidar com seguir/Deixar de seguir
if (isset($_POST['follow'])) {
    if ($is_following) {
        // Se já está seguindo, remover o seguimento
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $profile_id);
    } else {
        // Se não está seguindo, adicionar o seguimento
        $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $_SESSION['user_id'], $profile_id);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: eprofile.php?id=' . $profile_id); // Redireciona para atualizar a página
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <style>
        /* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: #f4f4f4;
    color: #333;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-header {
    display: flex;
    align-items: center;
    border-bottom: 2px solid #eee;
    padding-bottom: 20px;
    width: 100%;
}

.profile-header img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 20px;
    border: 4px solid #3498db;
    transition: transform 0.3s ease;
}

.profile-header img:hover {
    transform: scale(1.05);
}

.profile-header h1 {
    font-size: 2rem;
    color: #3498db;
}

.profile-header p {
    font-size: 1.2rem;
    color: #555;
    margin: 5px 0;
}

.follow-button {
    background: #3498db;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
}

.follow-button:hover {
    background: #2980b9;
    transform: scale(1.05);
}

.follow-button:focus {
    outline: none;
}

.stats {
    display: flex;
    justify-content: space-around;
    width: 100%;
    padding: 20px 0;
    border-bottom: 2px solid #eee;
    margin-bottom: 20px;
}

.stats div {
    text-align: center;
}

.stats div p {
    font-size: 1.2rem;
    color: #3498db;
    font-weight: bold;
}

.stats div span {
    font-size: 1rem;
    color: #777;
}

@media (max-width: 768px) {
    .container {
        padding: 10px;
    }

    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .profile-header img {
        margin-bottom: 10px;
    }

    .stats {
        flex-direction: column;
        align-items: center;
    }

    .stats div {
        margin-bottom: 10px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <img src="profile_img/<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture">
            <div>
                <h1><?php echo htmlspecialchars($profile['username']); ?></h1>
                <p>Seguidores: <?php echo htmlspecialchars($followers_count); ?></p>
                <p>Seguindo: <?php echo htmlspecialchars($following_count); ?></p>
                <form action="eprofile.php?id=<?php echo htmlspecialchars($profile_id); ?>" method="post">
                    <button type="submit" name="follow" class="follow-button">
                        <?php echo $is_following ? 'Deixar de seguir' : 'Seguir'; ?>
                    </button>
                </form>
            </div>
        </div>
        <div class="stats">
            <div>
                <p><?php echo htmlspecialchars($followers_count); ?></p>
                <span>Seguidores</span>
            </div>
            <div>
                <p><?php echo htmlspecialchars($following_count); ?></p>
                <span>Seguindo</span>
            </div>
        </div>
    </div>
</body>
</html>


<?php
// Fechar conexão
$conn->close();
?>
