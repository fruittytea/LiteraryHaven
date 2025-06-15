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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Главная страница</title>
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<script type="text/javascript" src="/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=VN7HGoi4BiWMw7b1Bi7jnARiAvcXNESdmDsnACt2fLXEGuEAYpMMDiA-pf1ZWEbL2WGYt4nQsTGHYkuFaSz8y0Gt2me_TPUsjQS1lW6tw5pY1YV8lX_VfQhzvhnN3JVkKb4BsEOvDXv1nu_GuVoo3XUQyzA-8tmN1Kd_IUP2DcjngmOIV_lsAZZZ775jkwQNyrEGW7guI1B7gy_IMg54eiT5uuOrttS6V0XJAWnHl6g" charset="UTF-8"></script></head>
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
        if($RoleId == 1){
            header("Location: adminhome.php");
        }
    }
?>
<body>
    <header>
        <div class="user-profile">
            <?php
            //Вывод фотографии профиля
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
    <div class="banner">
        <img id="bannerImage" src="Images/Banner/banner1.png" alt="Баннер">
        <p>Твой проводник в мире книг!</p>
    </div>
    <?php
    //Получение 5 книг по предпочтениям пользователя с рейтингом не ниже 4
    $q = "SELECT b.BookId, b.BookName, b.Author, b.BookImage FROM book b 
    WHERE b.Genre IN ( 
        SELECT DISTINCT b2.Genre FROM readbook r 
        JOIN book b2 ON b2.BookId = r.Book WHERE r.User = $acc AND r.Mark > 4.0 
    ) 
    AND b.BookId NOT IN ( 
        SELECT r2.Book FROM readbook r2 
        WHERE r2.User = $acc 
    ) 
    LIMIT 5";

    $sql = mysqli_query($db_connect, $q);
    //Раздел "Книги по вашим предпочтениям"
    if ($sql && mysqli_num_rows($sql) > 0) {
        echo '<section class="books-section" id="preferences">';
        echo '<div id="left" class="section-bookmark" style="background-image: url(\'Images/Navigation/YellowSection.png\');">';
        echo '<h2 class="section-title">Книги по вашим предпочтениям</h2>';
        echo '</div>';
        echo '<div class="book-cards">';
        //Массив из изображений-заглушек для книг, у которых нет обложек
        $images = [
            'Images/Books/BlueBook.png',
            'Images/Books/PinkBook.png',
            'Images/Books/YellowBook.png'
        ];
        
        $imageCount = count($images);
        $index = 0;
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
        echo "<div class='book-card' id='more-books-card'>
                <a href='library.php?preferences=1'><img src='Images/Navigation/More.png' alt='MoreBooks' id='more-books'></a>
            </div>";
        echo '</div>';
        echo '</section>';
    }
    ?>

    <section class="books-section" id="popular">
        <div id="right" class="section-bookmark" style="background-image: url('Images/Navigation/BlueSectionRight.png');">
            <h2 class="section-title">Сейчас популярно</h2>
        </div>
        <div class="book-cards">
            <?php
            //Запрос на вывод популярных книг с оценкой от 4 до 5
            $q = "SELECT BookId, BookName, Author, BookImage FROM book 
            JOIN readbook ON book.BookId = readbook.Book 
            WHERE AverageScore >= 4.0 AND AverageScore <= 5.0 
            GROUP BY BookName, Author 
            ORDER BY COUNT(readbook.Book) DESC, 
            AverageScore DESC 
            LIMIT 5;";
            $sql = mysqli_query($db_connect, $q);
            //Проверка выполнения запроса
            if ($sql) {
                //Массив из изображений-заглушек для книг, у которых нет обложек
                $images = [
                    'Images/Books/BlueBook.png',
                    'Images/Books/PinkBook.png',
                    'Images/Books/YellowBook.png'
                ];
                $imageCount = count($images);
                $index = 0;
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
            ?>
            <div class="book-card" id='more-books-card'>
                <?php echo "<a href='library.php?popular=1'><img src='Images/Navigation/More.png' alt='MoreBooks' id='more-books'>";?>
            </div>
        </div>
    </section>

    <section class="books-section" id="new">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png');">
            <h2 class="section-title">Новинки на сайте</h2>
        </div>
        <div class="book-cards">
            <?php
                //Вывод последних 5 добавленных книг
                $q = "SELECT BookId, BookName, Author, BookImage FROM book 
                WHERE ModerationPassed = true
                ORDER BY BookId DESC 
                LIMIT 5";
                $sql = mysqli_query($db_connect, $q);
                if ($sql) {
                    //Массив из изображений-заглушек для книг, у которых нет обложек
                    $images = [
                        'Images/Books/BlueBook.png',
                        'Images/Books/PinkBook.png',
                        'Images/Books/YellowBook.png'
                    ];
                    
                    $imageCount = count($images);
                    $index = 0;
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
            ?>
            <div class="book-card" id='more-books-card'>
                <?php echo "<a href='library.php?newbooks=1'><img src='Images/Navigation/More.png' alt='MoreBooks' id='more-books'></a>"; ?>
            </div>
        </div>
    </section>

    <section class="genres-section">
        <div id="right" class="section-bookmark" style="background-image: url('Images/Navigation/YellowSectionRight.png');">
            <h2 class="section-title">Жанры</h2>
        </div>
        <div class="genre-cards">
        <?php
                //Получение 5 жанров из БД
                $q = "SELECT GenreId, GenreName, GenreImage FROM genre LIMIT 5";
                $sql = mysqli_query($db_connect, $q);
                if ($sql) {
                    //Вывод жанров
                    while ($userrow = mysqli_fetch_assoc($sql)) {
                        $currentImage = "Images/Genre/" . $userrow['GenreImage'];
                        $genreId = $userrow['GenreId'];

                        echo "<a href='library.php?selectedGenre=$genreId'><div class='award-card'>
                                <img src='$currentImage' alt='Жанр'>
                                <p>{$userrow['GenreName']}</p>
                            </div><a>";

                        $index++;
                        if ($index >= $imageCount) {
                            $index = 0;
                        }
                    }
                }
            ?>
            <div class="book-card" id='more-genres-card'>
                <?php echo "<a href='allgenres.php'><img src='Images/Navigation/More.png' alt='More Genres' id='more-genres'></a>";?>
            </div>
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
    <script>
        //Массив с баннерами
        const images = [
            "Images/Banner/banner1.png",
            "Images/Banner/banner2.png"
        ];

        let currentIndex = 0;
        const bannerImage = document.getElementById("bannerImage");

        //Функция изменения изображения баннера
        function changeBannerImage() {
            currentIndex = (currentIndex + 1) % images.length;
            bannerImage.style.opacity = 0;

            setTimeout(() => {
                //Изменение источника изображения на следующее в массиве
                bannerImage.src = images[currentIndex];
                bannerImage.style.opacity = 1;
            }, 1000);
        }
        //Интервал для смены изображения - 5 секунд
        setInterval(changeBannerImage, 5000);
        //Функция предварительной загрузки изображений
        const preloadImages = (imageArray) => {
            imageArray.forEach((src) => {
                const img = new Image();
                img.src = src;
            });
        };
        //Вызов функции предварительной загрузки с массивом изображений
        preloadImages(images);
    </script>
</body>
</html>
