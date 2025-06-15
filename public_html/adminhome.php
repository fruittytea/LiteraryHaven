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
    <title>Главная страница</title>
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Images/Logo/icon.png" type="image/png">
<?php
//Подключение к бд
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

//Проверка роли пользователя
$roleCheck = "SELECT Role FROM user WHERE UserId = $acc";
$roleCheckSql = mysqli_query($db_connect, $roleCheck);

if ($roleCheckSql && mysqli_num_rows($roleCheckSql) > 0) {
    $rowRole = mysqli_fetch_assoc($roleCheckSql);
    $RoleId = $rowRole['Role'];
    if($RoleId == 2){
        header("Location: index.php");
    }
}
?>
<script>
    //Функция для проверки размера экрана устройства
    function checkScreenWidth() {
      const minWidth = 1200; //Минимальный размер устройства
      const blockedMessage = document.getElementById('blocked-message'); //Сообщение о несоответствии размера экрана
      const pageContent = document.getElementById('page-content'); //Контент на странице
      if(window.innerWidth < minWidth) {
        alert ("Извините, но панель администратора не доступна на мобильных устройствах в связи с ограничением функционала!");
        window.history.back();
      }
    }

    window.addEventListener('load', checkScreenWidth);
    window.addEventListener('resize', checkScreenWidth);
</script>
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
                
                echo "<a href='adminhome.php'>
                        <img src='$profimage' alt='Профиль' class='profile-icon'>
                    </a>";
            } else {
                echo "<a href='adminhome.php'>
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
    <section class='admin-home-block'>
        <a href="library.php">
            <div class="admin-home-section" style="background-color: #FFCC4C;">
                <img src="Images/Navigation/book_section.png" class="admin-section-img" alt="Книги">
                <p>Полная библиотека</p>
            </div>
        </a>
        <a href="readers.php" >
            <div style="background-color: #8893CD;" class="admin-home-section-right">
                <p>Список пользователей</p>
                <img src="Images/Navigation/reader_section.png" class="admin-section-img" alt="Пользователи">
            </div>
        </a>
        <a href="allreview.php" >
            <div style="background-color: #FF87B9;" class="admin-home-section">
                <img src="Images/Navigation/review_section.png" class="admin-section-img" alt="Рецензии">
                <p>Рецензии пользователей</p>
            </div>
        </a>
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
