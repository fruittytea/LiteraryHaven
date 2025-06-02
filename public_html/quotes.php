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
if (isset($_GET['bookId'])) {
    $SelectBookId = $_GET['bookId'];
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
    $q2 = "SELECT BookName, Author FROM book
    WHERE BookId = $SelectBookId";
    $sql2 = mysqli_query($db_connect, $q2);

    if ($sql2 && mysqli_num_rows($sql2) > 0) 
    {
        $row2 = mysqli_fetch_assoc($sql2);
        $PageTitle = $row2['BookName'] . " - " . $row2['Author'] . " | Цитаты";
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
    <?php
    $readIdQuery = "SELECT ReadId FROM readbook 
                    WHERE Book = $SelectBookId and User = $acc;";
    $sqlreadId = mysqli_query($db_connect, $readIdQuery);
    if ($sqlreadId && mysqli_num_rows($sqlreadId) != 0) {
        $row = mysqli_fetch_assoc($sqlreadId);
        $readId = $row['ReadId'];
    }


    $IsReadingQ = "SELECT ReadId, QuotesId, ReadBook, Quote, Page FROM quotes q 
                    JOIN readbook rb ON q.ReadBook = rb.ReadId
                    WHERE rb.Book = $SelectBookId and rb.User = $acc;";
    $sqlIsReading = mysqli_query($db_connect, $IsReadingQ);

    if ($sqlIsReading && mysqli_num_rows($sqlIsReading) != 0) {
        echo "<section class='select-book-rewiew' style='margin-top: 5%;'>";
        
        $colors = ['#FF87B9', '#FFCC4C', '#99a5e7'];
        $colorIndex = 0;

        while ($rewiewSel = mysqli_fetch_assoc($sqlIsReading)) {
            $pageQ = $rewiewSel['Page'];
            $quote = $rewiewSel['Quote'];
            
            $currentColor = $colors[$colorIndex % count($colors)];
            
            echo "<div class='review-box' style='background-color: $currentColor;'>
                    <p class='label-review'>Цитата — $pageQ страница</p>
                    <p>« $quote »</p>
                </div>";
            
            $colorIndex++;
        }
        
        echo "<div id='no-results' style='display: block; margin-top: 7%;'>";    
    } else {
        echo "<div id='no-results' style='display: block; margin-top: 7%;'>";
        echo "<p>У вас нет избранных цитат из этой книги!</p>
            <p class='mini-label'>Вы можете добавить цитату с помощью кнопки!</p>";
    }

    echo "<a href='newquotes.php?readId=$readId'>
            <button class='new-quote-style'>Добавить цитату</button>
        </a>
        </div>";
    ?>
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