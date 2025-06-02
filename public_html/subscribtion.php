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

if (isset($_GET['otherUser'])) {
    $otherUser = $_GET['otherUser'];
}
if (isset($_GET['myfan'])) {
    $myfan = $_GET['myfan'];
}
if (isset($_GET['mysub'])) {
    $mysub = $_GET['mysub'];
}
if(!$otherUser && $myfan && $mysub){
    echo "<script>window.history.back();</script>";
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <?php
            if($otherUser){
                if($myfan){
                    echo "<title>Подписчики пользователя</title>";
                }
                else if($mysub){
                    echo "<title>Подписки пользователя</title>";
                }
            }
            else if($myfan){
                echo "<title>Мои подписчики</title>";
            }
            else if($mysub){
                echo "<title>Мои подписки</title>";
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
                <a href="library.php" style="margin-bottom: 1%">Полная библиотека</a><br>
                <a href="readers.php">Рейтинг читателей</a>
            </div>
        </div>
    </header>
    
    <section class="books-section" id="preferences">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/BlueSection.png'); margin-top: 2%">
        <?php
            if($otherUser){
                if($myfan){
                    echo "<h2 class='section-title'>Подписчики пользователя</h2>";
                }
                else if($mysub){
                    echo "<h2 class='section-title'>Подписки пользователя</h2>";
                }
            }
            else if($myfan){
                echo "<h2 class='section-title'>Мои подписчики</h2>";
            }
            else if($mysub){
                echo "<h2 class='section-title'>Мои подписки</h2>";
            }
        ?>
        </div>
        <center>
                <?php
                $subscribedUsers = [];
                if (isset($_SESSION['acc'])) {
                    $currentUserId = $_SESSION['acc'];
                    $subscriptionQuery = "SELECT Blogger FROM subscription WHERE Subscriber = $currentUserId";
                    $subscriptionResult = mysqli_query($db_connect, $subscriptionQuery);
                    while ($row = mysqli_fetch_assoc($subscriptionResult)) {
                        $subscribedUsers[] = $row['Blogger'];
                    }
                }
                if($otherUser){
                    if($myfan){
                        $q = "SELECT u.UserId, u.UserPhoto, u.Nickname, s.StatusPhoto, s.StatusName, COUNT(rb.Book) AS Books
                                FROM subscription AS sub
                                JOIN user AS u ON sub.Subscriber = u.UserId
                                LEFT JOIN status AS s ON u.Status = s.StatusId
                                LEFT JOIN readbook AS rb ON u.UserId = rb.User
                                WHERE sub.Blogger = $otherUser and Block = false
                                GROUP BY u.UserId";

                        $sql = mysqli_query($db_connect, $q);
                        if ($sql) {
                            if (mysqli_num_rows($sql) > 0) {
                                while ($readersrow = mysqli_fetch_assoc($sql)) {
                                    $ReaderIcon = "Images/Profile/".$readersrow['UserPhoto'];
                                    $StatusIcon = "Images/Status/".$readersrow['StatusPhoto'];
                                    $Nick = "@".$readersrow['Nickname'];
                                    $UsId = $readersrow['UserId'];
                                    $isSubscribed = in_array($readersrow['UserId'], $subscribedUsers);
                                    $isCurrentUser  = $readersrow['UserId'] == $currentUserId;
                                    
                                    echo "<table class='readers-table'>
                                        <tbody>";
                                    echo "<tr>";
                                    echo "<td rowspan=2><img src='$ReaderIcon' alt='Читатель' class='readers-profile-icon'></td>";
                                    echo "<td rowspan=2 class='reader-nick-status'><p><a href='profile.php?usprofile=$UsId'>$Nick
                                    <img src='$StatusIcon' alt='Статус' title='{$readersrow['StatusName']}'></a>
                                    </p></td>";
                                    echo "<td id='indicators-name'><p>Книг прочитано</p></td>";
                                
                                    if (!$isCurrentUser ) {
                                        echo "<td rowspan=2>
                                        <img src='Images/Navigation/" . ($isSubscribed ? 'Follow.png' : 'Unfollow.png') . "' 
                                            alt='Подписка' 
                                            class='follow-reader-button' 
                                            data-user-id='" . $readersrow['UserId'] . "'
                                            onclick='SubReader(this)'>
                                        </td>";
                                    } else {
                                        echo "<td rowspan=2>
                                        <img src='Images/Navigation/YourProfile.png' 
                                            class='follow-reader-button' 
                                            data-user-id='" . $readersrow['UserId'] . "'>
                                        </td>";
                                    }
                                    echo "</tr><tr>";
                                    echo "<td id='num_indicators'><p>{$readersrow['Books']}</p></td>";
                                    echo "</tr>";
                                }
                            }
                            else{
                                echo "<div class='no-sub'>
                                    <p>У пользователя пока нет подписчиков :(</p>
                                </div>";
                            }
                        }
                    }
                    else if ($mysub){
                        $q = "SELECT u.UserId, u.UserPhoto, u.Nickname, s.StatusPhoto, s.StatusName, COUNT(rb.Book) AS Books
                            FROM subscription AS sub
                            JOIN user AS u ON sub.Blogger = u.UserId
                            LEFT JOIN status AS s ON u.Status = s.StatusId
                            LEFT JOIN readbook AS rb ON u.UserId = rb.User
                            WHERE sub.Subscriber = $otherUser and Block = false
                            GROUP BY u.UserId";
                                            
                        $sql = mysqli_query($db_connect, $q);
                        if ($sql) {
                            if (mysqli_num_rows($sql) > 0) {
                                while ($readersrow = mysqli_fetch_assoc($sql)) {
                                    $ReaderIcon = "Images/Profile/".$readersrow['UserPhoto'];
                                    $StatusIcon = "Images/Status/".$readersrow['StatusPhoto'];
                                    $Nick = "@".$readersrow['Nickname'];
                                    $UsId = $readersrow['UserId'];
                                    $isSubscribed = in_array($readersrow['UserId'], $subscribedUsers);
                                    $isCurrentUser  = $readersrow['UserId'] == $currentUserId;
                                    
                                    echo "<table class='readers-table'>
                                        <tbody>";
                                    echo "<tr>";
                                    echo "<td rowspan=2><img src='$ReaderIcon' alt='Читатель' class='readers-profile-icon'></td>";
                                    echo "<td rowspan=2 class='reader-nick-status'><p><a href='profile.php?usprofile=$UsId'>$Nick
                                    <img src='$StatusIcon' alt='Статус' title='{$readersrow['StatusName']}'></a>
                                    </p></td>";
                                    echo "<td id='indicators-name'><p>Книг прочитано</p></td>";
                                
                                    //Добавление кнопки подписки
                                    if (!$isCurrentUser ) {
                                        echo "<td rowspan=2>
                                        <img src='Images/Navigation/" . ($isSubscribed ? 'Follow.png' : 'Unfollow.png') . "' 
                                            alt='Подписка' 
                                            class='follow-reader-button' 
                                            data-user-id='" . $readersrow['UserId'] . "'
                                            onclick='SubReader(this)'>
                                        </td>";
                                    } else {
                                        echo "<td rowspan=2></td>";
                                    }
                                    echo "</tr><tr>";
                                    echo "<td id='num_indicators'><p>{$readersrow['Books']}</p></td>";
                                    echo "</tr>";
                                }
                            }
                            else{
                                echo "<div class='no-sub'>
                                    <p>У пользователя нет подписок :(</p>
                                </div>";
                            }
                        }
                    }
                    else{
                        echo "<script>window.history.back();</script>";
                    }
                }
                else if($myfan){
                    $q = "SELECT u.UserId, u.UserPhoto, u.Nickname, s.StatusPhoto, s.StatusName, COUNT(rb.Book) AS Books
                            FROM subscription AS sub
                            JOIN user AS u ON sub.Subscriber = u.UserId
                            LEFT JOIN status AS s ON u.Status = s.StatusId
                            LEFT JOIN readbook AS rb ON u.UserId = rb.User
                            WHERE sub.Blogger = $acc and Block = false
                            GROUP BY u.UserId";

                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
                        if (mysqli_num_rows($sql) > 0) {
                            while ($readersrow = mysqli_fetch_assoc($sql)) {
                                $ReaderIcon = "Images/Profile/".$readersrow['UserPhoto'];
                                $StatusIcon = "Images/Status/".$readersrow['StatusPhoto'];
                                $Nick = "@".$readersrow['Nickname'];
                                $UsId = $readersrow['UserId'];
                                $isSubscribed = in_array($readersrow['UserId'], $subscribedUsers);
                                $isCurrentUser  = $readersrow['UserId'] == $currentUserId;
                                
                                echo "<table class='readers-table'>
                                        <tbody>";
                                echo "<tr>";
                                echo "<td rowspan=2><img src='$ReaderIcon' alt='Читатель' class='readers-profile-icon'></td>";
                                echo "<td rowspan=2 class='reader-nick-status'><p><a href='profile.php?usprofile=$UsId'>$Nick
                                <img src='$StatusIcon' alt='Статус' title='{$readersrow['StatusName']}'></a>
                                </p></td>";
                                echo "<td id='indicators-name'><p>Книг прочитано</p></td>";
                            
                                if (!$isCurrentUser ) {
                                    echo "<td rowspan=2>
                                    <img src='Images/Navigation/" . ($isSubscribed ? 'Follow.png' : 'Unfollow.png') . "' 
                                        alt='Подписка' 
                                        class='follow-reader-button' 
                                        data-user-id='" . $readersrow['UserId'] . "'
                                        onclick='SubReader(this)'>
                                    </td>";
                                } else {
                                    echo "<td rowspan=2></td>";
                                }
                                echo "</tr><tr>";
                                echo "<td id='num_indicators'><p>{$readersrow['Books']}</p></td>";
                                echo "</tr>";
                                
                            }
                        }
                        else{
                            echo "<div class='no-sub'>
                                <p>У вас ещё нет подписчиков :(</p>
                            </div>";
                        }
                    }
                }
                else if($mysub){
                    $q = "SELECT u.UserId, u.UserPhoto, u.Nickname, s.StatusPhoto, s.StatusName, COUNT(rb.Book) AS Books
                        FROM subscription AS sub
                        JOIN user AS u ON sub.Blogger = u.UserId
                        LEFT JOIN status AS s ON u.Status = s.StatusId
                        LEFT JOIN readbook AS rb ON u.UserId = rb.User
                        WHERE sub.Subscriber = $acc and Block = false
                        GROUP BY u.UserId";
                                        
                    $sql = mysqli_query($db_connect, $q);
                    if ($sql) {
                        if (mysqli_num_rows($sql) > 0) {
                            while ($readersrow = mysqli_fetch_assoc($sql)) {
                                $ReaderIcon = "Images/Profile/".$readersrow['UserPhoto'];
                                $StatusIcon = "Images/Status/".$readersrow['StatusPhoto'];
                                $Nick = "@".$readersrow['Nickname'];
                                $UsId = $readersrow['UserId'];
                                $isSubscribed = in_array($readersrow['UserId'], $subscribedUsers);
                                $isCurrentUser  = $readersrow['UserId'] == $currentUserId;
                                
                                echo "<table class='readers-table'>
                                        <tbody>";
                                echo "<tr>";
                                echo "<td rowspan=2><img src='$ReaderIcon' alt='Читатель' class='readers-profile-icon'></td>";
                                echo "<td rowspan=2 class='reader-nick-status'><p><a href='profile.php?usprofile=$UsId'>$Nick
                                <img src='$StatusIcon' alt='Статус' title='{$readersrow['StatusName']}'></a>
                                </p></td>";
                                echo "<td id='indicators-name'><p>Книг прочитано</p></td>";
                            
                                if (!$isCurrentUser ) {
                                    echo "<td rowspan=2>
                                    <img src='Images/Navigation/" . ($isSubscribed ? 'Follow.png' : 'Unfollow.png') . "' 
                                        alt='Подписка' 
                                        class='follow-reader-button' 
                                        data-user-id='" . $readersrow['UserId'] . "'
                                        onclick='SubReader(this)'>
                                    </td>";
                                } else {
                                    echo "<td rowspan=2></td>";
                                }
                                echo "</tr><tr>";
                                echo "<td id='num_indicators'><p>{$readersrow['Books']}</p></td>";
                                echo "</tr>";
                            }
                        }
                        else{
                            echo "<div class='no-sub'>
                                <p>У вас ещё нет отслеживаемых пользователей :(</p>
                            </div>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
        <script>
            function SubReader(button) {
                const userId = button.getAttribute('data-user-id');
                const isSubscribed = button.src.includes('Follow.png');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', isSubscribed ? 'unsubscribe.php' : 'subscribe.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            button.src = isSubscribed ? 'Images/Navigation/Unfollow.png' : 'Images/Navigation/Follow.png';
                            
                            const fansCountCell = button.closest('tr').nextElementSibling.querySelector('.q-fans p');
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

            xhr.send(`userId=${userId}`);
        }
        </script>
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