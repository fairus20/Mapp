<?php
include 'db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO Categories (title, status, created, modified) VALUES (?, ?, NOW(), NOW())");
        $stmt->bind_param("ss", $title, $status);

        if ($stmt->execute()) {
            $message = "New category created successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Category title cannot be empty.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Category</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Create New Category</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            <form action="create_category.php" method="POST">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Category Title:</label>
                    <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Add Category
                    </button>
                    <a href="categories.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Back to Categories
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>