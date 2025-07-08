<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST['application_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $comment_text = $_POST['comment'] ?? '';
    $rating = $_POST['rating'] ?? null;
    $status = 'active'; 

    if ($application_id && is_numeric($application_id) && !empty($name) && !empty($comment_text)) {
        
        $stmt = $conn->prepare("INSERT INTO Comments (application_id, name, comment, rating, status, created, modified) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("issss", $application_id, $name, $comment_text, $rating, $status);

        if ($stmt->execute()) {
            header("Location: view_application.php?id=" . urlencode($application_id) . "&message=Comment+added+successfully!");
        } else {
            header("Location: view_application.php?id=" . urlencode($application_id) . "&error=Error+adding+comment:+" . urlencode($stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: index.php?error=Invalid+comment+submission.");
    }
} else {
    header("Location: index.php"); 
}
$conn->close();
exit();
?>