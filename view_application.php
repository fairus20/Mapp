<?php


include 'db_connect.php';

$app = null;
$comments = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $app_id = $_GET['id'];

    $stmt_app = $conn->prepare("SELECT a.*, c.title AS category_title FROM Applications a LEFT JOIN Categories c ON a.category_id = c.id WHERE a.id = ?");
    $stmt_app->bind_param("i", $app_id);
    $stmt_app->execute();
    $result_app = $stmt_app->get_result();
    if ($result_app->num_rows > 0) {
        $app = $result_app->fetch_assoc();
    }
    $stmt_app->close();

    $stmt_comments = $conn->prepare("SELECT * FROM Comments WHERE application_id = ? ORDER BY created ASC");
    $stmt_comments->bind_param("i", $app_id);
    $stmt_comments->execute();
    $result_comments = $stmt_comments->get_result();
    if ($result_comments->num_rows > 0) {
        while ($row = $result_comments->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    $stmt_comments->close();
} else {
    
    header("Location: index.php");
    exit();
}

$conn->close();

function formatDateTime($datetime) {
    return date("F j, Y, g:i a", strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app ? htmlspecialchars($app['title']) : 'Application Not Found'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-blue-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-2xl font-bold">App Reviews</a>
            <div class="space-x-4">
                <a href="index.php" class="text-white hover:text-blue-200">Applications</a>
                <a href="categories.php" class="text-white hover:text-blue-200">Categories</a>
                <a href="comments.php" class="text-white hover:text-blue-200">Comments</a>
                <a href="create_application.php" class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Add New Review</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <?php if ($app): ?>
            <div class="bg-white shadow-md rounded-lg p-8 mb-8">
                <div class="flex flex-col md:flex-row gap-6 mb-6">
                    <?php if (!empty($app['image_dir']) && file_exists($app['image_dir'])): ?>
                        <div class="md:w-1/3 flex-shrink-0">
                            <img src="<?php echo htmlspecialchars($app['image_dir']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" class="w-full h-auto object-cover rounded-md shadow-lg">
                        </div>
                    <?php endif; ?>
                    <div class="<?php echo !empty($app['image_dir']) ? 'md:w-2/3' : 'w-full'; ?>">
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-3"><?php echo htmlspecialchars($app['title']); ?></h1>
                        <p class="text-gray-700 text-lg mb-2">By: <span class="font-semibold"><?php echo htmlspecialchars($app['author']); ?></span></p>
                        <p class="text-gray-600 text-md mb-2">Category: <span class="font-medium"><?php echo htmlspecialchars($app['category_title']); ?></span></p>

                        <p class="text-md font-bold mb-4">Status:
                            <?php if ($app['status'] == 'active'): ?>
                                <span class="text-green-600">Active</span>
                            <?php else: ?>
                                <span class="text-red-600">Inactive</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Review:</h3>
                    <p class="text-gray-800 leading-relaxed text-lg"><?php echo nl2br(htmlspecialchars($app['review'])); ?></p>
                </div>

                <div class="text-sm text-gray-500 border-t pt-4 mt-4">
                    <p>Posted On: <?php echo formatDateTime($app['posted_date']); ?></p>
                    <p>Last Modified: <?php echo formatDateTime($app['modified']); ?></p>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <a href="export_pdf.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded">Export to PDF</a>
                    <a href="edit_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">Edit Review</a>
                    <a href="delete_application.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this review?');">Delete Review</a>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Back to List</a>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Comments (<?php echo count($comments); ?>)</h2>

                <?php if (empty($comments)): ?>
                    <p class="text-gray-600">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($comments as $comment): ?>
                            <div class="border-b pb-4 last:border-b-0">
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($comment['name']); ?></p>
                                <p class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Posted on: <?php echo formatDateTime($comment['created']); ?>
                                    <?php if ($comment['modified'] != $comment['created']): ?>
                                        (Modified: <?php echo formatDateTime($comment['modified']); ?>)
                                    <?php endif; ?>
                                </p>
                                </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">Add a Comment</h3>
                <form action="add_comment.php" method="POST" class="space-y-4">
                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($app['id']); ?>">
                    <div>
                        <label for="comment_name" class="block text-gray-700 text-sm font-bold mb-2">Your Name:</label>
                        <input type="text" name="name" id="comment_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div>
                        <label for="comment_text" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                        <textarea name="comment" id="comment_text" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                    </div>
                    <div>
                        <label for="comment_rating" class="block text-gray-700 text-sm font-bold mb-2">Rating (1-5):</label>
                        <input type="number" name="rating" id="comment_rating" min="1" max="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg p-8 text-center">
                <p class="text-xl text-red-500 mb-4">Application review not found.</p>
                <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Back to List</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>