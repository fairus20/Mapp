<?php

include 'db_connect.php'; 

$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$category_filter = $_GET['category_filter'] ?? '';

$sql = "SELECT a.*, c.title AS category_title FROM Applications a LEFT JOIN Categories c ON a.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (a.title LIKE ? OR a.author LIKE ? OR a.review LIKE ?)";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $types .= "sss";
}

if (!empty($status_filter) && ($status_filter === 'active' || $status_filter === 'inactive')) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($category_filter)) {
    $sql .= " AND a.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$sql .= " ORDER BY a.created DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $applications = [];
    echo "Error preparing statement: " . $conn->error;
}


$categories = [];
$cat_result = $conn->query("SELECT id, title FROM Categories ORDER BY title ASC");
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
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
    <title>Mobile Application Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen font-sans text-white">
   
    <nav class="bg-pink-400 shadow-md p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-extrabold text-black hover:text-gray-800 transition"> App Reviews</a>
        <div class="space-x-4 text-sm font-medium">
            <a href="index.php" class="text-black hover:text-white transition">Applications</a>
            <a href="categories.php" class="text-black hover:text-white transition">Categories</a>
            <a href="comments.php" class="text-black hover:text-white transition">Comments</a>
            <a href="create_application.php" class="bg-black hover:bg-gray-900 text-pink-400 font-semibold py-2 px-4 rounded-lg transition shadow">+ Add Review</a>
        </div>
    </div>
    </nav>

    <div class="max-w-7xl mx-auto py-10 px-4">
        <h1 class="text-4xl font-extrabold mb-8 text-center text-pink-300 tracking-wide">Mobile Application Reviews</h1>

      
    <form action="index.php" method="GET" class="mb-10 bg-gray-900 p-6 shadow-lg rounded-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <label class="block text-sm font-medium mb-1">Search:</label>
            <input type="text" name="search" id="search"
                value="<?= htmlspecialchars($search_query); ?>"
                placeholder="Search title, author..."
                class="w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pink-400 focus:ring-pink-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status:</label>
            <select name="status_filter" id="status_filter"
                class="w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pink-400 focus:ring-pink-400">
                <option value="">All</option>
                <option value="active" <?= $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div>
            
            <label class="block text-sm font-medium mb-1">Category:</label>
            <select name="category_filter" id="category_filter"
                class="w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pink-400 focus:ring-pink-400">
                <option value="">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['id']); ?>"
                    <?= $category_filter == $category['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($category['title']); ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                    class="bg-pink-400 text-black px-4 py-2 rounded-md shadow hover:bg-pink-500 transition w-full">
                    Apply Filter
                </button>
                <a href="index.php"
                    class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow w-full text-center transition">
                    Clear
                </a>
            </div>
        </form>

      
        <?php if (empty($applications)): ?>
            <p class="text-center text-xl text-gray-300">No application reviews found.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($applications as $app): ?>
            <div class="bg-gray-900 border border-pink-400 rounded-xl overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 flex flex-col">
            <?php if (!empty($app['image_dir']) && file_exists($app['image_dir'])): ?>
                <img src="<?= htmlspecialchars($app['image_dir']); ?>"
                    alt="<?= htmlspecialchars($app['title']); ?>"
                    class="w-full h-48 object-cover">
                <?php else: ?>
                <div class="w-full h-48 bg-gray-800 flex items-center justify-center text-gray-500">No Image</div>
                <?php endif; ?>

                <div class="p-5 flex flex-col flex-grow">
                    <h2 class="text-lg font-bold text-pink-300 mb-2"><?= htmlspecialchars($app['title']); ?></h2>
                    <p class="text-sm text-gray-300 mb-1"><?= htmlspecialchars($app['author']); ?></p>
                    <p class="text-sm text-gray-400 mb-1"><?= htmlspecialchars($app['category_title']); ?></p>
                    <p class="text-sm font-semibold mb-2">
                        Status:
                    <?php if ($app['status'] == 'active'): ?>
                    <span class="text-green-400">Active</span>
                    <?php else: ?>
                    <span class="text-red-400">Inactive</span>
                    <?php endif; ?>
                </p>

            <p class="text-gray-200 text-sm mb-4 flex-grow">
                <?= nl2br(htmlspecialchars(substr($app['review'], 0, 150))) . (strlen($app['review']) > 150 ? '...' : ''); ?>
                 </p>

                <div class="text-xs text-gray-500 mt-auto mb-3">
                    <p>Created: <?= formatDateTime($app['created']); ?></p>
                        <p>Modified: <?= formatDateTime($app['modified']); ?></p>
                    </div>

                    <div class="mt-3 flex space-x-2">
                    <a href="view_application.php?id=<?= htmlspecialchars($app['id']); ?>"
                        class="bg-pink-400 hover:bg-pink-500 text-black text-sm py-2 px-4 rounded-md transition">
                         View
                    </a>
                    <a href="edit_application.php?id=<?= htmlspecialchars($app['id']); ?>"
                        class="bg-pink-600 hover:bg-pink-700 text-white text-sm py-2 px-4 rounded-md transition">
                         Edit
                    </a>
                    <a href="delete_application.php?id=<?= htmlspecialchars($app['id']); ?>"
                        class="bg-red-500 hover:bg-red-600 text-white text-sm py-2 px-4 rounded-md transition"
                        onclick="return confirm('Are you sure to delete this review?');">
                        Delete
                        </a>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>