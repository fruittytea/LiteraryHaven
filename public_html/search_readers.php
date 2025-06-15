<?php
//Начало сессии
session_start();
if (isset($_SESSION['acc'])) {
    $acc = $_SESSION['acc'];
} elseif (isset($_GET['acc'])) {
    $acc = $_GET['acc'];
    $_SESSION['acc'] = $acc;
} else {
    header("Location: autorisation.php");
    exit();
}
//Подключение к БД
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}
//Проверка роли
$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
}
//Проверка получения запроса
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($db_connect, $_GET['query']);
    $currentUserId = isset($_SESSION['acc']) ? $_SESSION['acc'] : null; // Получаем ID текущего пользователя
    //Запрос на поиск пользователя по никнейму для администратора
    if($RoleId==1)
    {
        $q = "SELECT user.UserId,
                 user.UserPhoto, 
                 user.Nickname, 
                 status.StatusName, 
                 status.StatusPhoto, 
                 user.Block, 
                 COUNT(readbook.ReadId) as Books,
                 (SELECT COUNT(*) FROM subscription WHERE Blogger = user.UserId) AS Fans,  -- Подсчет подписчиков
                 (SELECT COUNT(*) FROM subscription WHERE Subscriber = $currentUserId AND Blogger = user.UserId) AS IsSubscribed
          FROM user 
          JOIN status ON status.StatusId = user.Status 
          LEFT JOIN readbook ON readbook.User = user.UserId 
          WHERE user.Nickname LIKE '%$query%' and Role = 2
          GROUP BY user.UserPhoto, 
                   user.Nickname, 
                   status.StatusName, 
                   status.StatusPhoto 
          ORDER BY user.Scores DESC;";
    }
    //Запрос на поиск пользователя по никнейму для читателя
    else
    {
        $q = "SELECT user.UserId,
                 user.UserPhoto, 
                 user.Nickname, 
                 status.StatusName, 
                 status.StatusPhoto, 
                 COUNT(readbook.ReadId) as Books,
                 (SELECT COUNT(*) FROM subscription WHERE Blogger = user.UserId) AS Fans,  -- Подсчет подписчиков
                 (SELECT COUNT(*) FROM subscription WHERE Subscriber = $currentUserId AND Blogger = user.UserId) AS IsSubscribed
          FROM user 
          JOIN status ON status.StatusId = user.Status 
          LEFT JOIN readbook ON readbook.User = user.UserId 
          WHERE user.Nickname LIKE '%$query%' and Role = 2 and Block = false
          GROUP BY user.UserPhoto, 
                   user.Nickname, 
                   status.StatusName, 
                   status.StatusPhoto 
          ORDER BY user.Scores DESC;";
    }
    
    $sql = mysqli_query($db_connect, $q);
    $results = [];
    
    if ($sql) {
        //Проверка подписок и блокировок
        while ($row = mysqli_fetch_assoc($sql)) {
            if($RoleId == 1){
                $row['IsBlocked'] = $row['Block'] == 1;
            }
            $row['IsSubscribed'] = $row['IsSubscribed'] > 0;
            $results[] = $row;
        }
    }
    
    echo json_encode($results);
}
?>
