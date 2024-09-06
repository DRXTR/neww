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
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Consultar post
    $query = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    // Verificar se o post existe
    if (!$post) {
        echo "Post não encontrado ou você não tem permissão para editá-lo.";
        exit;
    }

    // Atualizar descrição
    if (isset($_POST['update'])) {
        $new_description = $_POST['description'];
        $update_query = "UPDATE posts SET description = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_description, $post_id);
        $update_stmt->execute();
        $update_stmt->close();

        header('Location: feed.php');
        exit;
    }
} else {
    echo "ID do post não fornecido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicação</title>
</head>
<body>
    <h2>Editar Descrição da Publicação</h2>
    <form action="edit.php?id=<?php echo htmlspecialchars($post_id); ?>" method="post">
        <textarea name="description" required><?php echo htmlspecialchars($post['description']); ?></textarea>
        <button type="submit" name="update">Atualizar</button>
    </form>
</body>
</html>

<?php
// Fechar conexão
$conn->close();
?>
