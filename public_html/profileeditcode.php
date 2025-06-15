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
//Получение данных из формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sname = mysqli_real_escape_string($db_connect, $_POST['user-sname-box']);
    $name = mysqli_real_escape_string($db_connect, $_POST['user-name-box']);
    $fname = mysqli_real_escape_string($db_connect, $_POST['user-fname-box']);
    $mail = mysqli_real_escape_string($db_connect, $_POST['user-mail-box']);
    $pass = mysqli_real_escape_string($db_connect, $_POST['user-pass-box']);
    //Добавление нового администратора
    if($RoleId == 1){
        $ph= mysqli_real_escape_string($db_connect, $_POST['user-ph-box']);
        $query = "INSERT INTO user (Surname, Name, Fathername, Nickname, Phone, Password, Email, Role, Status, Block) VALUES ('$sname','$name','$fname','admin','$ph','$pass','$mail','1','1', false)";
        if (mysqli_query($db_connect, $query)) {
            header("Location: adminhome.php");
            exit();
        } 
        else {
            echo "<script> alert 'Ошибка при обновлении данных'; window.history.back();</script>";

        }
    }
    //Изменение личной информации
    else if($RoleId == 2){
        $query = "UPDATE user SET Surname ='$sname', Name ='$name', Fathername ='$fname', Password ='$pass', Email ='$mail' WHERE UserId = $acc";
        if (mysqli_query($db_connect, $query)) {

            // Файл аватарки
            if (isset($_FILES['us-img-tb']) && $_FILES['us-img-tb']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['us-img-tb']['tmp_name'];
                $fileName = $_FILES['us-img-tb']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                //Типы файлов изображений
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    //Переименование
                    $newFileName = $acc . '.' . $fileExtension;
                    //Папка для хранения изображений
                    $uploadFileDir = 'Images/Profile/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        //Обновление фотографии профиля
                        $updateQuery = "UPDATE user SET UserPhoto = '$newFileName' WHERE UserId = $acc";
                        mysqli_query($db_connect, $updateQuery);
                        header("Location: profile.php");
                        exit();
                    } else {
                        echo "<script> alert 'Ошибка при обновлении фотографии';</script>";
                        header("Location: profile.php");
                        exit();
                    }
                } else {
                    echo "<script> alert 'Ошибка при обновлении фотографии';</script>";
                    header("Location: profile.php");
                    exit();
                }
            } else {
                header("Location: profile.php");
                exit();
            }
        } else {
            echo "<script> alert 'Ошибка при обновлении данных'; window.history.back();</script>";

        }
    }
} 
else {
    echo "<script> alert 'Ошибка при получении данных'; window.history.back();</script>";
}

mysqli_close($db_connect);
?>
