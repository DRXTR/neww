<?php
session_start();

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

// Recuperar a consulta de busca
$query = $_GET['query'] ?? '';

// Evitar SQL Injection
$query = $conn->real_escape_string($query);

// Consultar usuários com base na consulta
$sql = "SELECT username FROM users WHERE username LIKE '%$query%' LIMIT 10";
$result = $conn->query($sql);

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['username'];
}

// Retornar sugestões como JSON
header('Content-Type: application/json');
echo json_encode($suggestions);

$conn->close();
?>
