<?php
//Начало сессии при авторизации
session_start();
//Проверка передачи id аккаунта
if (isset($_SESSION['acc'])) {
    $acc = $_SESSION['acc']; 
} elseif (isset($_GET['acc'])) {
    $acc = $_GET['acc'];
    $_SESSION['acc'] = $acc;
} else {
    header("Location: autorisation.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Все рецензии</title>
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
    <?php
    //Подключение к БД
    $host="localhost";
    $dbname="sadkovaann";
    $password="R2UJCEw@Q";
    $user="sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if(!$db_connect){
        die("Ошибка подключения" . mysqli_connect_error());
    }

    //Проверка роли на соответствие
    $roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
    $roleCheckSql = mysqli_query($db_connect, $roleCheck);

    if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
        $rowRole = mysqli_fetch_assoc($roleCheckSql);
        $RoleId = $rowRole['Role'];
        if($RoleId != 1){
            header("Location: index.php");
        }
    }
    ?>
<body>
    <header>
        <div class="user-profile">
            <?php
            //Вывод фотографии пользователя
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
            <img src="Images/Navigation/BurgerMenu.png" alt="Меню" style="border-radius: 0%; object-fit: contain;" class="profile-icon">
            <div class="menu-container">
                <a href="library.php">Полная библиотека</a><br>
                <a href="readers.php">Рейтинг читателей</a>
            </div>
        </div>
    </header>
    <section class="books-section" id="new">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/YellowSection.png'); margin-top: 1%;">
            <h2 class="section-title">Все рецензии</h2>
        </div>
        <?php
        //Получение информации о рецензиях
        $otherUserReview = "SELECT Comment, Mark, UserPhoto, Nickname, UserId, b.BookName, b.Author FROM readbook rb
        JOIN user u ON u.UserId = rb.User 
        JOIN book b ON b.BookId = rb.Book
        ORDER BY ReadId DESC;";
        $sqlOUReview = mysqli_query($db_connect, $otherUserReview);

        if ($sqlOUReview && mysqli_num_rows($sqlOUReview) != 0) 
        {
            echo "</section>";
            echo "<section class='select-book-rewiew'>";
            //Вывод всех рецензий на страницу
            while ($rewiewSel = mysqli_fetch_assoc($sqlOUReview)) {
                $UsPh = "Images/Profile/" . $rewiewSel['UserPhoto'];
                $otherUserNick = "@" . $rewiewSel['Nickname'];
                $BookTitle = $rewiewSel['BookName'] . " — " . $rewiewSel['Author'];
                $otherUser = $rewiewSel['UserId'];
                echo "<table style='width: 100%; margin-bottom: 5%'><tr>";
                echo "<td style='vertical-align: bottom; text-align: right;'>";
                echo "<a href='profile.php?usprofile=$otherUser' class='review-user-photo'>";
                echo "<img src='$UsPh' class='profile-icon'>";
                echo "</a>";
                echo "</td>";
                echo "<td>";
                echo "<div class='review-box' style='background-color: #ffda7d; margin-bottom: 0;'>
                        <p class='label-review'>
                            Рецензия $otherUserNick 
                            <a href='blockUser.php?user=$otherUser&BookId=$SelectBookId' onclick='return confirm(\"Вы уверены, что хотите заблокировать этого пользователя?\");'>
                                <img src='Images/Navigation/Block.png' class='block-but' alt='Блокировать пользователя'>
                            </a>
                        </p>
                        <hr style='border: none; background-color: #455C86; width: 50%;' size='4%' />
                        <h3 style='color: #455C86; text-align: center;'> $BookTitle </h3>
                        <p>{$rewiewSel['Comment']}</p>";
                if ($rewiewSel['Mark'] > 4) {
                    echo "<p style='color: #2ab74e;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                } else if ($rewiewSel['Mark'] >= 3) {
                    echo "<p style='color: #da9c00;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                } else if ($rewiewSel['Mark'] >= 1) {
                    echo "<p style='color: #FF0000;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                echo "</div>";
                echo "</td>";
                echo "</tr></table>";
            }
        }
        ?>
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
