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
//Проверка роли
$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
    if($RoleId != 2){
        header("Location: adminhome.php");
    }
}

//Проверка на переход в профиль другого пользователя
if (isset($_GET['usprofile'])) {
    $otherUser = $_GET['usprofile'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <?php
    //Заголовок для профиля другого читателя
    if($otherUser != null && $otherUser != $acc){
        $qTitle = "SELECT Nickname FROM user WHERE UserId = $otherUser";
        $sqlTitle = mysqli_query($db_connect, $qTitle);

        if ($sqlTitle && mysqli_num_rows($sqlTitle) > 0) {
            $row = mysqli_fetch_assoc($sqlTitle);
            $titlePage = $row['Nickname'];        
            echo "<title>$titlePage</title>";
        } 
    }
    //Заголовок для личного профиля
    else{
        $qTitle = "SELECT Nickname FROM user WHERE UserId = $acc";
        $sqlTitle = mysqli_query($db_connect, $qTitle);

        if ($sqlTitle && mysqli_num_rows($sqlTitle) > 0) {
            $row = mysqli_fetch_assoc($sqlTitle);
            $titlePage = $row['Nickname'];        
            echo "<title>$titlePage</title>";
        } 
    }
    
    ?>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<body>
    <header>
        <div class="user-profile">
            <?php
            //Вывод фото профиля
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
        <script>
            //Функция для отображения некоторого контента в зависимости от типа профиля
            document.addEventListener("DOMContentLoaded", function() {
                const otherUser  = parseInt("<?php echo $otherUser ; ?>", 10);
                const autUser  = parseInt("<?php echo $acc; ?>", 10);
                console.log('otherUser :', otherUser , 'autUser :', autUser );

                if (otherUser  && otherUser  !== autUser ) {
                    hideElement();
                }
                else {
                    var bestgenre = document.getElementById("other-best-genre");
                    if (bestgenre) {
                        bestgenre.style.display = "none";
                    }
                }
                //Функция скрытия элементов
                function hideElement() {
                    //Получение элементов по id
                    var awardlist = document.getElementById("award-list");
                    var bestgenre = document.getElementById("best-genre");
                    console.log('awardlist:', awardlist, 'bestgenre:', bestgenre);
                    //скрытие элементов
                    if (awardlist) {
                        awardlist.style.display = "none";
                    }
                    if (bestgenre) {
                        bestgenre.style.display = "none";
                    }
                }
            });
        </script>
    </header>
    
    <section class="user-profile-head">
        <?php
        //Вывод в аккаунте другого пользователя
        if($otherUser != null && $otherUser != $acc)
        {
            $qUserData = "SELECT 
                    u.UserPhoto, 
                    u.Nickname, 
                    (SELECT StatusPhoto FROM status s WHERE s.StatusId = u.Status) AS Status,
                    (SELECT COUNT(*) FROM subscription s1 WHERE s1.Subscriber = u.UserId) AS Subscriptions,
                    (SELECT COUNT(*) FROM subscription s2 WHERE s2.Blogger = u.UserId) AS Subscribers,
                    (SELECT COUNT(*) FROM readbook rb WHERE rb.User = u.UserId) AS Books
                FROM 
                    user u
                WHERE 
                    u.UserId = $otherUser;";
            $sqlUserData = mysqli_query($db_connect, $qUserData);

            if ($sqlUserData && mysqli_num_rows($sqlUserData) > 0) {
                $row = mysqli_fetch_assoc($sqlUserData);
                $UserPhoto = "Images/Profile/" . $row['UserPhoto'];
                $UsNick = "@" . $row['Nickname'];
                $UsStatus = "Images/Status/" . $row['Status'];

                $checkSubscription = "SELECT COUNT(*) AS isSubscribed FROM subscription WHERE Blogger = $otherUser AND Subscriber = $acc";
                $subscriptionResult = mysqli_query($db_connect, $checkSubscription);
                $isSubscribed = mysqli_fetch_assoc($subscriptionResult)['isSubscribed'] > 0;

                //Путь к изображению в зависимости от подписки
                $followButtonImage = $isSubscribed ? 'Images/Navigation/Follow.png' : 'Images/Navigation/Unfollow.png';

                echo "<table>";
                echo "<tr>";
                echo "<td rowspan='3'><img src='$UserPhoto' class='user-profile-icon-head'></td>";
                echo "<td colspan='3' class='us-nick-head'><p>$UsNick <img src='$UsStatus'></p></td>";
                echo "<td class='us-nick-head'><img src='$followButtonImage' id='foll-btn' name='foll-btn' style='margin-left: 50%' onclick='SubReader(this)'></td></tr>";
                echo "<tr class='us-num-head'><td><a href='subscribtion.php?myfan=1&otherUser=$otherUser' style='color: #FF589E'><p id='count-fan'>{$row['Subscribers']}</p></a></td>";
                echo "<td><a href='subscribtion.php?mysub=1&otherUser=$otherUser' style='color: #8893CD'><p>{$row['Subscriptions']}</p></a></td>";
                echo "<td><a href='library.php?otherUser=$otherUser' style='color: #FFB600'><p>{$row['Books']}</p></a></td>";
                echo "</tr>";
                echo "<tr class='us-exp-head'>";
                echo "<td><a href='subscribtion.php?myfan=1&otherUser=$otherUser' style='color: #FF589E'><p>подписчиков</p></a></td>";
                echo "<td><a href='subscribtion.php?mysub=1&otherUser=$otherUser' style='color: #8893CD'><p>подписок</p></a></td>";
                echo "<td><a href='library.php?otherUser=$otherUser' style='color: #FFB600'><p>книг прочитано</p></a></td>";
                echo "</tr>";
                echo "</table>";
            }
        }
        //Вывод шапки в личном аккаунте
        else
        {
            $qUserData = "SELECT 
                            u.UserPhoto, 
                            u.Nickname, 
                            (SELECT StatusPhoto FROM status s WHERE s.StatusId = u.Status) AS Status,
                            (SELECT COUNT(*) FROM subscription s1 WHERE s1.Subscriber = u.UserId) AS Subscriptions,
                            (SELECT COUNT(*) FROM subscription s2 WHERE s2.Blogger = u.UserId) AS Subscribers,
                            (SELECT COUNT(*) FROM readbook rb WHERE rb.User = u.UserId) AS Books
                        FROM 
                            user u
                        WHERE 
                            u.UserId = $acc;";
            $sqlUserData = mysqli_query($db_connect, $qUserData);

            if ($sqlUserData && mysqli_num_rows($sqlUserData) > 0) {
                $row = mysqli_fetch_assoc($sqlUserData);
                $UserPhoto = "Images/Profile/" . $row['UserPhoto'];
                $UsNick = "@" . $row['Nickname'];
                $UsStatus = "Images/Status/" . $row['Status'];
                echo "<table>";
                echo "<tr>";
                echo "<td rowspan='3'><img src='$UserPhoto' class='user-profile-icon-head'></td>";
                echo "<td colspan='3' class='us-nick-head'><p>$UsNick <img src='$UsStatus'></p></td>";
                echo "<td class='us-nick-head'><a href='editprofile.php'><img src='Images/Navigation/edit.png' style='margin-left: 50%' id='edit-img'></a></td></tr>";
                echo "<tr class='us-num-head'><td><a href='subscribtion.php?myfan=1' style='color: #FF589E'><p>{$row['Subscribers']}</p></a></td>";
                echo "<td><a href='subscribtion.php?mysub=1' style='color: #8893CD'><p>{$row['Subscriptions']}</p></a></td>";
                echo "<td><a href='library.php?mylib=1' style='color: #FFB600'><p>{$row['Books']}</p></a></td>";
                echo "</tr>";
                echo "<tr class='us-exp-head'>";
                echo "<td><a href='subscribtion.php?myfan=1' style='color: #FF589E'><p>подписчиков</p></a></td>";
                echo "<td><a href='subscribtion.php?mysub=1' style='color: #8893CD'><p>подписок</p></a></td>";
                echo "<td><a href='library.php?mylib=1' style='color: #FFB600'><p>книг прочитано</p></a></td>";
                echo "</tr>";
                echo "</table>";
            } 
        }
        ?>
    </section>
    <script>
        //Функция подписки
        function SubReader(button) {
                const otherUser = <?php echo $otherUser; ?>; //Получение id пользователя, на которого нужно подписаться/отписаться
                //Проверка подписки пользователя
                const isSubscribed = button.src.includes('Follow.png');

                const xhr = new XMLHttpRequest();
                //Отправление запроса к нужному файлу
                xhr.open('POST', isSubscribed ? 'unsubscribe.php' : 'subscribe.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    if (xhr.status === 200) {//Успешный результат
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            //Вывод нового изображения для кнопки
                            button.src = isSubscribed ? 'Images/Navigation/Unfollow.png' : 'Images/Navigation/Follow.png';
                            
                            const fansCountCell = document.getElementById('count-fan');
                            if (fansCountCell) {
                                fansCountCell.textContent = response.newFansCount;
                            }

                        } else {
                            console.error("Ошибка при изменении подписки:", response.message);
                        }
                    } else {
                        console.error('Ошибка при выполнении запроса:', xhr.statusText);
                    }
                };
                xhr.onerror = function() {
                console.error('Ошибка сети');
            };

            xhr.send(`otherUser=${otherUser}`); //Отправка запроса
        }
    </script>
    <?php
    //Вывод информации о другом пользователе
    if($otherUser != null && $otherUser != $acc){
        $q = "SELECT b.BookId, b.BookName, b.Author, b.BookImage, u.Nickname FROM book b 
        JOIN readbook r ON r.Book = b.BookId
        JOIN user u ON u.UserId = r.User
        WHERE r.User = $otherUser
        LIMIT 5";

        $sql = mysqli_query($db_connect, $q);

        if ($sql && mysqli_num_rows($sql) > 0) {
            echo '<section class="books-section" id="my-library">';
            echo '<div id="left" class="section-bookmark" style="background-image: url(\'Images/Navigation/YellowSection.png\');">';
            echo '<h2 class="section-title">Библиотека пользователя</h2>';
            echo '</div>';
            echo '<div class="book-cards">';

            $images = [
                'Images/Books/BlueBook.png',
                'Images/Books/PinkBook.png',
                'Images/Books/YellowBook.png'
            ];
            
            $imageCount = count($images);
            $index = 0;

            while ($userrow = mysqli_fetch_assoc($sql)) {
                echo "<a href='bookcard.php?id={$userrow['BookId']}&otherUser=$otherUser'><div class='book-card'>";
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
                    <a href='library.php?uslib=1&otherUser=$otherUser'><img src='Images/Navigation/More.png' alt='MoreBooks' id='more-books'></a>
                </div>";
            echo '</div>';
            echo '</section>';
        } 
        else{
            echo "<p id='user-no-book'>Пользователь не добавил в прочитанное ни одной книги!</p>";
        }  
    }
    //Вывод информации в личном профиле
    else{
        $q = "SELECT b.BookId, b.BookName, b.Author, b.BookImage FROM book b 
        JOIN readbook r ON r.Book = b.BookId
        WHERE r.User = $acc
        LIMIT 5";

        $sql = mysqli_query($db_connect, $q);

        if ($sql && mysqli_num_rows($sql) > 0) {
            echo '<section class="books-section" id="my-library">';
            echo '<div id="left" class="section-bookmark" style="background-image: url(\'Images/Navigation/YellowSection.png\');">';
            echo '<h2 class="section-title">Моя библиотека</h2>';
            echo '</div>';
            echo '<div class="book-cards">';

            $images = [
                'Images/Books/BlueBook.png',
                'Images/Books/PinkBook.png',
                'Images/Books/YellowBook.png'
            ];
            
            $imageCount = count($images);
            $index = 0;

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
                    <a href='library.php?mylib=1'><img src='Images/Navigation/More.png' alt='MoreBooks' id='more-books'></a>
                </div>";
            echo '</div>';
            echo '</section>';
        } 
    }
    ?>
    <section class="genres-section" id="award-list">
        <div id="right" class="section-bookmark" style="background-image: url('Images/Navigation/BlueSectionRight.png');">
            <h2 class="section-title">Награды</h2>
        </div>
        <div class="genre-cards">
        <?php
            //Вывод полученных наград
                $q = "SELECT a.AwardName, a.AwardPath, a.AwardDescription FROM awards a
                    JOIN awardsreceived ar ON ar.Award = a.AwardId
                    WHERE ar.UserId = $acc
                    LIMIT 5";
                $sql = mysqli_query($db_connect, $q);
                if ($sql) {
                    while ($userrow = mysqli_fetch_assoc($sql)) {
                        $currentImage = "Images/Awards/" . $userrow['AwardPath'];
                        echo "<div class='award-card'>
                                <img src='$currentImage' title='{$userrow['AwardDescription']}'>
                                <p>{$userrow['AwardName']}</p>
                            </div>";

                        $index++;
                        if ($index >= $imageCount) {
                            $index = 0;
                        }
                    }
                }
            ?>
            <div class="book-card" id='more-genres-card'>
                <a href="awards.php">
                    <img src="Images/Navigation/More.png" alt="More Genres" id="more-genres">
                </a>
            </div>
        </div>
    </section>
    <?php
    if($otherUser != null && $otherUser != $acc){
        echo " ";
    }
    //Вывод книг по предпочтениям
    else{
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

        if ($sql && mysqli_num_rows($sql) > 0) {
            echo '<section class="books-section" id="preferences">';
            echo '<div id="left" class="section-bookmark" style="background-image: url(\'Images/Navigation/PinkSection.png\');">';
            echo '<h2 class="section-title">Книги по вашим предпочтениям</h2>';
            echo '</div>';
            echo '<div class="book-cards">';

            $images = [
                'Images/Books/BlueBook.png',
                'Images/Books/PinkBook.png',
                'Images/Books/YellowBook.png'
            ];
            
            $imageCount = count($images);
            $index = 0;

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
    }
    ?>
    <?php
    //Вывод любимых жанров
    $q = "SELECT 
                g.GenreName, 
                g.GenreImage, 
                COUNT(rb.Book) AS ReadCount
            FROM 
                genre g
            JOIN 
                book b ON b.Genre = g.GenreId
            JOIN 
                readbook rb ON rb.Book = b.BookId
            WHERE 
                rb.User = $acc
            GROUP BY 
                g.GenreName, g.GenreImage
            ORDER BY 
                ReadCount DESC
            LIMIT 5;";
    $sql = mysqli_query($db_connect, $q);
    if ($sql && mysqli_num_rows($sql) > 0) {
        echo "<section class='genres-section' id='best-genre'>
        <div id='right' class='section-bookmark' style='background-image: url(Images/Navigation/YellowSectionRight.png);'>
            <h2 class='section-title'>Любимые жанры</h2>
        </div>
        <div class='genre-cards'>";
        while ($userrow = mysqli_fetch_assoc($sql)) {
            $currentImage = "Images/Genre/" . $userrow['GenreImage'];
            echo "<div class='award-card'>
                    <img src='$currentImage' title='{$userrow['GenreName']}'>
                    <p>{$userrow['GenreName']}</p>
                </div>";

            $index++;
            if ($index >= $imageCount) {
                $index = 0;
            }
        }
        echo "</div>
                </section>";
    }
    ?>
        
    <?php
    if($otherUser != null && $otherUser != $acc){
        $q = "SELECT 
                g.GenreId,
                g.GenreName, 
                g.GenreImage, 
                COUNT(rb.Book) AS ReadCount
            FROM 
                genre g
            JOIN 
                book b ON b.Genre = g.GenreId
            JOIN 
                readbook rb ON rb.Book = b.BookId
            WHERE 
                rb.User = $otherUser
            GROUP BY 
                g.GenreName, g.GenreImage
            ORDER BY 
                ReadCount DESC
            LIMIT 5;";

        $sql = mysqli_query($db_connect, $q);

        if ($sql && mysqli_num_rows($sql) > 0) {
            echo '<section class="genres-section" id="other-best-genre">';
            echo '<div id="right" class="section-bookmark" style="background-image: url(\'Images/Navigation/BlueSectionRight.png\');">';
            echo '<h2 class="section-title">Любимые жанры пользователя</h2>';
            echo '</div>';
            echo '<div class="genre-cards">';
            $genreId = $userrow['GenreId'];

            while ($userrow = mysqli_fetch_assoc($sql)) {
                $currentImage = "Images/Genre/" . $userrow['GenreImage'];
                echo "<div class='award-card'>
                            <img src='$currentImage' title='{$userrow['GenreName']}'>
                            <p>{$userrow['GenreName']}</p>
                        </div>";
            }
            echo '</div>';
            echo '</section>';
        }
    }
    ?>
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
