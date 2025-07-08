<?php
// create_application.php

include 'db_connect.php'; // Include your database connection

$message = ''; // To display success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $category_id = $_POST['category_id'] ?? '';
    $posted_date = $_POST['posted_date'] ?? date('Y-m-d H:i:s');
    $author = $_POST['author'] ?? '';
    $title = $_POST['title'] ?? '';
    $review = $_POST['review'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $image_dir = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $message .= "File is not an image.<br>";
            $uploadOk = 0;
        }

        if ($_FILES["image"]["size"] > 5000000) {
            $message .= "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $message .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_dir = $target_file;
            } else {
                $message .= "Sorry, there was an error uploading your file.<br>";
            }
        }
    }

    // ✅ FIXED: Removed 'image' column (since it doesn't exist in your DB)
    $stmt = $conn->prepare("INSERT INTO Applications (category_id, posted_date, author, title, review, image_dir, status, created, modified) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    $stmt->bind_param("issssss", $category_id, $posted_date, $author, $title, $review, $image_dir, $status);

    if ($stmt->execute()) {
        $message = "New application review created successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch categories
$categories = [];
$result = $conn->query("SELECT id, title FROM Categories");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Application Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Create New Application Review</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Message:</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            <form action="create_application.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required>
                        <option value="">Select a Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                <?php echo htmlspecialchars($category['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="author" class="block text-gray-700 text-sm font-bold mb-2">Author:</label>
                    <input type="text" name="author" id="author" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Application Title:</label>
                    <input type="text" name="title" id="title" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="review" class="block text-gray-700 text-sm font-bold mb-2">Review:</label>
                    <textarea name="review" id="review" rows="5" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
                    <input type="file" name="image" id="image" accept="image/*" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Upload an image for the application review.</p>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select name="status" id="status" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Add Review
                    </button>
                    <a href="index.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Back to List
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
