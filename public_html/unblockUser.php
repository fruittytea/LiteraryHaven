<?php
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

if (isset($_POST['user'])) {
    $user = $_POST['user'];
}

if($user != null){
    $blockUserQ = "UPDATE user SET Scores = 20, Block = false WHERE UserId = $user";
    $sqlBlock = mysqli_query($db_connect, $blockUserQ);
    if ($sqlBlock) {
        $newBlockStatus = false;
        echo json_encode(['success' => true, 'BlockStatus' => $newBlockStatus]);
    } 
    else {
        $error = mysqli_error($db_connect);
        echo "<script> alert(" . json_encode("Ошибка при разблокировке пользователя") . "); </script>";
    }
}
?>
