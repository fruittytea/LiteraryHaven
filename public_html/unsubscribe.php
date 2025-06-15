<?php
//Начало сессии
session_start();
if (!isset($_SESSION['acc'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit();
}

$subscriberId = $_SESSION['acc'];
if (isset($_POST['userId'])) {
    $bloggerId = $_POST['userId'];
}
if(isset($_POST['otherUser'])){
    $bloggerId = $_POST['otherUser'];
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
//Запрос на удаление подписки
$query = "DELETE FROM subscription WHERE Subscriber = ? AND Blogger = ?";
$stmt = mysqli_prepare($db_connect, $query);
mysqli_stmt_bind_param($stmt, 'ii', $subscriberId, $bloggerId);

if (mysqli_stmt_execute($stmt)) {
    //Получение нового количества подписчиков
    $countQuery = "SELECT COUNT(*) as Fans FROM subscription WHERE Blogger = ?";
    $countStmt = mysqli_prepare($db_connect, $countQuery);
    mysqli_stmt_bind_param($countStmt, 'i', $bloggerId);
    mysqli_stmt_execute($countStmt);
    $result = mysqli_stmt_get_result($countStmt);
    $row = mysqli_fetch_assoc($result);
    
    echo json_encode(['success' => true, 'newFansCount' => $row['Fans']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при отписке']);
}

mysqli_stmt_close($stmt);
mysqli_close($db_connect);
?>
