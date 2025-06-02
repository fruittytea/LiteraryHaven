<?php
session_start();
if (isset($_SESSION['acc'])) {
    $acc = $_SESSION['acc'];
} else {
    header("Location: autorisation.php");
    exit();
}

if (isset($_GET['edit'])) {
    $edit = $_GET['edit'];
}

function checkGenre($genre, $acc) {
    if ($genre == 15) {
        $award = 10;
    } else {
        $award = $genre + 2;
    }

    if ($award != 0) {
        $host = "localhost";
        $dbname = "sadkovaann";
        $password = "R2UJCEw@Q";
        $user = "sadkovaann";

        $db_connect = mysqli_connect($host, $user, $password, $dbname);
        if (!$db_connect) {
            return [false, "Ошибка подключения: " . mysqli_connect_error()];
        }

        $checkAward = "SELECT * FROM awardsreceived WHERE Award = $award AND UserId = $acc";
        $stmtCheckAward = mysqli_prepare($db_connect, $checkAward);
        if (!$stmtCheckAward) {
            $err = "не удалось получить данные о ваших наградах!";
            mysqli_close($db_connect);
            return [false, $err];
        }

        if (!mysqli_stmt_execute($stmtCheckAward)) {
            $err = "не удалось получить данные о ваших наградах!";
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }

        mysqli_stmt_store_result($stmtCheckAward);
        if (mysqli_stmt_num_rows($stmtCheckAward) == 0) {
            $checkBooks = "SELECT * FROM readbook rb 
                           JOIN book b ON b.BookId = rb.Book
                           WHERE b.Genre = $genre AND rb.User = $acc";
            $stmtCheckBooks = mysqli_prepare($db_connect, $checkBooks);
            if (!$stmtCheckBooks) {
                $err = "не удалось получить данные о вашей библиотеке!";
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }

            if (!mysqli_stmt_execute($stmtCheckBooks)) {
                $err = "не удалось получить данные о вашей библиотеке!";
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }

            mysqli_stmt_store_result($stmtCheckBooks);
            if (mysqli_stmt_num_rows($stmtCheckBooks) >= 5) {
                // Выдача награды
                $rewardq = "INSERT INTO awardsreceived (Award, UserId) VALUES ($award, $acc)";
                $stmtReward = mysqli_prepare($db_connect, $rewardq);
                if (!$stmtReward) {
                    $err = "не удалось вручить награду!";
                    mysqli_stmt_close($stmtCheckBooks);
                    mysqli_stmt_close($stmtCheckAward);
                    mysqli_close($db_connect);
                    return [false, $err];
                }

                if (!mysqli_stmt_execute($stmtReward)) {
                    $err = "не удалось вручить награду!";
                    mysqli_stmt_close($stmtReward);
                    mysqli_stmt_close($stmtCheckBooks);
                    mysqli_stmt_close($stmtCheckAward);
                    mysqli_close($db_connect);
                    return [false, $err];
                }
                mysqli_stmt_close($stmtReward);

                // Обновление баллов пользователя
                $newScore = "UPDATE user SET Scores = Scores + 
                             (SELECT awards.Scores FROM awards WHERE AwardId = $award) 
                             WHERE UserId = $acc";
                $stmtNewScore = mysqli_prepare($db_connect, $newScore);
                if (!$stmtNewScore) {
                    $err = "не удалось обновить количество баллов!";
                    mysqli_stmt_close($stmtCheckBooks);
                    mysqli_stmt_close($stmtCheckAward);
                    mysqli_close($db_connect);
                    return [false, $err];
                }

                if (!mysqli_stmt_execute($stmtNewScore)) {
                    $err = "не удалось обновить количество баллов!";
                    mysqli_stmt_close($stmtNewScore);
                    mysqli_stmt_close($stmtCheckBooks);
                    mysqli_stmt_close($stmtCheckAward);
                    mysqli_close($db_connect);
                    return [false, $err];
                }
                mysqli_stmt_close($stmtNewScore);

                // Закрытие подготовленных выражений и соединения
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);

                return [true, null]; // Награда выдана и баллы обновлены
            } else {
                // Если меньше 5 прочитанных книг
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [true, null]; // Условия не выполнены, но ошибок нет
            }
        } else {
            // Если награда уже выдана
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [true, null]; // Награда уже выдана
        }
    } else {
        // Если award == 0
        return [false, "Некорректное значение награды"];
    }
}


//Мудрец
function manyBooks($acc) {
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if (!$db_connect) {
        return [false, "Ошибка подключения: " . mysqli_connect_error()];
    }

    $award = 9;

    $checkAwardSql = "SELECT * FROM awardsreceived WHERE Award = $award AND UserId = $acc";
    $stmtCheckAward = mysqli_prepare($db_connect, $checkAwardSql);
    if (!$stmtCheckAward) {
        $err = "не удалось получить сведения о наградах!";
        mysqli_close($db_connect);
        return [false, $err];
    }

    if (!mysqli_stmt_execute($stmtCheckAward)) {
        $err = "не удалось получить сведения о наградах!";
        mysqli_stmt_close($stmtCheckAward);
        mysqli_close($db_connect);
        return [false, $err];
    }

    mysqli_stmt_store_result($stmtCheckAward);
    if (mysqli_stmt_num_rows($stmtCheckAward) == 0) {
        $checkBooksSql = "SELECT * FROM readbook rb WHERE rb.User = $acc";
        $stmtCheckBooks = mysqli_prepare($db_connect, $checkBooksSql);
        if (!$stmtCheckBooks) {
            $err = "не удалось получить сведения о вашей библиотеке!";
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }

        if (!mysqli_stmt_execute($stmtCheckBooks)) {
            $err = "не удалось получить сведения о вашей библиотеке!";
            mysqli_stmt_close($stmtCheckBooks);
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }

        mysqli_stmt_store_result($stmtCheckBooks);
        if (mysqli_stmt_num_rows($stmtCheckBooks) >= 100) {
            $rewardInsertSql = "INSERT INTO awardsreceived (Award, UserId) VALUES ($award, $acc)";
            $stmtReward = mysqli_prepare($db_connect, $rewardInsertSql);
            if (!$stmtReward) {
                $err = "не удалось вручить награду!";
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }

            if (!mysqli_stmt_execute($stmtReward)) {
                $err = "не удалось вручить награду!";
                mysqli_stmt_close($stmtReward);
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }
            mysqli_stmt_close($stmtReward);

            $updateScoreSql = "UPDATE user SET Scores = Scores + (SELECT scores FROM awards WHERE AwardId = $award) WHERE UserId = $acc";
            $stmtUpdateScore = mysqli_prepare($db_connect, $updateScoreSql);
            if (!$stmtUpdateScore) {
                $err = "не удалось обновить количество баллов!";
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }

            if (!mysqli_stmt_execute($stmtUpdateScore)) {
                $err = "не удалось обновить количество баллов!";
                mysqli_stmt_close($stmtUpdateScore);
                mysqli_stmt_close($stmtCheckBooks);
                mysqli_stmt_close($stmtCheckAward);
                mysqli_close($db_connect);
                return [false, $err];
            }
            mysqli_stmt_close($stmtUpdateScore);

            mysqli_stmt_close($stmtCheckBooks);
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [true, null];
        } else {
            // Недостаточно прочитанных книг для награды
            mysqli_stmt_close($stmtCheckBooks);
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [true, null];
        }
    } else {
        // Награда уже выдана
        mysqli_stmt_close($stmtCheckAward);
        mysqli_close($db_connect);
        return [true, null];
    }
}

//Награда "Первая книга"
function firstBook($acc) {
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if (!$db_connect) {
        return [false, "Ошибка подключения: " . mysqli_connect_error()];
    }

    $checkAward = "SELECT * FROM awardsreceived WHERE Award = 2 AND UserId = $acc";
    $stmtCheckAward = mysqli_prepare($db_connect, $checkAward);
    if (!$stmtCheckAward) {
        $err = "не удалось получить сведения о наградах!";
        mysqli_close($db_connect);
        return [false, $err];
    }

    if (!mysqli_stmt_execute($stmtCheckAward)) {
        $err = "не удалось получить сведения о наградах!";
        mysqli_stmt_close($stmtCheckAward);
        mysqli_close($db_connect);
        return [false, $err];
    }

    mysqli_stmt_store_result($stmtCheckAward);
    if (mysqli_stmt_num_rows($stmtCheckAward) == 0) {
        $rewardq = "INSERT INTO awardsreceived (Award, UserId) VALUES (2, $acc)";
        $stmtReward = mysqli_prepare($db_connect, $rewardq);
        if (!$stmtReward) {
            $err = "не удалось вручить награду!";
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }

        if (!mysqli_stmt_execute($stmtReward)) {
            $err = "не удалось вручить награду!";
            mysqli_stmt_close($stmtReward);
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }
        mysqli_stmt_close($stmtReward);

        $newScore = "UPDATE user SET Scores = Scores + 5 WHERE UserId = $acc";
        $stmtNewScore = mysqli_prepare($db_connect, $newScore);
        if (!$stmtNewScore) {
            $err = "количество баллов не было обновлено!";
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }

        if (!mysqli_stmt_execute($stmtNewScore)) {
            $err = "количество баллов не было обновлено!";
            mysqli_stmt_close($stmtNewScore);
            mysqli_stmt_close($stmtCheckAward);
            mysqli_close($db_connect);
            return [false, $err];
        }
        mysqli_stmt_close($stmtNewScore);

        mysqli_stmt_close($stmtCheckAward);
        mysqli_close($db_connect);
        return [true, null];
    } else {
        //Если награда уже выдана
        mysqli_stmt_close($stmtCheckAward);
        mysqli_close($db_connect);
        return [true, null];
    }
}

//Обновление баллов
function updScores($acc){
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if (!$db_connect) {
        die("Ошибка подключения: " . mysqli_connect_error());
    }

    $queryUsScores = "UPDATE user
    SET Scores = Scores + 5
    WHERE UserId = $acc;";
    $stmtUsScores = mysqli_prepare($db_connect, $queryUsScores);
    if ($stmtUsScores) 
    {
        if (mysqli_stmt_execute($stmtUsScores)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    } 
    else 
    {
        return false;
    }
}

//Обновление статуса
function updStatus($acc){
    $host = "localhost";
    $dbname = "sadkovaann";
    $password = "R2UJCEw@Q";
    $user = "sadkovaann";

    $db_connect = mysqli_connect($host, $user, $password, $dbname);
    if (!$db_connect) {
        die("Ошибка подключения: " . mysqli_connect_error());
    }

    $queryUserStatus = "UPDATE user
    SET Status = (
        SELECT s.StatusId
        FROM status s
        WHERE user.Scores >= s.MinScores AND user.Scores <= s.MaxScores
    )
    WHERE EXISTS (
        SELECT 1
        FROM status s
        WHERE user.Scores >= s.MinScores AND user.Scores <= s.MaxScores
    )
    AND user.UserId = $acc";
    $stmtUserStatus = mysqli_prepare($db_connect, $queryUserStatus);
    if ($stmtUserStatus) 
    {
        if(mysqli_stmt_execute($stmtUserStatus)){
            if(mysqli_stmt_num_rows($stmtUserStatus) > 0)
            {
                return true;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    else {
        return false;
    }
}

$host = "localhost";
$dbname = "sadkovaann";
$password = "R2UJCEw@Q";
$user = "sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if (!$db_connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookId = $_GET['book-id-review'];
    $userId = $acc;
    $mark = (int)$_GET['rating-value-tb'];
    $comment = $_GET['user-review-box'];

    if ($mark < 1 || $mark > 5 ) {
        echo "<script>alert('Пожалуйста, поставьте оценку книге!'); window.history.back();</script>"; 
        exit();
    }
    //Добавление рецензии
    if($edit){
        $query = "UPDATE readbook SET Mark ='$mark', Comment ='$comment' WHERE Book = '$bookId' AND User ='$userId'";
        $stmt = mysqli_prepare($db_connect, $query);
        mysqli_stmt_bind_param($stmt, 'iiis', $bookId, $userId, $mark, $comment);
    }
    else {
        $query = "INSERT INTO readbook (Book, User, Mark, Comment) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connect, $query);
        mysqli_stmt_bind_param($stmt, 'iiis', $bookId, $userId, $mark, $comment);
    }

    if (mysqli_stmt_execute($stmt)) {
        //Обновление средней оценки книги
        $UpdRate = "UPDATE book
            SET AverageScore = (SELECT AVG(Mark) FROM readbook WHERE Book = ?) 
            WHERE BookId = ?;";

        $stmt1 = mysqli_prepare($db_connect, $UpdRate);
        if ($stmt1) {
            mysqli_stmt_bind_param($stmt1, 'ii', $bookId, $bookId);

            if (mysqli_stmt_execute($stmt1)) {

                $CheckGenre = "SELECT Genre FROM book WHERE BookId = $bookId";
                $stmtCheckGenre = mysqli_prepare($db_connect, $CheckGenre);
                if ($stmtCheckGenre) {
                    if (mysqli_stmt_execute($stmtCheckGenre)) {
                        mysqli_stmt_store_result($stmtCheckGenre); 
                        if (mysqli_stmt_num_rows($stmtCheckGenre) > 0) {
                            mysqli_stmt_bind_result($stmtCheckGenre, $genre);
                            mysqli_stmt_fetch($stmtCheckGenre);
                            list($success, $errorMessage) = checkGenre($genre, $acc);
                            if (!$success) {
                                echo "<script>alert('Ошибка: '".$errorMessage.");</script>";
                            }
                        } else {
                            echo "<script>alert('Ошибка при получении жанра книги')</script>"; 
                        }
                    }
                }

                    list($success, $errorMessage) = firstBook($acc);
                    if (!$success) {
                        echo "<script>alert('Ошибка: '".$errorMessage.");</script>";
                    }
                    
                    list($success, $errorMessage) = manyBooks($acc);
                    if (!$success) {
                        echo "<script>alert('Ошибка: '".$errorMessage.");</script>";
                    }

                    $functionUpdScores = updScores($acc);
                    $functionUpdStatus = updStatus($acc);

                    echo "<script>window.location.href = 'bookcard.php?id=". $bookId ."';</script>";
                }
        } 
        else 
        {
            echo "<script>alert('Ошибка при обновлении личной библиотеки'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt1);
    } 
    else {
        echo "<script>alert('Ошибка при обновлении личной библиотеки'); window.history.back();</script>";
    }

    mysqli_stmt_close($stmt);

}

mysqli_close($db_connect);
?>