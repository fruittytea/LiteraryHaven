<?php
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
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <title>Награды</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<body>
    <header>
        <div class="user-profile">
            <?php
            $q1 = "SELECT UserPhoto FROM user WHERE UserId = $acc";
            $sql1 = mysqli_query($db_connect, $q1);

            if ($sql1 && mysqli_num_rows($sql1) > 0) {
                $row = mysqli_fetch_assoc($sql1);
                $imageName = $row['UserPhoto'];
                $profimage = "Images/Profile/" . $imageName;
                
                echo "<a href='profile.php'>
                        <img src='$profimage' alt='Профиль' class='profile-icon'>
                    </a>";
            } else {
                echo "<a href='profile.php'>
                        <img src='Images/Profile/NoPhoto.png' alt='Профиль' class='profile-icon'>
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
                <a href="library.php" style="margin-bottom: 1%">Полная библиотека</a><br>
                <a href="readers.php">Рейтинг читателей</a>
            </div>
        </div>
    </header>
    
    <section class="books-section" id="preferences">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png'); margin-top: 2%">
            <h2 class="section-title">Мои награды</h2>
        </div>
        <div class="book-cards" id="bookCards" style="margin: 0 15%;">
            <?php
                $q = "SELECT 
                        a.AwardName, 
                        a.AwardDescription, 
                        a.AwardPath,
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 
                                FROM awardsreceived ar 
                                WHERE ar.Award = a.AwardId AND ar.UserId = $acc
                            ) THEN 1 
                            ELSE 0 
                        END AS AwardReceived
                    FROM 
                        awards a
                    ORDER BY 
                        AwardReceived DESC;";
                $sql = mysqli_query($db_connect, $q);
                if ($sql) {

                    while ($awardsrow = mysqli_fetch_assoc($sql)) {
                        echo "<div class='award-card'>";

                        $AwardIcon = "Images/Awards/".$awardsrow['AwardPath'];
                        $awardReceived = $awardsrow['AwardReceived'];
                        
                        $cssClass = $awardReceived == 0 ? 'grayscale' : '';
                        
                        echo "<img src='$AwardIcon' alt='{$awardsrow['AwardName']}' title='{$awardsrow['AwardDescription']}' class='$cssClass'>";
                        //$AwardIcon = "Images/Awards/".$awardsrow['AwardPath'];
                        //echo "<img src='$AwardIcon' alt='{$awardsrow['AwardName']}' title='{$awardsrow['AwardDescription']}' >
                        
                        echo "<p id='book-name-p'>{$awardsrow['AwardName']}</p>";
                        echo "</div>";
                    }
                }
            ?>
        </div>
    </section>
    <center>
        <footer>
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
            <div class="footer-menu">
                <div class="user-menu">
                    <h3>Пользователь</h3>
                    <ul>
                        <li><a href="profile.php">Мой профиль</a></li>
                        <li><a href="subscribtion.php?mysub=1">Мои подписки</a></li>
                        <li><a href="awards.php">Мои награды</a></li>
                        <li><a href="library.php?mylib=1">Моя библиотека</a></li>
                    </ul>
                </div>
                <div class="books-menu">
                    <h3>Книги</h3>
                    <ul>
                        <li><a href="allgenres.php">Жанры</a></li>
                        <li><a href="library.php">Полная библиотека</a></li>
                        <li><a href="readers.php">Рейтинг читателей</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    </center>
</body>
</html>