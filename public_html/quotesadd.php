<?php
$host="localhost";
$dbname="sadkovaann";
$password="R2UJCEw@Q";
$user="sadkovaann";

$db_connect = mysqli_connect($host, $user, $password, $dbname);
if(!$db_connect){
    die("Ошибка подключения" . mysqli_connect_error());
}

if (isset($_GET['user-quote-box']) && isset($_GET['reading-page-box'])) {
$quoteText = $_GET['user-quote-box'];
$pageNumber = $_GET['reading-page-box'];
$readId = $_GET['read-id-box'];

if ($quoteText !== '' && $pageNumber > 0) {
    $stmt = $db_connect->prepare("INSERT INTO quotes (ReadBook, Quote, Page) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $readId, $quoteText, $pageNumber);
    
    if ($stmt->execute()) {
        echo "<script>alert('Цитата успешно добавлена!');</script>";
        
        $stmt = $db_connect->prepare("SELECT Book FROM readbook WHERE ReadId = ?");
        $stmt->bind_param("i", $readId);
        $stmt->execute();
        $stmt->bind_result($book);
        
        if ($stmt->fetch()) {
            header("Location: quotes.php?bookId=$book");
            exit();
        } else {
            echo "<script>alert('Ошибка: книга не найдена.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Ошибка при добавлении цитаты: " . $stmt->error . "'); window.history.back();</script>";
    }
    
    $stmt->close();
} else {
    echo "<script>alert('Пожалуйста, заполните все поля!');</script>";
}
}
?>