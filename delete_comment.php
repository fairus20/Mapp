<?php

include 'db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $comment_id = $_GET['id'];
    $application_id = null; 

    
    $stmt_get_app_id = $conn->prepare("SELECT application_id FROM Comments WHERE id = ?");
    $stmt_get_app_id->bind_param("i", $comment_id);
    $stmt_get_app_id->execute();
    $result_app_id = $stmt_get_app_id->get_result();
    if ($result_app_id->num_rows > 0) {
        $row = $result_app_id->fetch_assoc();
        $application_id = $row['application_id'];
    }
    $stmt_get_app_id->close();


    $stmt = $conn->prepare("DELETE FROM Comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);

    if ($stmt->execute()) {
        $redirect_url = "comments.php?message=Comment+deleted+successfully!";
        if ($application_id) {
            $redirect_url = "view_application.php?id=" . urlencode($application_id) . "&message=Comment+deleted+successfully!";
        }
        header("Location: " . $redirect_url);
    } else {
        $redirect_url = "comments.php?error=Error+deleting+comment:+" . urlencode($stmt->error);
        if ($application_id) {
             $redirect_url = "view_application.php?id=" . urlencode($application_id) . "&error=Error+deleting+comment:+" . urlencode($stmt->error);
        }
        header("Location: " . $redirect_url);
    }
    $stmt->close();
} else {
    header("Location: comments.php?error=Invalid+comment+ID.");
}

$conn->close();
exit();
?>