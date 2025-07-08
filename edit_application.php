<?php

include 'db_connect.php';

$app_id = $_GET['id'] ?? null;
$app = null;
$message = '';

if ($app_id && is_numeric($app_id)) {
   
    $stmt = $conn->prepare("SELECT * FROM Applications WHERE id = ?");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $app = $result->fetch_assoc();
    } else {
        $message = "Application not found.";
    }
    $stmt->close();
} else {
    header("Location: index.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $app) {
    
    $category_id = $_POST['category_id'] ?? $app['category_id'];
    $posted_date = $_POST['posted_date'] ?? $app['posted_date'];
    $author = $_POST['author'] ?? $app['author'];
    $title = $_POST['title'] ?? $app['title'];
    $review = $_POST['review'] ?? $app['review'];
    $status = $_POST['status'] ?? $app['status'];
    $current_image_dir = $app['image_dir']; 
    

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $uploadOk = 1;

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) { $uploadOk = 1; } else { $message .= "File is not an image.<br>"; $uploadOk = 0; }
        if ($_FILES["image"]["size"] > 5000000) { $message .= "Sorry, your file is too large.<br>"; $uploadOk = 0; }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>"; $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            $message .= "Sorry, your new file was not uploaded. Keeping old image if exists.<br>";
            $image_to_save = $current_image_dir; 
        } else {
            
            if (!empty($current_image_dir) && file_exists($current_image_dir)) {
                unlink($current_image_dir);
            }
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_to_save = $target_file; 
            } else {
                $message .= "Sorry, there was an error uploading your new file. Keeping old image if exists.<br>";
                $image_to_save = $current_image_dir; 
            }
        }
    } else {
        $image_to_save = $current_image_dir; 
    }

    
    $stmt = $conn->prepare("UPDATE Applications SET category_id=?, posted_date=?, author=?, title=?, review=?, image=?, image_dir=?, status=?, modified=NOW() WHERE id=?");
    $stmt->bind_param("isssssssi", $category_id, $posted_date, $author, $title, $review, $image_to_save, $image_to_save, $status, $app_id);

    if ($stmt->execute()) {
        $message = "Application review updated successfully!";
        
        $stmt_re_fetch = $conn->prepare("SELECT * FROM Applications WHERE id = ?");
        $stmt_re_fetch->bind_param("i", $app_id);
        $stmt_re_fetch->execute();
        $result_re_fetch = $stmt_re_fetch->get_result();
        $app = $result_re_fetch->fetch_assoc();
        $stmt_re_fetch->close();
    } else {
        $message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}


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
    <title>Edit Application Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Edit Application Review</h1>

        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($app): ?>
            <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
                <form action="edit_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                        <select name="category_id" id="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($app['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="author" class="block text-gray-700 text-sm font-bold mb-2">Author:</label>
                        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($app['author']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Application Title:</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($app['title']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="review" class="block text-gray-700 text-sm font-bold mb-2">Review:</label>
                        <textarea name="review" id="review" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($app['review']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Current Image:</label>
                        <?php if (!empty($app['image_dir']) && file_exists($app['image_dir'])): ?>
                            <img src="<?php echo htmlspecialchars($app['image_dir']); ?>" alt="Current Image" class="w-32 h-32 object-cover mb-2 rounded">
                            <p class="text-xs text-gray-500 mb-2">Leave blank to keep current image.</p>
                        <?php else: ?>
                            <p class="text-gray-500 mb-2">No image uploaded currently.</p>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-xs text-gray-500 mt-1">Upload a new image if you want to change it.</p>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                        <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="active" <?php echo ($app['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($app['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Review
                        </button>
                        <a href="index.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p class="text-center text-red-500 text-xl mt-10">Application not found for editing.</p>
        <?php endif; ?>
    </div>
</body>
</html>