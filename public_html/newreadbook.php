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
$host = "localhost";
$dbname = "sadkovaann";
$password = "R2UJCEw@Q";
$user = "sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if (!$db_connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}
//Проверка роли
$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
}
//Получение данных о книге
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bname = mysqli_real_escape_string($db_connect, $_POST['user-bname-box']);
    $aname = mysqli_real_escape_string($db_connect, $_POST['user-aname-box']);
    $bdescr = mysqli_real_escape_string($db_connect, $_POST['book-descr-tb']);
    $bgenre = mysqli_real_escape_string($db_connect, $_POST['sel-genre']);
    //Проверка на отметку об изменении и получение кода книги
    if(isset($_POST['edit']) && isset($_POST['BookId'])) {
        $Edit = $_POST['edit'];
        $EditBookId = $_POST['BookId'];
    }
    //При роди администратора
    if($RoleId == 1){
        $moder = true;
        $path = "library.php";
        if($Edit){
            $mess = "Информация о книге была успешно изменена! Вы можете найти её в библиотеке!";
        }
        else{
            $mess = "Книга успешно добавлена! Вы можете найти её в библиотеке!";
        }
    }
    else {
        $moder = false;
        $path = "library.php?mylib=1";
        $mess = "Книга отправлена на модерацию! После проверки вы сможете найти её в библиотеке!";
    }
    //Действия при изменении
    if($Edit){
        //Запрос на обновлении данных о книге
        $query = "UPDATE book SET BookName = '$bname', Author ='$aname', Genre = '$bgenre', BookDescription = '$bdescr' WHERE BookId = $EditBookId";
        if (mysqli_query($db_connect, $query)) {
            //Файл обложки
            if (isset($_FILES['book-img-tb']) && $_FILES['book-img-tb']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['book-img-tb']['tmp_name'];
                $fileName = $_FILES['book-img-tb']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                //Типы файлов
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = $EditBookId . '.' . $fileExtension;
                    //Папка для хранения
                    $uploadFileDir = 'Images/Books/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        //Обновление обложки
                        $updateQuery = "UPDATE book SET BookImage = '$newFileName' WHERE BookId = $EditBookId";
                        mysqli_query($db_connect, $updateQuery);
                        echo "<script>alert('".$mess."');
                        window.location.href = '".$path."';</script>";
                        exit();
                    } else {
                        echo "<script> alert('Ошибка при смене обложки');
                        window.location.href = '".$path."';</script>";
                        exit();
                    }
                } else {
                    echo "<script> alert('Ошибка при смене обложки');
                    window.location.href = '".$path."';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('".$mess."');
                    window.location.href = '".$path."';</script>";
            }
        } else {
            echo "<script> alert('Ошибка при обновлении сведений о книге'); window.history.back();</script>";

        }
    }
    else{
        //Запрос на добавление
        $query = "INSERT INTO book (BookName, Author, BookDescription, Genre, ModerationPassed) VALUES ('$bname', '$aname', '$bdescr', '$bgenre', '$moder')";
        if (mysqli_query($db_connect, $query)) {
            //Код добавленной книги
            $bookId = mysqli_insert_id($db_connect);

            //Файл обложки
            if (isset($_FILES['book-img-tb']) && $_FILES['book-img-tb']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['book-img-tb']['tmp_name'];
                $fileName = $_FILES['book-img-tb']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                //Типы файлов
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = $bookId . '.' . $fileExtension;
                    //Папка для хранения
                    $uploadFileDir = 'Images/Books/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        //Обновление обложки добавленной книги
                        $updateQuery = "UPDATE book SET BookImage = '$newFileName' WHERE BookId = $bookId";
                        mysqli_query($db_connect, $updateQuery);
                        echo "<script>alert('".$mess."');
                        window.location.href = '".$path."';</script>";
                        exit();
                    } else {
                        echo "<script> alert('Ошибка при добавлении обложки');
                        window.location.href = '".$path."';</script>";
                        exit();
                    }
                } else {
                    echo "<script> alert('Ошибка при добавлении обложки');
                    window.location.href = '".$path."';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('".$mess."');
                    window.location.href = '".$path."';</script>";
            }
        } else {
            echo "<script> alert('Ошибка при добавлении книги'); window.history.back();</script>";

        }
    }
} else {
    echo "<script> alert('Ошибка при добавлении книги'); window.history.back();</script>";
}

mysqli_close($db_connect);
?>
