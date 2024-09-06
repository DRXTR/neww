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

// Lidar com o envio do formulário de seguir
if (isset($_POST['following_id'])) {
    $follower_id = $_SESSION['user_id'];
    $following_id = intval($_POST['following_id']);

    // Verificar se o usuário a ser seguido realmente existe
    $user_check_query = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_check_query);
    $stmt->bind_param("i", $following_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo "Usuário não encontrado.";
        exit;
    }
    $stmt->close();

    // Verificar se já está seguindo
    $check_follow_query = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
    $stmt = $conn->prepare($check_follow_query);
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Se já está seguindo, remover o follow
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Caso contrário, adicionar o follow
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fechar conexão
$conn->close();

// Redirecionar de volta para o perfil do usuário
header("Location: eprofile.php?id=" . intval($_POST['following_id']));
exit;
