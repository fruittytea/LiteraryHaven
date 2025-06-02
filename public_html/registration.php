<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<body class="autorisation-body">
    <header class="mini-header">
        <div class="logo">
            <img src="Images/Logo/logo.png" alt="Logo">
        </div>
    </header>
    <main>
    <div class="autorisation-form">
        <form action="" method="get">
            <h1>Регистрация</h1>
            <label for="SurnameInput">Фамилия</label><br>
            <input type="text" name="SurnameInput" id="SurnameInput" required placeholder="Иванов"><br>
            <label for="NameInput">Имя</label><br>
            <input type="text" name="NameInput" id="NameInput" required placeholder="Иван"><br>
            <label for="FathernameInput">Отчество</label><br>
            <input type="text" name="FathernameInput" id="FathernameInput" placeholder="Иванович"><br>
            <label for="NicknameInput">Имя пользователя</label><br>
            <input type="text" name="NicknameInput" id="NicknameInput" required placeholder="user"><br>
            <label for="PhoneInput">Телефон</label><br>
            <input type="text" name="PhoneInput" id="PhoneInput" required placeholder="+7 (900) 900 90 90"><br>
            <label for="EmailInput">Email</label><br>
            <input type="email" name="EmailInput" id="EmailInput" required placeholder="email@email.com"><br>
            <label for="PasswordInput">Пароль</label><br>
            <input type="password" name="PasswordInput" id="PasswordInput" required><br>
            <input type="submit" name="" id="autorisation-button" value="Зарегистрироваться">
            <br>
            <a href="autorisation.php">Есть аккаунт? Авторизуйтесь!</a>
        </form>
    </div>
    </main>
    <!--JS код для маски ввода-->
    <script>
    // Добавляем обработчик события, который срабатывает, когда весь HTML загружен и обработан
        window.addEventListener("DOMContentLoaded", function() {
            [].forEach.call( document.querySelectorAll('#PhoneInput'), function(input) {
                var keyCode;
                // Функция mask(event) для форматирования ввода телефонного номера
                function mask(event) {
                    event.keyCode && (keyCode = event.keyCode);
                    var pos = this.selectionStart;
                    if (pos < 3) event.preventDefault();
                    var matrix = "+7 (___) ___ __ __",
                        i = 0,
                        def = matrix.replace(/\D/g, ""),
                        val = this.value.replace(/\D/g, ""),
                        new_value = matrix.replace(/[_\d]/g, function(a) {
                            return i < val.length ? val.charAt(i++) : a
                        });
                    i = new_value.indexOf("_");
                    if (i != -1) {
                        i < 5 && (i = 3);
                        new_value = new_value.slice(0, i)
                    }
                    var reg = matrix.substr(0, this.value.length).replace(/_+/g,
                        function(a) {
                            return "\\d{1," + a.length + "}"
                        }).replace(/[+()]/g, "\\$&");
                    reg = new RegExp("^" + reg + "$");
                    if (!reg.test(this.value) || this.value.length < 5 || keyCode > 47 && keyCode < 58) {
                        this.value = new_value;
                    }
                    if (event.type == "blur" && this.value.length < 5) {
                        this.value = "";
                    }
                }
                input.addEventListener("input", mask, false);
                input.addEventListener("focus", mask, false);
                input.addEventListener("blur", mask, false);
                input.addEventListener("keydown", mask, false);

            });
        });   
    </script>
    <?php
    ob_start();
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['SurnameInput'], $_GET['NameInput'], 
        $_GET['PhoneInput'], $_GET['PasswordInput'], $_GET['EmailInput'], $_GET['NicknameInput'])) {

        $sn = $_GET['SurnameInput'] ?? '';
        $n = $_GET['NameInput'] ?? '';
        $f = $_GET['FathernameInput'] ?? '';
        $em = $_GET['EmailInput'] ?? '';
        $ph = $_GET['PhoneInput'] ?? '';
        $pas = $_GET['PasswordInput'] ?? '';
        $nick = $_GET['NicknameInput'] ?? '';

        if (empty($sn) || empty($n) || empty($ph) || empty($pas) || empty($em) || empty($nick)) {
            echo "<script>alert('Пожалуйста, заполните все поля');</script>";
        } else {
            // Проверка на уникальность номера телефона
            $check_phone_stmt = $db_connect->prepare("SELECT COUNT(*) FROM user WHERE Phone = ?");
            if ($check_phone_stmt) {
                $check_phone_stmt->bind_param("s", $ph);
                $check_phone_stmt->execute();
                $check_phone_stmt->bind_result($count_phone);
                $check_phone_stmt->fetch();
                $check_phone_stmt->close();
                if ($count_phone > 0) {
                    echo "<script>alert('Этот номер телефона уже зарегистрирован!');</script>";
                } else {
                    // Проверка на уникальность nickname
                    $check_nick_stmt = $db_connect->prepare("SELECT COUNT(*) FROM user WHERE Nickname = ?");
                    if ($check_nick_stmt) {
                        $check_nick_stmt->bind_param("s", $nick);
                        $check_nick_stmt->execute();
                        $check_nick_stmt->bind_result($count_nick);
                        $check_nick_stmt->fetch();
                        $check_nick_stmt->close();
                        if ($count_nick > 0) {
                            echo "<script>alert('Это имя пользователя уже занято!');</script>";
                        } else {
                            // Если номер телефона и nickname уникальны, продолжаем регистрацию
                            $stmt = $db_connect->prepare("INSERT INTO user (Surname, Name, Fathername, Phone, Password, Email, Scores, Role, Status, Nickname) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $status = 1;
                            $scores = 20;
                            $role = 2;
                            if ($stmt) {
                                $stmt->bind_param("ssssssiiis", $sn, $n, $f, $ph, $pas, $em, $scores, $role, $status, $nick);
                                if ($stmt->execute()) {
                                    $last_id = $db_connect->insert_id;
                                    $next_stmt = $db_connect->prepare("INSERT INTO awardsreceived (Award, UserId) VALUES (?, ?)");
                                    if ($next_stmt) {
                                        $award = 1;
                                        $next_stmt->bind_param("ii", $award, $last_id);
                                        if ($next_stmt->execute()) {
                                            ob_clean();
                                            header("Location: autorisation.php");
                                            exit();
                                        } else {
                                            echo "Ошибка при добавлении награды: " . $next_stmt->error;
                                        }
                                        $next_stmt->close();
                                    } else {
                                        echo "Ошибка подготовки запроса для награды: " . $db_connect->error;
                                    }
                                } else {
                                    echo "Ошибка при добавлении пользователя: " . $stmt->error;
                                }
                                $stmt->close();
                            } else {
                                echo "Ошибка подготовки запроса: " . $db_connect->error;
                            }
                        }
                    } else {
                        echo "Ошибка подготовки запроса для проверки имени пользователя: " . $db_connect->error;
                    }
                }
            } else {
                echo "Ошибка подготовки запроса для проверки номера телефона: " . $db_connect->error;
            }
        }
    }
    ob_end_flush();
    ?>
    <center>
        <footer style="display: flex; align-items: center; justify-content: center;">
            <div class="footer-content">
                <a href="#"><img src="Images/Logo/logo2.png" alt="Logo" class="footer-logo"></a>
                <p style="color:#455C86">Разработка дизайна:</p>
                <p>Садкова Анна Владимировна<br>sadkovaanna46@gmail.com</p>
                <p style="color:#455C86">По техническим вопросам:</p>
                <p>sadkovaanna46@gmail.com</p>
                <div class="social-icons">
                <a href="#"><img src="Images/Social/telegram.png" alt="Telegram"></a>
                <a href="#"><img src="Images/Social/vk.png" alt="VK"></a>
                <a href="#"><img src="Images/Social/email.png" alt="Email"></a>
                </div>
            </div>
        </footer>
    </center>
</body>
</html>