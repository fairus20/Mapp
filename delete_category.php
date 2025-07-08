<?php

include 'db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $category_id = $_GET['id'];


    $stmt_update_applications = $conn->prepare("UPDATE Applications SET category_id = NULL WHERE category_id = ?");
    $stmt_update_applications->bind_param("i", $category_id);
    $stmt_update_applications->execute();
    $stmt_update_applications->close();

  

    $stmt = $conn->prepare("DELETE FROM Categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);

    if ($stmt->execute()) {
        header("Location: categories.php?message=Category+deleted+successfully!");
    } else {
        header("Location: categories.php?error=Error+deleting+category:+" . urlencode($stmt->error));
    }
    $stmt->close();
} else {
    header("Location: categories.php?error=Invalid+category+ID.");
}

$conn->close();
exit();
?>