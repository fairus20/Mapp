<?php



include 'db_connect.php';

$category_id = $_GET['id'] ?? null;
$category = null;
$message = '';

if ($category_id && is_numeric($category_id)) {
    
    $stmt = $conn->prepare("SELECT * FROM Categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        $message = "Category not found.";
    }
    $stmt->close();
} else {
    header("Location: categories.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $category) {
    $title = $_POST['title'] ?? $category['title'];
    $status = $_POST['status'] ?? $category['status'];

    $stmt = $conn->prepare("UPDATE Categories SET title=?, status=?, modified=NOW() WHERE id=?");
    $stmt->bind_param("ssi", $title, $status, $category_id);

    if ($stmt->execute()) {
        $message = "Category updated successfully!";
       
        $stmt_re_fetch = $conn->prepare("SELECT * FROM Categories WHERE id = ?");
        $stmt_re_fetch->bind_param("i", $category_id);
        $stmt_re_fetch->execute();
        $result_re_fetch = $stmt_re_fetch->get_result();
        $category = $result_re_fetch->fetch_assoc();
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
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Edit Category</h1>

        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($category): ?>
            <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
                <form action="edit_category.php?id=<?php echo htmlspecialchars($category['id']); ?>" method="POST">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Category Title:</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($category['title']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                        <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="active" <?php echo ($category['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($category['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Category
                        </button>
                        <a href="categories.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p class="text-center text-red-500 text-xl mt-10">Category not found for editing.</p>
        <?php endif; ?>
    </div>
</body>
</html>