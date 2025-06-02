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

$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
}

if (isset($_GET['mylib'])) {
    $mylib = $_GET['mylib'];
}
if (isset($_GET['otherUser'])) {
    $otherUser = (int)$_GET['otherUser'];
}
if (isset($_GET['preferences'])){
    $preferences = (int)$_GET['preferences'];
}
if (isset($_GET['newbooks'])){
    $newbooks = (int)$_GET['newbooks'];
}
if(isset($_GET['popular'])){
    $popular = (int)$_GET['popular'];
}
if(isset($_GET['selectedGenre'])){
    $selectedGenre = (int)$_GET['selectedGenre'];
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <?php
    if($mylib==1){
        echo "<title>Моя библиотека</title>";
    }
    else if($otherUser){
        echo "<title>Библиотека пользователя</title>";
    }
    else if($preferences){
        echo "<title>Книги по предпочтениям</title>";
    }
    else if($newbooks){
        echo "<title>Новинки на сайте</title>";
    }
    else if($popular){
        echo "<title>Популярные книги</title>";
    }
    else{
        echo "<title>Полная библиотека</title>";
    }
    ?>
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
    <section class="books-section" id="preferences">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/PinkSection.png'); margin-top: 2%">
            <?php
            if($mylib==1){
                echo "<h2 class='section-title'>Моя библиотека</h2>";
            }
            else if($otherUser){
                echo "<h2 class='section-title'>Библиотека пользователя</h2>";
            }
            else if($preferences){
                echo "<h2 class='section-title'>Книги по вашим предпочтениям</h2>";
            }
            else if($newbooks){
                echo "<h2 class='section-title'>Новинки</h2>";
            }
            else if($popular){
                echo "<h2 class='section-title'>Популярно</h2>";
            }
            else{
                echo "<h2 class='section-title'>Полная библиотека</h2>";
            }
            ?>
        </div>
        <?php
            if(!$preferences && !$newbooks && !$popular){
                echo '<input type="text" name="SearchBox" id="SearchBox" placeholder="Поиск" oninput="searchBooks()">
                <select id="genreSelect" onchange="searchBooks()" title="Фильтр по жанрам">
                    <option value="">Все жанры</option>';
                    foreach ($genres as $genre) {
                        $selected = (isset($selectedGenre) && $selectedGenre == $genre['GenreId']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($genre['GenreId']) . '" ' . $selected . '>' . htmlspecialchars($genre['GenreName']) . '</option>';
                    }
                echo '</select>';
                if($RoleId == 1){
                    echo "<a href='moderbook.php'><button class='moder-but'>Книги на модерации</button></a>";
                }
                }
        ?>
        <div class="book-cards" id="bookCards" style="margin: 0 15%;">
            <?php
                if($mylib == 1){
                    $q = "SELECT BookId, BookName, Author, BookImage FROM book b 
                    JOIN readbook rb ON rb.Book = b.BookId
                    WHERE rb.User = $acc AND b.ModerationPassed = true
                    ORDER BY AverageScore DESC";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
                        $images = [
                            'Images/Books/BlueBook.png',
                            'Images/Books/PinkBook.png',
                            'Images/Books/YellowBook.png'
                        ];
                        
                        $imageCount = count($images);
                        $index = 0;
                        if ($sql && mysqli_num_rows($sql) > 0){
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
                            <p>Вы ещё не добавили книги в прочитанное!</p>
                            <a href='library.php' class='add-book-button'>
                                <button>Перейти в библиотеку</button>
                            </a>
                            </div>";
                        }
                    }
                }
                else if($otherUser){
                    $q = "SELECT BookId, BookName, Author, BookImage FROM book b 
                    JOIN readbook rb ON rb.Book = b.BookId
                    WHERE rb.User = $otherUser AND b.ModerationPassed = true
                    ORDER BY AverageScore DESC";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
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
                    }
                }
                else if($preferences){
                    $q = "SELECT b.BookId, b.BookName, b.Author, b.BookImage FROM book b 
                            WHERE b.Genre IN ( 
                                SELECT DISTINCT b2.Genre FROM readbook r 
                                JOIN book b2 ON b2.BookId = r.Book WHERE r.User = $acc AND r.Mark > 4.0 
                            ) 
                            AND b.BookId NOT IN ( 
                                SELECT r2.Book FROM readbook r2 
                                WHERE r2.User = $acc 
                            ) 
                            AND b.ModerationPassed = true
                            LIMIT 20";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
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
                    }
                }
                else if($newbooks){
                    $q = "SELECT BookId, BookName, Author, BookImage FROM book 
                    WHERE ModerationPassed = true
                    ORDER BY BookId DESC 
                    LIMIT 20";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
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
                    }
                }
                else if($popular){
                    $q = "SELECT BookId, BookName, Author, BookImage FROM book 
                            JOIN readbook ON book.BookId = readbook.Book 
                            WHERE AverageScore >= 4.0 AND AverageScore <= 5.0 AND ModerationPassed = true 
                            GROUP BY BookName, Author 
                            ORDER BY COUNT(readbook.Book) DESC, 
                            AverageScore DESC 
                            LIMIT 20;";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
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
                    }
                }
                else{
                    $q = "SELECT BookId, BookName, Author, BookImage FROM book 
                    WHERE ModerationPassed = true
                    ORDER BY AverageScore DESC";
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
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
                    }
                }
            ?>
        </div>
        <?php
        if($mylib==1){
            echo "<div id='no-results'>
                <p>Упс, такой книги еще нет в твоей библиотеке :( </p>
                <p class='mini-label'>Найди книгу в библиотеке!</p>
                <a href='library.php' class='add-book-button'>
                    <button>Перейти в полную библиотеку</button>
                </a>
            </div>";
        }
        else if($otherUser){
            echo "<div id='no-results'>
                <p style='margin: 5% 0'>В библиотеке пользователя еще нет такой книги</p>
            </div>";
        }
        else{
            echo "<div id='no-results'>
                <p>Упс, такой книги еще нет в библиотеке :( </p>
                <p class='mini-label'>Добавь книгу самостоятельно!</p>
                <a href='newbook.php' class='add-book-button'>
                    <button>Добавить книгу</button>
                </a>
            </div>";
        }
        ?>
    </section>
    <script>
        const selectGenre = <?php echo isset($selectedGenre) ? json_encode($selectedGenre) : 'null'; ?>;
        function searchBooks() {
            const searchTerm = document.getElementById('SearchBox').value;
            const selectedGenre = document.getElementById('genreSelect').value;
            const mylib = "<?php echo $mylib; ?>";
            const uslib = "<?php echo $uslib; ?>";
            const otherUser = "<?php echo $otherUser; ?>";
            const images = [
                'BlueBook.png',
                'PinkBook.png',
                'YellowBook.png',
                'PinkBook.png'
            ];
            let index = 0;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search.php?term=' + encodeURIComponent(searchTerm) + '&genre=' + encodeURIComponent(selectedGenre) 
                    + '&mylib=' + encodeURIComponent(mylib) + '&otherUser=' + encodeURIComponent(otherUser), true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const results = JSON.parse(xhr.responseText);
                    const bookCards = document.getElementById('bookCards');
                    const noResults = document.getElementById('no-results');
                    bookCards.innerHTML = '';

                    if (results.length === 0) {
                        noResults.style.display = 'block';
                    } else {
                        noResults.style.display = 'none';
                        results.forEach(book => {
                            const bookCard = document.createElement('a');
                            bookCard.href = `bookcard.php?id=${book.BookId}&otherUser=${otherUser}`;
                            bookCard.className = 'book-card';

                            const bookCover = book.BookImage
                                ? `Images/Books/${book.BookImage}`
                                : `Images/Books/${images[index]}`; 

                            bookCard.innerHTML = `
                                <img src='${bookCover}' alt='Обложка'>
                                <p id='book-name-p'>${book.BookName}</p>
                                <p id='author-name-p'>${book.Author}</p>
                            `;
                            bookCards.appendChild(bookCard);

                            index++;
                            if (index >= images.length) {
                                index = 0;
                            }
                        });
                    }
                }
            };
            xhr.send();
        }
        if (selectGenre) {
            //document.getElementById('genreSelect').value = selectGenre;
            searchBooks();
        }

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