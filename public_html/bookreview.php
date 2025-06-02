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
if (isset($_GET['BookId'])) {
    $ReviewBookId = $_GET['BookId'];
}
else{
    header("Location: index.php");
}

if (isset($_GET['edit'])) {
    $edit = $_GET['edit'];
    $reviewQuery = "SELECT Comment, Mark FROM readbook WHERE Book = $ReviewBookId AND User = $acc";
    $reviewResult = mysqli_query($db_connect, $reviewQuery);
    if ($reviewResult && mysqli_num_rows($reviewResult) > 0) {
        $reviewData = mysqli_fetch_assoc($reviewResult);
        $existingReviewText = $reviewData['Comment'];
        $existingRating = $reviewData['Mark'];
    } else {
        $existingReviewText = '';
        $existingRating = 0;
    }
} else {
    $existingReviewText = '';
    $existingRating = 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <title>Рецензия на книгу</title>
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
            $genreQuery = "SELECT GenreId, GenreName FROM genre";
            $genreResult = mysqli_query($db_connect, $genreQuery);
            $genres = [];
            if ($genreResult) {
                while ($row = mysqli_fetch_assoc($genreResult)) {
                    $genres[] = $row;
                }
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
    
    <section class='new-review-section'>
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png'); margin-top: 2%">
            <h2 class="section-title">Рецензия на книгу</h2>
        </div>
        <center>
        <table>
            <tr>
                <td style='padding-right: 40px'>
                    <?php
                    $NewReviewBookSel = "SELECT BookName, Author, BookImage FROM book WHERE BookId = $ReviewBookId";
                    $sqlNewReview = mysqli_query($db_connect, $NewReviewBookSel);
                    if (!$sqlNewReview) {
                        die("Ошибка выполнения запроса: " . mysqli_error($db_connect));
                    }
                    if ($sqlNewReview && mysqli_num_rows($sqlNewReview) > 0) {
                        $rows = mysqli_fetch_assoc($sqlNewReview);
                        if($rows['BookImage'] != null) {
                            $SelBookImg = "Images/Books/" . $rows['BookImage'];
                        }
                        else {
                            $bookImages = ["Images/Books/BlueBook.png", "Images/Books/PinkBook.png", "Images/Books/YellowBook.png"];
                            $randomIndex = array_rand($bookImages);
                            $SelBookImg = $bookImages[$randomIndex];
                        }
                        $SelBookName = $rows['BookName'] . " — " . $rows['Author'];

                        echo "<p class='new-review-book-label'>$SelBookName</p>";
                        echo "<center><img class='new-review-book-img' src='$SelBookImg'></center>";
                    }
                    ?>
                </td>
                <td>
                    <center>
                        <form action='newreviewadd.php' method='GET' class='new-review-user'> 
                            <input type="text" name="book-id-review" id="book-id-review" style="display: none;" value="<?php echo $ReviewBookId?>">
                            <input type="text" name="rating-value-tb" id="rating-value-tb" style="display: none;" value="<?php echo htmlspecialchars($existingRating); ?>">
                            <label for="user-review-box">Напишите подробную рецензию на книгу </label><br>
                            <textarea required name="user-review-box" id="user-review-box" placeholder="Поделитесь своим мнением о прочитанной книге"><?php echo htmlspecialchars($existingReviewText); ?></textarea><br>
                            <label>Поставьте оценку прочитанной книге </label><br>
                            <div class="rating" id="rating">
                                <span class="book-score-star" id="rating-value"><?php if ($existingRating > 0) echo $existingRating . ' '; ?></span>
                                <span class="star <?php if($existingRating >= 1) echo 'selected'; ?>" data-value="1">&#9733;</span>
                                <span class="star <?php if($existingRating >= 2) echo 'selected'; ?>" data-value="2">&#9733;</span>
                                <span class="star <?php if($existingRating >= 3) echo 'selected'; ?>" data-value="3">&#9733;</span>
                                <span class="star <?php if($existingRating >= 4) echo 'selected'; ?>" data-value="4">&#9733;</span>
                                <span class="star <?php if($existingRating >= 5) echo 'selected'; ?>" data-value="5">&#9733;</span>
                            </div><br>
                            <?php
                            if($edit == 1){
                                echo "<input type='hidden' name='edit' value='1'>";
                            }
                            ?>
                            <input type="submit" id='AddNewReview' value="Сохранить рецензию">
                        </form>
                    </center>
                </td>
            </tr>
        </table>
        </center>
    </section>
    <script>
        //Функция для обновления рейтинга
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('rating-value');
            const ratingValue2 = document.getElementById('rating-value-tb');
            let selectedRating = 0; //Хранение рейтинга

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-value');
                    selectedRating = rating;

                    stars.forEach(s => s.classList.remove('selected'));

                    for (let i = 0; i < rating; i++) {
                        stars[i].classList.add('selected');
                    }

                    ratingValue.textContent = `${rating} `;
                    ratingValue2.value = `${rating}`;

                    //Цвет для выбранного рейтинга
                    updateStarColors(selectedRating);
                });

                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-value');
                    updateStarColors(rating);
                });

                star.addEventListener('mouseout', function() {
                    updateStarColors(selectedRating);
                });
            });

            function updateStarColors(rating) {
                stars.forEach((s, index) => {
                    if (index < rating) {
                        if (rating === '1' || rating === '2') {
                            s.style.color = '#FF0000';
                        } else if (rating === '3') {
                            s.style.color = '#FFB600';
                        } else if (rating === '4' || rating === '5') {
                            s.style.color = '#34C85A';
                        }
                    } else {
                        s.style.color = 'lightgray';
                    }
                });
            }
        });

        //Функция для позиционирования подвала
        function adjustFooter() {
            const footer = document.querySelector('footer');
            //Полная высота документа (включая шапки, контент и футер)
            const docHeight = document.body.scrollHeight;
            //Высота окна браузера
            const windowHeight = window.innerHeight;

            //Если документ меньше окна, фиксируем футер внизу окна
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