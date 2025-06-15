<?php
//Подключение к БД
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$userDB="sadkovaann";

$db_connect = mysqli_connect($host, $userDB, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

//Проверка на получение id пользователя через метод GET
if (isset($_GET['user'])) {
    $user = $_GET['user'];
}
//Проверка на получение id пользователя через метод POST
if (isset($_POST['user'])) {
    $user = $_POST['user'];
}
//Проверка на получение id книги
if(isset($_GET['BookId'])){
    $BookId = $_GET['BookId'];
}

if($user != null){
    //Удаление рецензий и прочитанных книг у блокируемого пользователя
    $moderQ = "DELETE FROM readbook WHERE User = $user";
    $sqlModer = mysqli_query($db_connect, $moderQ);
    if ($sqlModer) {
        //Удаление подписок и подписчиков у блокируемого пользователя
        $subQ = "DELETE FROM subscription WHERE Subscriber = $user OR Blogger = $user";
        $sqlSub = mysqli_query($db_connect, $subQ);
        if ($sqlSub) {
            //Обновление количества баллов и статуса блокировки у пользователя
            $blockUserQ = "UPDATE user SET Scores = 0, Block = true WHERE UserId = $user";
            $sqlBlock = mysqli_query($db_connect, $blockUserQ);
            if ($sqlBlock) {
                //Переход в карточку книги при наличии кода книги
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
