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

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'];

// Adicionar amizade
$stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $friend_id);
$stmt->execute();
$stmt->close();

header('Location: feed.php'); // Redireciona de volta para o feed
exit;
?>
