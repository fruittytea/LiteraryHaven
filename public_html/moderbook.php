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

//Проверка роли на соотвествие
$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
    if($RoleId !=1){
        header("Location: index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <title>Модерация добавленных книг</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<body>
    <header>
        <div class="user-profile">
            <?php
            if($RoleId == 1){
                echo "<a href='adminhome.php'>";
            }
            else if($RoleId == 2){
                echo "<a href='profile.php'>";
            }
            //Вывод фотографии пользователя
            $q1 = "SELECT UserPhoto FROM user WHERE UserId = $acc";
            $sql1 = mysqli_query($db_connect, $q1);

            if ($sql1 && mysqli_num_rows($sql1) > 0) {
                $row = mysqli_fetch_assoc($sql1);
                $imageName = $row['UserPhoto'];
                $profimage = "Images/Profile/" . $imageName;
                
                echo "<img src='$profimage' alt='Профиль' class='profile-icon'>
                    </a>";
            } else {
                echo "<img src='Images/Profile/NoPhoto.png' alt='Профиль' class='profile-icon'>
                    </a>";
            }
            ?>
            <div class="exit-container">
            <form action="logout.php" method="POST">
                <button type="submit" class="exit-icon">
                    <img src="Images/Navigation/Exit.png" alt="Выйти">
                </button>
            </form>    
            </div>    
        </div>
        <div class="logo">
            <a href="index.php">
                <img src="Images/Logo/logo.png" alt="Logo">
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="library.php">Полная библиотека</a></li>
                <li><a href="readers.php">Рейтинг читателей</a></li>
            </ul>
        </nav>
        <div class="nav-menu">
            <img src="Images/Navigation/BurgerMenu.png" alt="Меню" class="profile-icon">
            <div class="menu-container">
                <a href="library.php">Полная библиотека</a><br>
                <a href="readers.php">Рейтинг читателей</a>
            </div>
        </div>
    </header>
    
    <section class="books-section" id="preferences">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png'); margin-top: 2%">
            <h2 class='section-title'>Модерация книг</h2>
        </div>
        <div class="book-cards" id="bookCards" style="margin: 0 15%;">
            <?php
            //Получение книг, не прошедших модерацию
            $q = "SELECT BookId, BookName, Author, BookImage FROM book b 
            WHERE b.ModerationPassed = false";
            $sql = mysqli_query($db_connect, $q);
            if ($sql) {
                //Заглушки для обложек
                $images = [
                    'Images/Books/BlueBook.png',
                    'Images/Books/PinkBook.png',
                    'Images/Books/YellowBook.png'
                ];
                
                $imageCount = count($images);
                $index = 0;
                if ($sql && mysqli_num_rows($sql) > 0){
                    //Вывод книг
                        while ($userrow = mysqli_fetch_assoc($sql)) {
                        echo "<a href='bookcard.php?id={$userrow['BookId']}'><div class='book-card'>";
                        if ($userrow['BookImage']!= null){
                            $BookCover = "Images/Books/".$userrow['BookImage'];
                            echo "<img src='$BookCover' alt='Обложка' class='book-cover-img'>";
                        }
                        else{
                            $currentImage = $images[$index];
                            echo "<img src='$currentImage' alt='Book'>";
                        }
                        echo "<p id='book-name-p'>{$userrow['BookName']}</p>
                                <p id='author-name-p'>{$userrow['Author']}</p>";
                        echo "</div></a>";

                        $index++;
                        if ($index >= $imageCount) {
                            $index = 0;
                        }
                    }
                }
                else{
                    echo "<div id='no-results' style='display: block;'>
                    <p>Пока нет книг, которые не прошли модерацию!</p>
                    <a href='library.php' class='add-book-button'>
                        <button>Перейти в библиотеку</button>
                    </a>
                    </div>";
                }
            }
            ?>
        </div>
    </section>
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
