<?php
//Начало сессии
session_start();
header('Content-Type: application/json');
$acc = $_SESSION['acc'];

//Подключение к бд
$host = "localhost";
$dbname = "sadkovaann";
$password = "R2UJCEw@Q";
$user = "sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if (!$db_connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
$searchTerm = mysqli_real_escape_string($db_connect, $searchTerm);

$searchMyLib = isset($_GET['mylib']) ? trim($_GET['mylib']) : '';
$mylib = isset($_GET['mylib']) ? intval($_GET['mylib']) : null;
$genreId = isset($_GET['genre']) ? intval($_GET['genre']) : null;
$otherUser = isset($_GET['otherUser']) ? intval($_GET['otherUser']) : null;

//Запрос на поиск книг по автору или названию
$query = "SELECT b.BookId, b.BookName, b.Author, b.BookImage FROM book b 
          WHERE (b.BookName LIKE '%$searchTerm%' OR b.Author LIKE '%$searchTerm%') AND b.ModerationPassed = true";
//Добавление жанра при его выборе
if ($genreId) {
    $query .= " AND b.Genre = $genreId";
}
//Добавление условия для поиска в библиотеке другого читателя
if ($otherUser) {
    $query .= " AND EXISTS (SELECT 1 FROM readbook r WHERE r.User = $otherUser AND r.Book = b.BookId)";
}
//Добавление условия для поиска в личной библиотеке
if ($mylib) {
    $query .= " AND EXISTS (SELECT 1 FROM readbook r WHERE r.User = $acc AND r.Book = b.BookId)";
}

$query .= " ORDER BY b.AverageScore DESC";

$result = mysqli_query($db_connect, $query);

$books = [];
//Формирование массива с книгами
while ($row = mysqli_fetch_assoc($result)) {
    $books[] = [
        'BookId' => $row['BookId'],
        'BookName' => $row['BookName'],
        'Author' => $row['Author'],
        'BookImage' => $row['BookImage']
    ];
}

echo json_encode($books);
?>
