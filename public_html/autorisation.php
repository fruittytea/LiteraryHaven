<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //Подключение к БД
    $host="localhost";
    $dbname="sadkovaann";
    $password="R2UJCEw@Q";
    $user="sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if(!$db_connect){
        die("Ошибка подключения" . mysqli_connect_error());
        echo "<script> alert('Ошибка подключения " . mysqli_connect_error() . "');</script>";
    }
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['LoginInput'], $_POST['PasswordInput'])) {
        $l = $_POST['LoginInput'] ?? '';
        $p = $_POST['PasswordInput'] ?? '';
        
        //Проверка полученных значений на пустоту
        if (empty($l) || empty($p)) {
            echo "<script> alert('Пожалуйста, введите логин и пароль'); </script>";
        } else {
            //Запрос на получение данных авторизующегося пользователя
            $stmt = $db_connect->prepare("SELECT UserId, Role, Block FROM user WHERE Phone = ? AND Password = ?");
            if (!$stmt) {
                echo "Ошибка подготовки запроса: " . mysqli_error($db_connect);
                exit();
            }
            $stmt->bind_param("ss", $l, $p);
            $stmt->execute();
            $result = $stmt->get_result();
            
            //Проверка выполнения запроса
            if ($result) {
                if ($acc = $result->fetch_assoc()) {
                    //Проверка блокировки у пользователя
                    if($acc['Block'] == 0){
                        //Проверка роли пользователя
                        if($acc['Role'] == 1){
                            header("Location: adminhome.php?acc={$acc['UserId']}");
                            exit();
                        }
                        else if($acc['Role'] == 2){
                            $us = $acc['UserId'];
                            $checkaward = "SELECT * FROM awardsreceived WHERE Award = 15 AND UserId = '$us'";
                            $result = mysqli_query($db_connect, $checkaward);
                            if ($result) {
                                if (mysqli_num_rows($result) <= 0) {
                                    $functionResult = bloggerFunc($acc['UserId']);
                                }
                                $funcStatus = editStatus($us);
                                header("Location: index.php?acc={$us}");
                                exit();
                            }
                        }
                    }
                    else{
                        echo "<script> alert('Ваш аккаунт был заблокирован!'); window.history.back();</script>";
                    }
                } 
                else {
                    echo "<script> alert('Неверно введен логин или пароль'); window.history.back();</script>";
                }
            } else {
                echo "<script> alert('Ошибка выполнения запроса! Пожалуйста, попробуйте ещё раз!'); window.history.back();</script>";
            }
            $stmt->close();
        }
    }
    //Функция выдачи награды "Литературный блогер"
    function bloggerFunc($userId) {
        $host="localhost";
        $dbname="sadkovaann";
        $password="R2UJCEw@Q";
        $user="sadkovaann";
    
        $db_connect = mysqli_connect($host, $user, $password, $dbname);
        if(!$db_connect){
            die("Ошибка подключения" . mysqli_connect_error());
            echo "<script> alert('Ошибка подключения " . mysqli_connect_error() . "');</script>";
        }
        
        $checkfan = "SELECT * FROM subscription WHERE Blogger = $userId";
        $result = mysqli_query($db_connect, $checkfan);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $addAward = "INSERT INTO awardsreceived(Award, UserId) VALUES ('15','$userId')";
                $r = mysqli_query($db_connect, $addAward);
                if ($r) {
                    $addScore = "UPDATE user SET Scores = Scores + 15 WHERE UserId = $userId";
                    $rr = mysqli_query($db_connect, $addScore);
                    if ($rr) {
                        mysqli_close($db_connect);
                        return true;
                    }
                    else{
                        mysqli_close($db_connect);
                        return false; 
                    }
                }
                else{
                    mysqli_close($db_connect);
                    return false;
                }
            }
            else {
                mysqli_close($db_connect);
                return false;
            }
        }
        else{
            mysqli_close($db_connect);
            return false;
        }
    }

    //Функция изменения статуса пользователя
    function editStatus($userId) {
        $host="localhost";
        $dbname="sadkovaann";
        $password="R2UJCEw@Q";
        $user="sadkovaann";
    
        $db_connect = mysqli_connect($host, $user, $password, $dbname);
        if(!$db_connect){
            die("Ошибка подключения" . mysqli_connect_error());
        }
        
        $checkStatus = "UPDATE user u
                    JOIN status s ON u.Scores >= s.MinScores AND u.Scores <= s.MaxScores
                    SET u.Status = s.StatusId
                    WHERE u.UserId = $userId;";
        $result = mysqli_query($db_connect, $checkStatus);
        if ($result) {
            mysqli_close($db_connect);
            return true;
        }
        else{
            mysqli_close($db_connect);
            return false;
        }
    }
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Авторизация</title>
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
        <!--Форма авторизации-->
        <form method="POST" action="">
            <h1>Авторизация</h1>
            <label for="LoginInput">Номер телефона</label><br>
            <input type="text" required  name="LoginInput" id="LoginInput" placeholder="+7 (900) 900 90 90"><br>
            <label for="PasswordInput">Пароль</label><br>
            <input type="password" required name="PasswordInput" id="PasswordInput"><br>
            <input type="submit" name="autorisation-button" id="autorisation-button" value="Войти">
            <br>
            <a href="registration.html">Нет аккаунта? Зарегистрируйся!</a>
        </form>
    </div>
    </main>
    <!--JS код для маски ввода-->
    <script>
        //Выполнение функции при загрузке страницы
        window.addEventListener("DOMContentLoaded", function() {
            //Итерация с элементом с id=LoginInput
            [].forEach.call(document.querySelectorAll('#LoginInput'), function(input) {
                var keyCode; //Хранениякода нажатой клавиши
                
                //Функция для маскирования ввода
                function mask(event) {
                    event.keyCode && (keyCode = event.keyCode);
                    var pos = this.selectionStart; //Получение позиции курсора в поле ввода
                    
                    //Запрет ввода, если курсор находится в первых двух позициях
                    if (pos < 3) event.preventDefault();
                    
                    // Шаблон для маски ввода
                    var matrix = "+7 (___) ___ __ __",
                        i = 0,
                        def = matrix.replace(/\D/g, ""),
                        val = this.value.replace(/\D/g, ""),
                        new_value = matrix.replace(/[_\d]/g, function(a) {
                            // Заполняем маску введенными значениями
                            return i < val.length ? val.charAt(i++) : a;
                        });
                    
                    i = new_value.indexOf("_");
                    if (i != -1) {
                        i < 5 && (i = 3);
                        new_value = new_value.slice(0, i);
                    }
                    
                    //Регулярное выражение для проверки введенного значения
                    var reg = matrix.substr(0, this.value.length).replace(/_+/g,
                        function(a) {
                            return "\\d{1," + a.length + "}";
                        }).replace(/[+()]/g, "\\$&");
                    reg = new RegExp("^" + reg + "$"); //Регулярное выражение для проверки формата
                    
                    //Обновление значения, если оно не соответствует маске или слишком короткое
                    if (!reg.test(this.value) || this.value.length < 5 || keyCode > 47 && keyCode < 58) {
                        this.value = new_value;
                    }
                    
                    if (event.type == "blur" && this.value.length < 5) {
                        this.value = "";
                    }
                }
                
                //Обработчики событий для ввода, фокуса, потери фокуса и нажатия клавиш
                input.addEventListener("input", mask, false);
                input.addEventListener("focus", mask, false);
                input.addEventListener("blur", mask, false);
                input.addEventListener("keydown", mask, false);
            });
        });   
    </script>
    <center>
        <footer style="display: flex; align-items: center; justify-content: center;">
            <div class="footer-content">
                <a href="#"><img src="Images/Logo/logo2.png" alt="Logo" class="footer-logo"></a>
                <p style="color:#455C86">Разработка веб-приложения:</p>
                <p>Садкова Анна Владимировна</p>
                <p style="color:#455C86">По техническим вопросам:</p>
                <a href='mailto:sadkovaanna46@gmail.com?subject=Сайт%20LiteraryHaven'><p>sadkovaanna46@gmail.com</p></a>
                <div class="social-icons">
                <a href="#"><img src="Images/Social/telegram.png" alt="Telegram"></a>
                <a href="#"><img src="Images/Social/vk.png" alt="VK"></a>
                <a href="mailto:sadkovaanna46@gmail.com?subject=Сайт%20LiteraryHaven"><img src="Images/Social/email.png" alt="Email"></a>
                </div>
            </div>
        </footer>
    </center>
</body>
</html>