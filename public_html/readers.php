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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LiteraryHaven - твой проводник в мире книг! Удобная социальная сеть для сообщества читателей.">
    <title>Рейтинг читателей</title>
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
    </header>
    
    <section class="books-section" id="preferences">
        <div id="left" class="section-bookmark" style="background-image: url('Images/Navigation/BlueSection.png'); margin-top: 2%">
            <h2 class="section-title">Рейтинг читателей</h2>
        </div>
        <?php
        //Вывод поля поиска
        if($RoleId == 1){
            echo "<input type='text' name='SearchBox' id='SearchBox' placeholder='Поиск' oninput='searchReaderAdmin()'>";
        }
        else if($RoleId == 2){
            echo "<input type='text' name='SearchBox' id='SearchBox' placeholder='Поиск' oninput='searchReader()'>";
        }
        ?>
        <div id='no-results' style="margin-top: 5%">
            <p>Упс, такого пользователя нет :(</p>
            <p class='mini-label'>Проверьте корректность введенных данных</p>
        </div>
        <center>
        <table class="readers-table">
        <tbody>
                <?php
                $subscribedUsers = []; //Массив для хранения подписанных пользователей
                $allReaders = []; //Массив для хранения всех читателей

                //Проверка подписок пользователя для изменения кнопок
                if (isset($_SESSION['acc'])) {
                    $currentUserId = $_SESSION['acc'];
                    $subscriptionQuery = "SELECT Blogger FROM subscription WHERE Subscriber = $currentUserId";
                    $subscriptionResult = mysqli_query($db_connect, $subscriptionQuery);
                    while ($row = mysqli_fetch_assoc($subscriptionResult)) {
                        $subscribedUsers[] = $row['Blogger'];
                    }
                }
                
                if($RoleId == 2)
                {
                    //Запрос на вывод пользователей у читателя
                    $q = "SELECT user.UserPhoto, 
                            user.UserId,  
                            user.Nickname, 
                            status.StatusName, 
                            status.StatusPhoto, 
                            COUNT(DISTINCT readbook.ReadId) as Books,
                            COUNT(DISTINCT subscription.Subscriber) as Fans
                    FROM user 
                    JOIN status ON status.StatusId = user.Status 
                    LEFT JOIN readbook ON readbook.User = user.UserId 
                    LEFT JOIN subscription ON subscription.Blogger = user.UserId 
                    WHERE Role = 2 and Block = false
                    GROUP BY user.UserPhoto, 
                            user.UserId, 
                            user.Nickname, 
                            status.StatusName, 
                            status.StatusPhoto 
                    ORDER BY user.Scores DESC;";
                }
                else if($RoleId=1)
                {
                    //Запрос на вывод пользователей у админа
                    $q = "SELECT user.UserPhoto, 
                            user.UserId,  
                            user.Nickname, 
                            status.StatusName, 
                            status.StatusPhoto,
                            user.Block, 
                            COUNT(DISTINCT readbook.ReadId) as Books,
                            COUNT(DISTINCT subscription.Subscriber) as Fans
                    FROM user 
                    JOIN status ON status.StatusId = user.Status 
                    LEFT JOIN readbook ON readbook.User = user.UserId 
                    LEFT JOIN subscription ON subscription.Blogger = user.UserId 
                    WHERE Role = 2 
                    GROUP BY user.UserPhoto, 
                            user.UserId, 
                            user.Nickname, 
                            status.StatusName, 
                            status.StatusPhoto 
                    ORDER BY user.Scores DESC;";
                }
                                
                $sql = mysqli_query($db_connect, $q);
                if ($sql) {
                    $rank = 1;
                    //Вывод пользователей
                    while ($readersrow = mysqli_fetch_assoc($sql)) {
                        $ReaderIcon = "Images/Profile/".$readersrow['UserPhoto'];
                        $StatusIcon = "Images/Status/".$readersrow['StatusPhoto'];
                        $Nick = "@".$readersrow['Nickname'];
                        $UsId = $readersrow['UserId'];
                        $isSubscribed = in_array($readersrow['UserId'], $subscribedUsers);
                        $isCurrentUser  = $readersrow['UserId'] == $currentUserId;
                        if($RoleId==1){
                            $isBlocked = $readersrow['Block'] == 1;
                        }
                        
                        //Сохранение данных о читателях
                        $allReaders[] = [
                            'rank' => $rank,
                            'UserId' => $readersrow['UserId'],
                            'UserPhoto' => $readersrow['UserPhoto'],
                            'Nickname' => $readersrow['Nickname'],
                            'StatusName' => $readersrow['StatusName'],
                            'StatusPhoto' => $readersrow['StatusPhoto'],
                            'Books' => $readersrow['Books'],
                            'Fans' => $readersrow['Fans'],
                            'IsSubscribed' => $isSubscribed,
                            'IsCurrentUser ' => $isCurrentUser  
                        ];

                        echo "<tr>";
                        echo "<td rowspan=2 class='rank-style'>$rank</td>";
                        echo "<td rowspan=2><img src='$ReaderIcon' alt='Читатель' class='readers-profile-icon'></td>";
                        echo "<td rowspan=2 class='reader-nick-status'><p><a href='profile.php?usprofile=$UsId'>$Nick
                        <img src='$StatusIcon' alt='Статус' title='{$readersrow['StatusName']}'></a>
                        </p></td>";
                        echo "<td id='indicators-name'><p>Книг прочитано</p></td>";
                        echo "<td id='indicators-name'><p>Подписчиков</p></td>";
                    
                        //Добавление кнопки подписки
                        if($RoleId == 2){
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
                        }
                        //Добавление кнопки блокировки
                        else if ($RoleId == 1){
                            echo "<td rowspan=2>
                                <img src='Images/Navigation/" . ($isBlocked ? 'Unblock.png' : 'Block.png') . "' 
                                    alt='Блокировка' 
                                    class='follow-reader-button' 
                                    data-user-id='" . $readersrow['UserId'] . "'
                                    onclick='BlockReader(this)'>
                            </td>";
                        }
                        echo "</tr><tr>";
                        echo "<td id='num_indicators'><p>{$readersrow['Books']}</p></td>";
                        echo "<td id='num_indicators' class='q-fans'><p>{$readersrow['Fans']}</p></td>";
                        echo "</tr>";
                        
                        $rank++;
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
        //Добавление кнопки "Добавить администратора"
        if($RoleId == 1){
            echo "<center>
            <a href='editprofile.php'>
            <button class='moder-but' style='width: auto; padding: 1%; margin: 0 0 5% 0;'>Добавить администратора</button>
            </a>
            </center>";
        }
        ?>
        <script>
            //Сохранение данных о пользователях
            const allReaders = <?php echo json_encode($allReaders); ?>;
            //Функция для поиска
            function searchReader() {
                const query = document.getElementById('SearchBox').value; //Получение текста из поля поиска
                const xhr = new XMLHttpRequest();
                //Отправка запроса в файл для поиска
                xhr.open('GET', `search_readers.php?query=${encodeURIComponent(query)}`);
                xhr.onload = function () {
                    if (xhr.status === 200) {//Успешное выполнение запроса
                        const results = JSON.parse(xhr.responseText);
                        const tableBody = document.querySelector('.readers-table tbody');
                        const noResults = document.getElementById('no-results');
                        tableBody.innerHTML = ''; //Очищение страницы

                        if (results.length === 0) {
                            noResults.style.display = 'block';
                        } else {
                            noResults.style.display = 'none'; 
                            results.forEach(reader => {
                                const originalReader = allReaders.find(r => r['UserId'] === reader.UserId);
                                const rank = originalReader ? originalReader.rank : ' ';
                                
                                const ReaderIcon = "Images/Profile/" + reader.UserPhoto;
                                const StatusIcon = "Images/Status/" + reader.StatusPhoto;
                                const Nick = "@" + reader.Nickname;
                                const UsId = reader.UserId;
                                const isSubscribed = reader.IsSubscribed;
                                const isCurrentUser  = reader.UserId == <?php echo json_encode($currentUserId); ?>;

                                const row = tableBody.insertRow();
                                //Добавление данных с учетом поиска
                                row.innerHTML = `
                                    <td rowspan=2 class='rank-style'>${rank}</td>
                                    <td rowspan=2><img src='${ReaderIcon}' alt='Читатель' class='readers-profile-icon'></td>
                                    <td rowspan=2 class='reader-nick-status'><a href='profile.php?usprofile=${UsId}'><p>${Nick}
                                    <img src='${StatusIcon}' alt='Статус' title='${reader.StatusName}'></p></a></td>
                                    <td id='indicators-name'><p>Книг прочитано</p></td>
                                    <td id='indicators-name'><p>Подписчиков</p></td>
                                    <td rowspan=2>
                                        ${!isCurrentUser  ? `<img src='Images/Navigation/${isSubscribed ? 'Follow.png' : 'Unfollow.png'}' 
                                            alt='Подписка' 
                                            class='follow-reader-button' 
                                            data-user-id='${reader.UserId}' 
                                            onclick='SubReader(this)'>` : ''}
                                    </td>
                                `;
                                const row2 = tableBody.insertRow();
                                row2.innerHTML = `
                                    <td id='num_indicators'><p>${reader.Books}</p></td>
                                    <td id='num_indicators' class='q-fans'><p>${reader.Fans}</p></td>
                                `;
                            });
                        }
                    }
                };
                xhr.send();
            }

            //Поиск пользователя администратором
            function searchReaderAdmin() {
                const query = document.getElementById('SearchBox').value; //Получение текста из поля поиска
                const xhr = new XMLHttpRequest();
                //Отправка запроса
                xhr.open('GET', `search_readers.php?query=${encodeURIComponent(query)}`);
                xhr.onload = function () {
                    if (xhr.status === 200) {//Успешное выполнение
                        const results = JSON.parse(xhr.responseText);
                        const tableBody = document.querySelector('.readers-table tbody');
                        const noResults = document.getElementById('no-results');
                        tableBody.innerHTML = ''; //Очищение страницы

                        if (results.length === 0) {
                            noResults.style.display = 'block';
                        } else {
                            noResults.style.display = 'none'; 
                            results.forEach(reader => {
                                const originalReader = allReaders.find(r => r['UserId'] === reader.UserId);
                                const rank = originalReader ? originalReader.rank : ' ';
                                
                                const ReaderIcon = "Images/Profile/" + reader.UserPhoto;
                                const StatusIcon = "Images/Status/" + reader.StatusPhoto;
                                const Nick = "@" + reader.Nickname;
                                const UsId = reader.UserId;
                                const isSubscribed = reader.IsSubscribed;
                                const isCurrentUser  = reader.UserId == <?php echo json_encode($currentUserId); ?>;
                                const isBlocked = reader.Block == 1;

                                const row = tableBody.insertRow();
                                //Добавление результатов поиска
                                row.innerHTML = `
                                    <td rowspan=2 class='rank-style'>${rank}</td>
                                    <td rowspan=2><img src='${ReaderIcon}' alt='Читатель' class='readers-profile-icon'></td>
                                    <td rowspan=2 class='reader-nick-status'><a href='profile.php?usprofile=${UsId}'><p>${Nick}
                                    <img src='${StatusIcon}' alt='Статус' title='${reader.StatusName}'></p></a></td>
                                    <td id='indicators-name'><p>Книг прочитано</p></td>
                                    <td id='indicators-name'><p>Подписчиков</p></td>
                                    <td rowspan=2>
                                        <img src='Images/Navigation/${isBlocked ? 'Unblock.png' : 'Block.png'}' 
                                        alt='Блокировка' 
                                        class='follow-reader-button' 
                                        data-user-id='${reader.UserId}'
                                        onclick='BlockReader(this)'>
                                    </td>
                                `;
                                const row2 = tableBody.insertRow();
                                row2.innerHTML = `
                                    <td id='num_indicators'><p>${reader.Books}</p></td>
                                    <td id='num_indicators' class='q-fans'><p>${reader.Fans}</p></td>
                                `;
                            });
                        }
                    }
                };
                xhr.send();
            }

            //Блокировка пользователя
            function BlockReader(button) {
                const user = button.getAttribute('data-user-id');
                const isBlocked = button.src.includes('Unblock.png');
                //Окно подтверждения блокировки или разблокировки
                const confirmationMessage = isBlocked ? "Вы хотите разблокировать пользователя?" : "Вы хотите заблокировать пользователя?";
                if (!confirm(confirmationMessage)) {
                    return; //Прерывание выполнения функции при выборе "Нет"
                }
                const xhr = new XMLHttpRequest();
                xhr.open('POST', isBlocked ? 'unblockUser.php' : 'blockUser.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            //Изменение изображения кнопки
                            button.src = response.BlockStatus ? 'Images/Navigation/Unblock.png' : 'Images/Navigation/Block.png';
                        } else {
                            console.error("Ошибка при изменении статуса блокировки:", response.message);
                        }
                    } else {
                        console.error('Ошибка при выполнении запроса:', xhr.statusText);
                    }
                };
                xhr.onerror = function() {
                    console.error('Ошибка сети');
                };
                xhr.send(`user=${user}`);
            }

            //Подписка на пользователя
            function SubReader(button) {
                const userId = button.getAttribute('data-user-id');
                const isSubscribed = button.src.includes('Follow.png');

                const xhr = new XMLHttpRequest();
                //Отправка запроса в зависимости от действия
                xhr.open('POST', isSubscribed ? 'unsubscribe.php' : 'subscribe.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    if (xhr.status === 200) {//Успешное выполнение
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            //Изменение кнопки
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
            const docHeight = document.body.scrollHeight;
            const windowHeight = window.innerHeight;

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
