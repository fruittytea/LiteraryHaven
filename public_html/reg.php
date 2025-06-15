<?php
    ob_start();
    //Подключение к БД
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    //Получение данных из формы
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
            //Проверка на уникальность номера телефона
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
                    //Проверка на уникальность ника
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
                            //Если номер телефона ник уникальны, то пользователь регистрируется
                            $stmt = $db_connect->prepare("INSERT INTO user (Surname, Name, Fathername, Phone, Password, Email, Scores, Role, Status, Nickname, UserPhoto) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $status = 1;
                            $scores = 20;
                            $role = 2;
                            $usphoto = "NoPhoto.png";
                            if ($stmt) {
                                $stmt->bind_param("ssssssiiiss", $sn, $n, $f, $ph, $pas, $em, $scores, $role, $status, $nick, $usphoto);
                                if ($stmt->execute()) {
                                    $last_id = $db_connect->insert_id;
                                    $next_stmt = $db_connect->prepare("INSERT INTO awardsreceived (Award, UserId) VALUES (?, ?)");
                                    if ($next_stmt) {
                                        $award = 1;
                                        $next_stmt->bind_param("ii", $award, $last_id);
                                        if ($next_stmt->execute()) {
                                            ob_clean();
                                            header("Location: index.php?acc=acc{$last_id}");
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
