<?php
// categories.php


include 'db_connect.php';

$message = '';
if (isset($_GET['message'])) {
    $message = '<span class="text-green-700">' . htmlspecialchars($_GET['message']) . '</span>';
}
if (isset($_GET['error'])) {
    $message = '<span class="text-red-700">' . htmlspecialchars($_GET['error']) . '</span>';
}

$categories = [];
$sql = "SELECT * FROM Categories ORDER BY created DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-pink-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-pink text-2xl font-bold">App Reviews</a>
            <div class="space-x-4">
                <a href="index.php" class="text-black hover:text-pink-200">Applications</a>
                <a href="categories.php" class="text-black hover:text-pink-200">Categories</a>
                <a href="comments.php" class="text-black hover:text-pink-200">Comments</a>
                <a href="create_application.php" class="bg-pink-700 hover:bg-pink-800 text-black font-bold py-2 px-4 rounded">Add New Review</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Manage Categories</h1>

        <?php if ($message): ?>
            <div class="bg-pink-100 border border-pink-400 text-pink-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="flex justify-end mb-4">
            <a href="create_category.php" class="bg-white-500 hover:bg-white-700 text-black font-bold py-2 px-4 rounded">
                Add New Category
            </a>
        </div>

        <?php if (empty($categories)): ?>
            <p class="text-center text-gray-600 text-xl mt-10">No categories found.</p>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                <thead>
                <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                      ID
                </th>
                 <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                     Title
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                     Status
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Created
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Modified
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                 </th>
                    </tr>
        </head>
    <tbody>
             <?php foreach ($categories as $category): ?>
                 <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <?php echo htmlspecialchars($category['id']); ?>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <?php echo htmlspecialchars($category['title']); ?>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <?php if ($category['status'] == 'active'): ?>
                         <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                             Active
                        </span>
                        <?php else: ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                            </span>
                        <?php endif; ?>
                           </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <?php echo formatDateTime($category['created']); ?>
                           </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <?php echo formatDateTime($category['modified']); ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="edit_category.php?id=<?php echo htmlspecialchars($category['id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <a href="delete_category.php?id=<?php echo htmlspecialchars($category['id']); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this category and potentially its associated applications?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>