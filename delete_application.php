<?php

include 'db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $app_id = $_GET['id'];

    
    $stmt_fetch_image = $conn->prepare("SELECT image_dir FROM Applications WHERE id = ?");
    $stmt_fetch_image->bind_param("i", $app_id);
    $stmt_fetch_image->execute();
    $result_image = $stmt_fetch_image->get_result();
    $image_data = $result_image->fetch_assoc();
    $stmt_fetch_image->close();

    $image_to_delete = $image_data['image_dir'] ?? '';

    
    $stmt_delete_comments = $conn->prepare("DELETE FROM Comments WHERE application_id = ?");
    $stmt_delete_comments->bind_param("i", $app_id);
    $stmt_delete_comments->execute();
    $stmt_delete_comments->close();

    $stmt = $conn->prepare("DELETE FROM Applications WHERE id = ?");
    $stmt->bind_param("i", $app_id);

    if ($stmt->execute()) {
        
        if (!empty($image_to_delete) && file_exists($image_to_delete)) {
            unlink($image_to_delete);
        }
        header("Location: index.php?message=Application+review+deleted+successfully!");
    } else {
        header("Location: index.php?error=Error+deleting+application+review:+" . urlencode($stmt->error));
    }
    $stmt->close();
} else {
    header("Location: index.php?error=Invalid+application+ID.");
}

$conn->close();
exit();
?>