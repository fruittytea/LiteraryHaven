<?php
session_start();
if (!isset($_SESSION['acc'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit();
}

//Функция обновления награды
function updAward($subscriberId) {
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    //Подключение к БД
    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if (!$db_connect) {
        die("Ошибка подключения: " . mysqli_connect_error());
    }

    //Запрос на проверку наличия выданной награды
    $award = 16;
    $queryCheckAward = "SELECT * FROM awardsreceived WHERE UserId = ? AND Award = ?";
    $stmtCheckAward = mysqli_prepare($db_connect, $queryCheckAward);
    mysqli_stmt_bind_param($stmtCheckAward, 'ii', $subscriberId, $award);
    if ($stmtCheckAward) {
        if (mysqli_stmt_execute($stmtCheckAward)) {
            mysqli_stmt_store_result($stmtCheckAward); 
            if (mysqli_stmt_num_rows($stmtCheckAward) == 0) {
                //Запрос для проверки подписки
                $queryCheck = "SELECT * FROM subscription WHERE Subscriber = ?";
                $stmtCheck = mysqli_prepare($db_connect, $queryCheck);
                mysqli_stmt_bind_param($stmtCheck, 'i', $subscriberId);
                if ($stmtCheck) {
                    if (mysqli_stmt_execute($stmtCheck)) {
                        mysqli_stmt_store_result($stmtCheck); 
                        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
                            
                            $queryAdd = "INSERT INTO awardsreceived (Award, UserId) VALUES (?, ?)";
                            $stmtAdd = mysqli_prepare($db_connect, $queryAdd);
                            if ($stmtAdd) {
                                mysqli_stmt_bind_param($stmtAdd, 'ii', $award, $subscriberId);
                                if (mysqli_stmt_execute($stmtAdd)) {
                                    
                                    $queryUpd = "UPDATE user SET Scores = Scores + 5 WHERE UserId = ?";
                                    $stmtUpd = mysqli_prepare($db_connect, $queryUpd);
                                    if ($stmtUpd) {
                                        mysqli_stmt_bind_param($stmtUpd, 'i', $subscriberId);
                                        if (mysqli_stmt_execute($stmtUpd)) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    } else {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        } else {
                            return true;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            else{
                return true;
            }
        }
        else {
            return false;
        }
    }
    else{
        return false;
    }
    
}

//Получение переменной
$subscriberId = $_SESSION['acc'];

if (isset($_POST['userId'])) {
    $bloggerId = $_POST['userId'];
} elseif (isset($_POST['otherUser '])) {
    $bloggerId = $_POST['otherUser '];
} else {
    echo json_encode(['success' => false, 'message' => 'Не указан блогер']);
    exit();
}

//Подключение к БД
$host = "localhost";
$dbname = "sadkovaann";
$password = "R2UJCEw@Q";
$user = "sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if (!$db_connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

// Проверка, существует ли уже подписка
$checkQuery = "SELECT COUNT(*) as count FROM subscription WHERE Subscriber = ? AND Blogger = ?";
$checkStmt = mysqli_prepare($db_connect, $checkQuery);
mysqli_stmt_bind_param($checkStmt, 'ii', $subscriberId, $bloggerId);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);
$row = mysqli_fetch_assoc($result);

if ($row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Вы уже подписаны на этого блогера']);
    exit();
}

//Вставка новой подписки
$query = "INSERT INTO subscription (Subscriber, Blogger) VALUES (?, ?)";
$stmt = mysqli_prepare($db_connect, $query);
mysqli_stmt_bind_param($stmt, 'ii', $subscriberId, $bloggerId);

if (mysqli_stmt_execute($stmt)) {
    //Получение количества подписчиков
    $countQuery = "SELECT COUNT(*) as Fans FROM subscription WHERE Blogger = ?";
    $countStmt = mysqli_prepare($db_connect, $countQuery);
    mysqli_stmt_bind_param($countStmt, 'i', $bloggerId);
    mysqli_stmt_execute($countStmt);
    $result = mysqli_stmt_get_result($countStmt);
    $row = mysqli_fetch_assoc($result);
    
    //Вызов функции для обновления награды
    $awardSuccess = updAward($subscriberId);
    
    if (!$awardSuccess) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении награды']);
        exit();
    }

    echo json_encode(['success' => true, 'newFansCount' => $row['Fans']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при подписке: ' . mysqli_error($db_connect)]);
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($checkStmt);
mysqli_close($db_connect);
?>