<?php
//Начало сессии
session_start();
//Проверка активной сессии
if (isset($_SESSION['acc'])) {
    $acc = $_SESSION['acc'];
} elseif (isset($_GET['acc'])) { //Получение id пользователя для начала сессии
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

if($RoleId == 2){
    //Получение информации о пользователе
    $usQuery = "SELECT * FROM user WHERE UserId = $acc";
    $usResult = mysqli_query($db_connect, $usQuery);
    if ($usResult) {
        $userData = mysqli_fetch_assoc($usResult);
    } else {
        die("Ошибка выполнения запроса: " . mysqli_error($db_connect));
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
    //Вывод названия страницы в зависимости от роли
    if($RoleId == 2){
        echo "<title>Редактирование профиля</title>";
    }
    else if ($RoleId == 1){
        echo "<title>Новый администратор</title>";
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
    <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/YellowSection.png'); margin-top: 2%">
        <?php
        //Вывод заголовка в зависимости от роли
        if($RoleId == 2){
            echo "<h2 class='section-title'>Редактирование профиля</h2>";
        }
        else if ($RoleId == 1){
            echo "<h2 class='section-title'>Новый администратор</h2>";
        }
        ?>
    </div>
    <center>
        <table style="margin-bottom: 3%;">
            <tr>
                <?php
                //Вывод формы при роли "Читатель"
                if($RoleId == 2){
                    echo "<td style='padding-right: 40px'>";
                    $bookImages = "Images/Profile/" . $userData['UserPhoto'];
                    $SelBookImg = $bookImages;
                    echo "<center><img class='us-prof-img' id='us-prof-img' src='$SelBookImg' style='margin-bottom:3%'><br>";
                    echo "<label for='us-img-tb' class='add-img-book' onchange='previewImage(event)'>Загрузить аватарку</label>
                    </center>
                    </td>";
                }
                ?>
                <td>
                    <center>
                        <form action='profileeditcode.php' method='POST' class='new-read-book-user' enctype="multipart/form-data"> 
                            <?php
                            if($RoleId == 2){
                                echo "<label for='user-nname-box'>Никнейм</label><br>
                                <input type='text' readonly name='user-nname-box' id='user-nname-box' value='{$userData['Nickname']}'>
                                <br>";
                            }
                            ?>
                            <label for="user-sname-box">Фамилия</label><br>
                            <input type="text" required name="user-sname-box" id="user-sname-box" value='<?php if($RoleId == 2) { echo $userData['Surname'];}?>'><br>
                            <label for="user-name-box">Имя</label><br>
                            <input type="text" required name="user-name-box" id="user-name-box" value='<?php if($RoleId == 2) { echo $userData['Name'];}?>'><br>
                            <label for="user-fname-box">Отчество</label><br>
                            <input type="text" required name="user-fname-box" id="user-fname-box" value='<?php if($RoleId == 2) { echo $userData['Fathername'];}?>'><br>
                            <label for="user-ph-box">Телефон</label><br>
                            <input type="text" <?php if($RoleId == 2){ echo 'readonly';}?> name="user-ph-box" id="user-ph-box" value='<?php if($RoleId == 2) { echo $userData['Phone'];}?>'><br>
                            <label for="user-mail-box">Email</label><br>
                            <input type="text" required name="user-mail-box" id="user-mail-box" value='<?php if($RoleId == 2) { echo $userData['Email'];}?>'><br>
                            <label for="user-pass-box">Пароль</label><br>
                            <input type="text" required name="user-pass-box" id="user-pass-box" value='<?php if($RoleId == 2) { echo $userData['Password'];}?>'><br>
                            <input type="file" name="us-img-tb" id="us-img-tb" style="display: none;" onchange="previewImage(event)">
                            <input type="submit" id='AddNewReading' value="Сохранить изменения">
                        </form>
                    </center>
                </td>
            </tr>
        </table>
    </center>
    </section>
    <script>
        //Функция предварительного просмотра выбранного изображения
        function previewImage(event) {
            //Получение изображения из файла
            const file = event.target.files[0];
            //Чтение файла
            const reader = new FileReader();
            reader.onload = function(e) {
                //Вывод изображения
                document.getElementById('us-prof-img').src = e.target.result;
            }
            //Чтение файла и его конвертация
            reader.readAsDataURL(file);
        }
    
        //Функция для позиционирования подвала
        function adjustFooter() {
            const footer = document.querySelector('footer');
            //Полная высота документа
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
