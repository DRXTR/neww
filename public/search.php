    <?php
    session_start();

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

    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT username, profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    $stmt->close();



    // Verificar conexão
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Recuperar a consulta de busca
    $query = $_GET['query'] ?? '';

    // Evitar SQL Injection
    $query = $conn->real_escape_string($query);

    // Consultar usuários com base na consulta
    $sql = "SELECT id, username, profile_picture FROM users WHERE username LIKE '%$query%'";
    $result = $conn->query($sql);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Busca de Usuários</title>
        <style>
            /* Reset básico */
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f0f2f5;
                color: #333;
                margin: 0;
                padding: 0;
            }

            .header button {
                background-color: #664AFF;
                color: #fff;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin-right: 20px;

             }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                border-bottom: 1px solid #ccc;
                background: #fff;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
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
                box-sizing: border-box;
            }

            .header .search-box .search-icon {
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                font-size: 20px;
                color: #888;
            }

            .search-suggestions {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1000;
                display: none;
            }

            .search-suggestions div {
                padding: 10px;
                cursor: pointer;
            }

            .search-suggestions div:hover {
                background: #f0f2f5;
            }

            .search-results {
                width: 80%;
                max-width: 1200px;
                margin: 80px auto 20px; /* Adjust for fixed header */
                padding: 20px;
                background: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .search-results h2 {
                font-size: 2em;
                margin-bottom: 20px;
                color: #1a1a1a;
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
            }

            .user-card {
                display: flex;
                align-items: center;
                padding: 15px;
                margin-bottom: 10px;
                background: #f9f9f9;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .user-card:hover {
                transform: scale(1.02);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .user-card img {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                object-fit: cover;
                margin-right: 15px;
                border: 2px solid #007bff;
                transition: border-color 0.2s;
            }

            .user-card img:hover {
                border-color: #0056b3;
            }

            .user-card a {
                text-decoration: none;
                color: #007bff;
                font-size: 1.2em;
                font-weight: 500;
                transition: color 0.2s;
            }

            .user-card a:hover {
                color: #0056b3;
            }

            .no-results {
                text-align: center;
                font-size: 1.2em;
                color: #666;
            }

            @media (max-width: 768px) {
                .search-results {
                    width: 90%;
                }

                .user-card {
                    flex-direction: column;
                    align-items: flex-start;
                    text-align: center;
                }

                .user-card img {
                    margin: 0 0 10px 0;
                }

                .user-card a {
                    font-size: 1em;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="profile_img/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" onclick="window.location.href='profile.php'">
            <div class="search-box">
                <form action="search.php" method="get">
                    <input type="text" name="query" id="search-input" placeholder="Buscar..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                    <div class="search-suggestions" id="suggestions-box"></div>
                </form>
            </div>
            <form action="logout.php" method="post">
                <button type="submit">Logout</button>
            </form>
        </div>

        <div class="search-results">
            <h2>Resultados da Busca</h2>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="user-card">
                        <img src="profile_img/<?php echo htmlspecialchars($row['profile_picture'] ?? 'default-profile.png'); ?>" alt="Profile Picture">
                        <a href="eprofile.php?id=<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['username']); ?></a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-results">Nenhum usuário encontrado.</p>
            <?php endif; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('search-input');
                const suggestionsBox = document.getElementById('suggestions-box');

                searchInput.addEventListener('input', function() {
                    const query = searchInput.value;

                    if (query.length > 1) { // Mostrar sugestões apenas se a consulta for maior que 1 caractere
                        fetch('autocomplete.php?query=' + encodeURIComponent(query))
                            .then(response => response.json())
                            .then(data => {
                                suggestionsBox.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(suggestion => {
                                        const div = document.createElement('div');
                                        div.textContent = suggestion;
                                        div.addEventListener('click', function() {
                                            searchInput.value = suggestion;
                                            suggestionsBox.innerHTML = '';
                                        });
                                        suggestionsBox.appendChild(div);
                                    });
                                    suggestionsBox.style.display = 'block';
                                } else {
                                    suggestionsBox.style.display = 'none';
                                }
                            });
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                });

                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
                        suggestionsBox.style.display = 'none';
                    }
                });
            });
        </script>
    </body>
    </html>


    <?php
    // Fechar conexão
    $conn->close();
    ?>
