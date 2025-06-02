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
if (isset($_GET['id'])) {
    $SelectBookId = $_GET['id'];
}
else{
    header("Location: index.php");
}

if (isset($_GET['otherUser'])) {
    $otherUser = $_GET['otherUser'];
}

$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
}

if($RoleId == 1){
   //Модерация книги
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['book_check'])) {
            if ($_POST['book_check'] == '1') {
                $moderQ = "UPDATE book SET ModerationPassed = true WHERE BookId = $SelectBookId";
                $sqlModer = mysqli_query($db_connect, $moderQ);
                if ($sqlModer) {
                    header("Location: moderbook.php");
                } else {
                    $error = mysqli_error($db_connect);
                    echo "<script> alert(" . json_encode("Ошибка при обновлении записи: $error") . "); </script>";                }
            } 
            else if ($_POST['book_check'] == '0') {
                $moderQ = "DELETE FROM book WHERE BookId = $SelectBookId";
                $sqlModer = mysqli_query($db_connect, $moderQ);
                if ($sqlModer) {
                    header("Location: moderbook.php");
                } else {
                    $error = mysqli_error($db_connect);
                    echo "<script> alert(" . json_encode("Ошибка при обновлении записи: $error") . "); </script>";                }
            }
        }
    } 
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
        $PageTitle = $row2['BookName'] . " - " . $row2['Author'];
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
    
    <section class='select-book'>
        <?php
        $q = "SELECT BookName, Author, GenreName, BookDescription, AverageScore, BookImage FROM book
        JOIN genre ON genre.GenreId = book.Genre 
        WHERE BookId = $SelectBookId";
        $sql = mysqli_query($db_connect, $q);

        if ($sql && mysqli_num_rows($sql) > 0) 
        {
            $row = mysqli_fetch_assoc($sql);
            echo "<table><tr>";
            echo "<td colspan='2' class='mobile-cover-book'>";
            if($row['BookImage'] != null) {
                $SelBookImg = "Images/Books/" . $row['BookImage'];
                echo "<center><img src='$SelBookImg' class='mob-cover-sel-book'></center>";
            }
            else {
                $bookImages = ["Images/Books/BlueBook.png", "Images/Books/PinkBook.png", "Images/Books/YellowBook.png"];
                $randomIndex = array_rand($bookImages);
                $SelBookImg = $bookImages[$randomIndex];
                echo "<center><img src='$SelBookImg' style='box-shadow: none;' class='mob-cover-sel-book'></center>";
            }
            echo "</td><tr>";
            echo "<tr><td rowspan='4' style='vertical-align: middle;'>";
            if($row['BookImage'] != null) {
                $SelBookImg = "Images/Books/" . $row['BookImage'];
                echo "<img src='$SelBookImg' class='web-cover-sel-book'></td>";
            }
            else {
                $bookImages = ["Images/Books/BlueBook.png", "Images/Books/PinkBook.png", "Images/Books/YellowBook.png"];
                $randomIndex = array_rand($bookImages);
                $SelBookImg = $bookImages[$randomIndex];
                echo "<img src='$SelBookImg' style='box-shadow: none;' class='web-cover-sel-book'></td>";
            }
            $SelBookName = $row['BookName'] . " — " . $row['Author'];

            echo "<td><p class='sel-book-n'>$SelBookName</p></td></tr>";
            echo "<tr><td class='sel-book-genre'><p>Жанр: {$row['GenreName']}</p></td></tr>";
            if($row['AverageScore']>=4) {
                echo "<tr><td class='sel-book-score'><p style='color: #34C85A'>&#9733; {$row['AverageScore']}</p></td></tr>";
            }
            else if($row['AverageScore']>=3) {
                echo "<tr><td class='sel-book-score'><p style='color: #FFB600'>&#9733; {$row['AverageScore']}</p></td></tr>";
            }
            else if($row['AverageScore']>0){
                echo "<tr><td class='sel-book-score'><p style='color: #FF0000'>&#9733; {$row['AverageScore']}</p></td></tr>";
            }
            else{
                echo "<tr><td class='sel-book-score'><p style='color: #686868'>&#9733; Нет оценки</p></td></tr>";
            }
            echo "<tr><td><p class='sel-book-descr'>{$row['BookDescription']}</p></td></tr>";
            echo "</table>";
        } 
        if ($RoleId == 1){
            $IsModerQ = "SELECT * FROM book
            WHERE BookId = $SelectBookId and ModerationPassed = true";
            $sqlIsModer = mysqli_query($db_connect, $IsModerQ);

            if ($sqlIsModer && mysqli_num_rows($sqlIsModer) == 0) 
            {
                echo "<center>
                <form action='' method='POST'>
                <button type='submit' name='book_check' value='1' style='color: #455C86; background-color: #FF87B9; padding: 1%; height: auto;'>Книга соответствует требованиям</button>
                <button type='submit' name='book_check' value='0' style='color: #455C86; background-color: #8893CD; padding: 1%; height: auto;'>Книга не соответствует требованиям</button>
                </form>
                </center>";        
            }
            else
            {
                echo "<center>
                    <a href='newbook.php?edit=1&BookId=$SelectBookId'><button style='padding: 1%; height: auto;'>Редактировать сведения о книге</button></a>
                </center>";
            }
        }
        else if($RoleId == 2){
            $IsReadingQ = "SELECT Comment, Mark, UserPhoto FROM readbook
            JOIN user ON user.UserId = readbook.User
            WHERE Book = $SelectBookId and User = $acc";
            $sqlIsReading = mysqli_query($db_connect, $IsReadingQ);

            if ($sqlIsReading && mysqli_num_rows($sqlIsReading) == 0) 
            {
                echo "<center>
                <button onclick=\"location.href='bookreview.php?BookId=$SelectBookId'\">Добавить в прочитанное</button>
                </center>";        
            }
            else
            {
                $rewiewSel = mysqli_fetch_assoc($sqlIsReading);
                $UsPh = "Images/Profile/" . $rewiewSel['UserPhoto'];
                echo "</section>";
                echo "<section class='select-book-rewiew'>";
                echo "<div class='review-box'>
                        <p class='label-review'>Моя рецензия:</p>
                        <p>{$rewiewSel['Comment']}</p>";
                if($rewiewSel['Mark']>4){
                    echo "<p style='color: #34C85A;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                else if($rewiewSel['Mark']>=3){
                    echo "<p style='color: #FFB600;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                else if($rewiewSel['Mark']>=1){
                    echo "<p style='color: #FF0000;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                echo "<table class='nav-menu-review'>
                        <tr>
                            <td id='nav-menu-review-start'>
                                <div class='edit-rev-style'>
                                    <a href='bookreview.php?BookId=$SelectBookId&edit=1'>
                                        <img src='Images/Navigation/edit.png'>
                                    </a>
                                </div>
                            </td>
                            <td id='nav-menu-review-end'>
                                <a href='quotes.php?bookId=$SelectBookId'>Перейти к избранным цитатам →</a>
                            </td>
                        </tr>
                    </table>";
                echo "</div>";
            }
        }
        
        //Вывод рецензии, если переход был совершен из библиотеки другого пользователя
        if($otherUser){
            $otherUserReview = "SELECT Comment, Mark, UserPhoto, Nickname FROM readbook
            JOIN user ON user.UserId = readbook.User
            WHERE Book = $SelectBookId and User = $otherUser";
            $sqlOUReview = mysqli_query($db_connect, $otherUserReview);

            if ($sqlOUReview && mysqli_num_rows($sqlOUReview) != 0) 
            {
                $rewiewSel = mysqli_fetch_assoc($sqlOUReview);
                $UsPh = "Images/Profile/" . $rewiewSel['UserPhoto'];
                $otherUserNick = "@" . $rewiewSel['Nickname'];
                echo "</section>";
                echo "<section class='select-book-rewiew'>";
                echo "<table style='width: 100%; margin-bottom: 5%'><tr>";
                echo "<td style='vertical-align: bottom; text-align: right;'>";
                echo "<a href='profile.php?usprofile=$otherUser' class='review-user-photo'>";
                echo "<img src='$UsPh' class='profile-icon'>";
                echo "</a>";
                echo "</td>";
                echo "<td>";
                echo "<div class='review-box' style='background-color: #fc9dc3; margin-bottom: 0;'>
                        <p class='label-review'>Рецензия $otherUserNick</p>
                        <p>{$rewiewSel['Comment']}</p>";
                if($rewiewSel['Mark']>4){
                    echo "<p style='color: #2ab74e;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                else if($rewiewSel['Mark']>=3){
                    echo "<p style='color: #da9c00;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                else if($rewiewSel['Mark']>=1){
                    echo "<p style='color: #FF0000;' class='score-img'>&#9733; {$rewiewSel['Mark']}</p>";
                }
                echo "</div>";
                echo "</td>";
                echo "</tr></table>";        
            }
        }
        //Вывод всех рецензий
        else {
            $otherUserReview = "SELECT Comment, Mark, UserPhoto, Nickname, UserId FROM readbook
            JOIN user ON user.UserId = readbook.User
            WHERE Book = $SelectBookId AND UserId != $acc";
            $sqlOUReview = mysqli_query($db_connect, $otherUserReview);

            if ($sqlOUReview && mysqli_num_rows($sqlOUReview) != 0) 
            {
                echo "</section>";
                echo "<section class='select-book-rewiew'>";

                while ($rewiewSel = mysqli_fetch_assoc($sqlOUReview)) {
                    $UsPh = "Images/Profile/" . $rewiewSel['UserPhoto'];
                    $otherUserNick = "@" . $rewiewSel['Nickname'];
                    $otherUser = $rewiewSel['UserId'];
                    echo "<table style='width: 100%; margin-bottom: 5%'><tr>";
                    echo "<td style='vertical-align: bottom; text-align: right;'>";
                    echo "<a href='profile.php?usprofile=$otherUser' class='review-user-photo'>";
                    echo "<img src='$UsPh' class='profile-icon'>";
                    echo "</a>";
                    echo "</td>";
                    echo "<td>";
                    echo "<div class='review-box' style='background-color: #fc9dc3; margin-bottom: 0;'>
                            <p class='label-review'>
                                Рецензия $otherUserNick";
                    if($RoleId == 1){
                        echo "<a href='blockUser.php?user=$otherUser&BookId=$SelectBookId' onclick='return confirm('Вы уверены, что хотите заблокировать этого пользователя?');'>
                                    <img src='Images/Navigation/Block.png' class='block-but' alt='Блокировать пользователя'>
                                </a>";
                    }
                    echo "</p><p>{$rewiewSel['Comment']}</p>";
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
        }
        ?>

    </section>
    <center>
        <footer <?php if (isset($RoleId) && $RoleId == 1) echo 'style="display: flex; align-items: center; justify-content: center;"'; ?>>
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
            <div class="footer-menu" <?php if (isset($RoleId) && $RoleId == 1) echo 'style="display: none;"'; ?>>
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