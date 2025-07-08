<?php



include 'db_connect.php';

$comment_id = $_GET['id'] ?? null;
$comment_data = null; 
$message = '';

if ($comment_id && is_numeric($comment_id)) {
    
    $stmt = $conn->prepare("SELECT * FROM Comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $comment_data = $result->fetch_assoc();
    } else {
        $message = "Comment not found.";
    }
    $stmt->close();
} else {
    header("Location: comments.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $comment_data) {
    $name = $_POST['name'] ?? $comment_data['name'];
    $comment_text = $_POST['comment'] ?? $comment_data['comment'];
    $rating = $_POST['rating'] ?? $comment_data['rating'];
    $status = $_POST['status'] ?? $comment_data['status'];

    $stmt = $conn->prepare("UPDATE Comments SET name=?, comment=?, rating=?, status=?, modified=NOW() WHERE id=?");
    $stmt->bind_param("ssisi", $name, $comment_text, $rating, $status, $comment_id);

    if ($stmt->execute()) {
        $message = "Comment updated successfully!";
       
        $stmt_re_fetch = $conn->prepare("SELECT * FROM Comments WHERE id = ?");
        $stmt_re_fetch->bind_param("i", $comment_id);
        $stmt_re_fetch->execute();
        $result_re_fetch = $stmt_re_fetch->get_result();
        $comment_data = $result_re_fetch->fetch_assoc();
        $stmt_re_fetch->close();
    } else {
        $message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Edit Comment</h1>

        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($comment_data): ?>
            <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
                <form action="edit_comment.php?id=<?php echo htmlspecialchars($comment_data['id']); ?>" method="POST">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Author Name:</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($comment_data['name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                        <textarea name="comment" id="comment" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($comment_data['comment']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating (1-5):</label>
                        <input type="number" name="rating" id="rating" min="1" max="5" value="<?php echo htmlspecialchars($comment_data['rating']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                        <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="active" <?php echo ($comment_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($comment_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Comment
                        </button>
                        <a href="comments.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p class="text-center text-red-500 text-xl mt-10">Comment not found for editing.</p>
        <?php endif; ?>
    </div>
</body>
</html>