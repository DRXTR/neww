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

// Verificar se o ID do post foi passado
if (isset($_POST['id'])) {
    $post_id = $_POST['id'];

    // Verificar se o post pertence ao usuário
    $query = "SELECT user_id FROM posts WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    if ($post && $post['user_id'] == $_SESSION['user_id']) {
        // Excluir post
        $delete_query = "DELETE FROM posts WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $post_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Redirecionar para o feed
        header('Location: feed.php');
        exit;
    } else {
        echo "Post não encontrado ou você não tem permissão para excluí-lo.";
    }
} else {
    echo "ID do post não fornecido.";
    exit;
}
?>

<?php
// Fechar conexão
$conn->close();
?>
