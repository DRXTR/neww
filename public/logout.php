<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se você estiver usando cookies para a sessão, delete-os
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login ou página inicial
header("Location: login.php"); // Altere para o caminho desejado
exit();
?>
