
<?php
// Conectar ao banco de dados
$conn = mysqli_connect("localhost", "username", "password", "social_media");

// Verificar se a conexão foi bem-sucedida
if (!$conn) {
  die("Conexão falhou: " . mysqli_connect_error());
}

// Inserir um novo usuário
$username = $_POST["username"];
$password = $_POST["password"];
$email = $_POST["email"];

$query = "INSERT INTO usuarios (username, senha, email) VALUES ('$username', '$password', '$email')";
mysqli_query($conn, $query);

// Inserir um novo tweet
$texto = $_POST["texto"];
$id_usuario = $_SESSION["id_usuario"];

$query = "INSERT INTO tweets (texto, id_usuario) VALUES ('$texto', '$id_usuario')";
mysqli_query($conn, $query);

// Selecionar todos os tweets de um usuário
$id_usuario = $_SESSION["id_usuario"];

$query = "SELECT * FROM tweets WHERE id_usuario = '$id_usuario'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
  echo $row["texto"] . "<br>";
}

// Fechar a conexão
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Profile</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="inicio.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><span class="material-icons">home</span> Start</li>
                <li><span class="material-icons">explore</span> To explore</li>
                <li><span class="material-icons">notifications</span> Notifications</li>
                <li><span class="material-icons">message</span> Messages</li>
                <li><span class="material-icons">bookmark</span> Saved</li>
                <li><span class="material-icons">list</span> Lists</li>
                <li><span class="material-icons">person</span> Profile</li>
                <li><span class="material-icons">more_horiz</span> More options</li>
            </ul>
            <button class="tweet-btn">Tweet</button>
        </aside>

        <main class="main-content">
            <header class="profile-header">
                <div class="cover-photo"></div>
                <div class="profile-info">
                    <div class="profile-pic"></div>
                    <div class="profile-details">
                        <h2>George Davis</h2>
                        <p>@georgedavis</p>
                        <p>377 Following • 735 Thousand Followers</p>
                    </div>
                    <button class="follow-btn">Follow</button>
                </div>
                <nav class="profile-nav">
                    <ul>
                        <li>Tweets</li>
                        <li>Tweets and responses</li>
                        <li>Multimedia</li>
                        <li>I like</li>
                    </ul>
                </nav>
            </header>

            <section class="tweets">
                <div class="tweet">
                    <div class="tweet-profile-pic"></div>
                    <div class="tweet-content">
                        <p><strong>George Davis</strong> @georgedavis • 5h</p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        <div class="tweet-actions">
                            <span class="material-icons">chat_bubble_outline</span> 60
                            <span class="material-icons">repeat</span> 110
                            <span class="material-icons">favorite_border</span> 600
                        </div>
                    </div>
                </div>
                <!-- Repetir para mais tweets -->
            </section>
        </main>

        <aside class="right-sidebar">
            <section class="follow-suggestions">
                <h3>Follow</h3>
                <div class="suggestion">
                    <div class="suggestion-pic"></div>
                    <div class="suggestion-details">
                        <p>Sportsnews</p>
                        <p>@sportsnews</p>
                    </div>
                    <button class="follow-btn-small">Follow</button>
                </div>
                <div class="suggestion">
                    <div class="suggestion-pic"></div>
                    <div class="suggestion-details">
                        <p>Sarah Clayton</p>
                        <p>@sarahclayton</p>
                    </div>
                    <button class="follow-btn-small">Follow</button>
                </div>
                <div class="suggestion">
                    <div class="suggestion-pic"></div>
                    <div class="suggestion-details">
                        <p>Milton Brown</p>
                        <p>@miltonbrown</p>
                    </div>
                    <button class="follow-btn-small">Follow</button>
                </div>
                <button class="show-more">Show more</button>
            </section>

            <section class="whats-going-on">
                <h3>What's going on</h3>
                <div class="news">
                    <p>Lorem Ipsum</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
                <div class="news">
                    <p>Lorem Ipsum</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
                <div class="news">
                    <p>Lorem Ipsum</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
                <button class="show-more">Show more</button>
            </section>
        </aside>
    </div>
</body>
</html>
