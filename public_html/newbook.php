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
}
//Проверка получения информации о изменении книги
if (isset($_GET['BookId']) && isset($_GET['edit'])) {
    $BookId = $_GET['BookId'];
    $Edit = $_GET['edit'];
    //Получение информации об изменяемой книге
    $qEditBook = "SELECT * FROM book WHERE BookId = $BookId";
    $sqlEditBook = mysqli_query($db_connect, $qEditBook);
    if ($sqlEditBook) {
        if (mysqli_num_rows($sqlEditBook) > 0) {
            //Заполнение информацией о книге
            $bookData = mysqli_fetch_assoc($sqlEditBook);
        } else {
            echo "Книга с ID $BookId не найдена.";
        }
    } else {
        echo "Ошибка выполнения запроса: " . mysqli_error($db_connect);
    }
}

//Получение жанров
$genreQuery = "SELECT GenreId, GenreName FROM genre";
$genreResult = mysqli_query($db_connect, $genreQuery);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <?php
    //Изменение названия страницы в зависимости от предназначения
    if ($Edit){
        echo "<title>Обновление карточки книги</title>";
    }
    else{
        echo "<title>Новая книга</title>";
    }
    ?>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
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
    <section class='select-book-rewiew' style='margin: 0;'>
    <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/BlueSection.png'); margin-top: 2%">
        <?php
        //Вывод заголовка страницы в зависимости от предназначения
        if ($Edit){
            echo "<h2 class='section-title'>Книга</h2>";
        }
        else{
            echo "<h2 class='section-title'>Новая книга</h2>";
        }
        ?>
    </div>
    <center>
        <table style="margin-bottom: 3%;">
            <tr>
                <td style='padding-right: 40px'>
                    <?php
                        //Заглушки обложек
                        $bookImages = ["Images/Books/BlueBook.png", "Images/Books/PinkBook.png", "Images/Books/YellowBook.png"];
                        $randomIndex = array_rand($bookImages);
                        $SelBookImg = $bookImages[$randomIndex];

                        if (isset($bookData['BookImage'])) {
                            $SelBookImg = "Images/Books/".$bookData['BookImage'];
                        }
                        
                        echo "<center><img class='new-review-book-img' id='new-review-book-img' src='$SelBookImg' style='margin-bottom:3%'><br>";
                    ?>
                        <label for="book-img-tb" class="add-img-book" onchange="previewImage(event)">Загрузить обложку книги</label>
                    </center>
                </td>
                <td>
                    <center>
                        <form action='newreadbook.php' method='POST' class='new-read-book-user' enctype="multipart/form-data"> 
                            <?php if ($Edit): ?>
                                <input type="hidden" name="edit" value="1">
                            <?php endif; ?>
                            <?php if ($Edit): ?>
                                <input type="hidden" name="BookId" value="<?php echo $BookId; ?>">
                            <?php endif; ?>
                            <input type="text" name="book-descr-tb" id="book-descr-tb" style="display: none;" value="<?php echo isset($bookData['BookDescription']) ? htmlspecialchars($bookData['BookDescription']) : ''; ?>">
                            <label for="user-bname-box">Название книги</label><br>
                            <input type="text" required name="user-bname-box" id="user-bname-box" value="<?php echo isset($bookData['BookName']) ? htmlspecialchars($bookData['BookName']) : ''; ?>"><br>
                            <label for="user-aname-box">Автор книги</label><br>
                            <input type="text" required name="user-aname-box" id="user-bname-box" value="<?php echo isset($bookData['Author']) ? htmlspecialchars($bookData['Author']) : ''; ?>"><br>
                            <label for="book-descr-box">Описание книги</label><br>
                            <textarea id="book-descr-box" name="book-descr-box" placeholder="Напишите краткое описание книги" onchange="descriptionRead()"><?php echo isset($bookData['BookDescription']) ? htmlspecialchars($bookData['BookDescription']) : ''; ?></textarea><br>
                            <label for="genre-select">Жанр книги</label><br>
                            <select name="genre-select" id="genre-select" required onchange="selectedGenre()">
                                <option value="">Выберите жанр</option>
                                <?php
                                if ($genreResult) {
                                    while ($row = mysqli_fetch_assoc($genreResult)) {
                                        $selected = (isset($bookData['Genre']) && $bookData['Genre'] == $row['GenreId']) ? 'selected' : '';
                                        echo "<option value='" . $row['GenreId'] . "' $selected>" . $row['GenreName'] . "</option>";
                                    }
                                }
                                ?>
                            </select><br><br>
                            <input type="text" name="sel-genre" id="sel-genre" style="display: none;" value="<?php echo isset($bookData['Genre']) ? htmlspecialchars($bookData['Genre']) : ''; ?>">
                            <input type="file" name="book-img-tb" id="book-img-tb" style="display: none;" onchange="previewImage(event)">
                            <?php
                            if ($Edit){
                                echo "<input type='submit' id='AddNewReading' value='Обновить карточку книги'>";
                            }
                            else{
                                echo "<input type='submit' id='AddNewReading' value='Сохранить карточку книги'>";
                            }
                            ?>
                        </form>
                    </center>
                </td>
            </tr>
        </table>
    </center>
    </section>
    <script>
        //Функция добавления изображения
        function previewImage(event) {
            //Получение изображения из файла
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('new-review-book-img').src = e.target.result; //Вывод изображения
            }
            reader.readAsDataURL(file);
        }
        //Функция добавления описания
        function descriptionRead(){
            const descr = document.getElementById('book-descr-box').value;
            document.getElementById('book-descr-tb').value = descr;
        }
        //Функция выбора жанра
        function selectedGenre(){
            const genresel = document.getElementById('genre-select').value;
            document.getElementById('sel-genre').value = genresel;
        }
    
        //Функция для позиционирования подвала
        function adjustFooter() {
            const footer = document.querySelector('footer');
            //Полная высота документа
            const docHeight = document.body.scrollHeight;
            //Высота окна браузера
            const windowHeight = window.innerHeight;

            //Если документ меньше окна, фиксируем подвал внизу окна
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
