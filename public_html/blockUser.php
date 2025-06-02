<?php
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$userDB="sadkovaann";

$db_connect = mysqli_connect($host, $userDB, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

if (isset($_GET['user'])) {
    $user = $_GET['user'];
}
if (isset($_POST['user'])) {
    $user = $_POST['user'];
}
if(isset($_GET['BookId'])){
    $BookId = $_GET['BookId'];
}

if($user != null){
    $moderQ = "DELETE FROM readbook WHERE User = $user";
    $sqlModer = mysqli_query($db_connect, $moderQ);
    if ($sqlModer) {
        $subQ = "DELETE FROM subscription WHERE Subscriber = $user OR Blogger = $user";
        $sqlSub = mysqli_query($db_connect, $subQ);
        if ($sqlSub) {
            $blockUserQ = "UPDATE user SET Scores = 0, Block = true WHERE UserId = $user";
            $sqlBlock = mysqli_query($db_connect, $blockUserQ);
            if ($sqlBlock) {
                if($BookId){
                    header("Location: bookcard.php?id=$BookId");
                }
                else{
                    $newBlockStatus = true;
                    echo json_encode(['success' => true, 'BlockStatus' => $newBlockStatus]);
                    header("Location: allreview.php");
                }
            } 
            else {
                $error = mysqli_error($db_connect);
                echo "<script> alert(" . json_encode("Ошибка при блокировке пользователя") . "); window.history.back();</script>";
            }
        }
    } 
    else {
        $error = mysqli_error($db_connect);
        echo "<script> alert(" . json_encode("Ошибка при удалении рецензии") . "); window.history.back();</script>"; 
    }
}
?>