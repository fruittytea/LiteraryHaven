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
if (isset($_GET['readId'])) {
    $readId = $_GET['readId'];
}
else{
    header("Location: index.php");
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
    <?php
    $q2 = "SELECT BookName, Author FROM book b
            JOIN readbook rb ON rb.Book = b.BookId
            WHERE rb.ReadId = $readId";
    $sql2 = mysqli_query($db_connect, $q2);

    if ($sql2 && mysqli_num_rows($sql2) > 0) 
    {
        $row2 = mysqli_fetch_assoc($sql2);
        $PageTitle = $row2['BookName'] . " - " . $row2['Author'] . " | Новая цитата";
        echo "<title>$PageTitle</title>";
    } 
    ?>
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
            <img src="Images/Navigation/BurgerMenu.png" alt="Меню" style="border-radius: 0%; object-fit: contain;" class="profile-icon">
            <div class="menu-container">
                <a href="library.php">Полная библиотека</a><br>
                <a href="readers.php">Рейтинг читателей</a>
            </div>
        </div>
    </header>
    <section class='select-book-rewiew' style='margin: 0;'>
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png'); margin-top: 2%">
            <h2 class="section-title">Новая цитата</h2>
        </div>
        <center>
        <form method='GET' class='new-quote' action="quotesadd.php"> 
            <input type="text" value="<?php echo $readId;?>" style="display: none;" name="read-id-box" id="read-id-box">
            <label for="user-quote-box">Какую цитату вы бы хотели добавить в избранные?</label><br>
            <textarea required name="user-quote-box" id="user-quote-box" placeholder="Напишите цитату в этом поле"></textarea><br>
            <label for="reading-page-box">На какой странице в вашей книге находится эта цитата? </label><br>
            <input required type="number" name="reading-page-box" id="reading-page-box"><br>
            <input type="submit" id='AddNewQuote' value="Сохранить цитату">
        </form>
    </center>
    </section>
    <script>
        //Функция для позиционирования подвала
        function adjustFooter() {
            const footer = document.querySelector('footer');
            // Полная высота документа (включая шапки, контент и футер)
            const docHeight = document.body.scrollHeight;
            // Высота окна браузера
            const windowHeight = window.innerHeight;

            // Если документ меньше окна, фиксируем футер внизу окна
            if(docHeight < windowHeight) {
            footer.classList.add('fixed-bottom');
            } else {
            footer.classList.remove('fixed-bottom');
            }
        }

        window.addEventListener('load', adjustFooter);
        window.addEventListener('resize', adjustFooter);
    </script>
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