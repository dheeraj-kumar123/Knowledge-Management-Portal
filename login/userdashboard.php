<?php
session_start();

// ✅ Database connection
$host = 'localhost';
$dbname = 'docrepo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

// ✅ User ID (default 0 for guest)
$user_id = $_SESSION['user_id'] ?? 0;

// ✅ Initialize bookmarks if not already in session
if (!isset($_SESSION['bookmarks'])) {
    $_SESSION['bookmarks'] = [
        ["title" => "PHP Basics", "link" => "https://www.w3schools.com/php/"],
        ["title" => "JavaScript Guide", "link" => "https://www.w3schools.com/js/"],
        ["title" => "AI Overview", "link" => "https://www.w3schools.com/ai/default.asp"]
    ];
}

// ✅ Initialize recent activity in session (optional)
if (!isset($_SESSION['recent_activity'])) {
    $_SESSION['recent_activity'] = [
        "Viewed Python Tutorial",
        "Searched HTML Basics",
        "Visited CSS Section"
    ];
}

// ✅ Handle bookmark submission
if (isset($_POST['add_bookmark'])) {
    $newTitle = htmlspecialchars($_POST['bookmark_title']);
    $keyword = strtolower(trim($newTitle));
    $baseUrl = "https://www.w3schools.com/";
    $topicsMap = [
        "php" => "php/",
        "javascript" => "js/",
        "python" => "python/",
        "html" => "html/",
        "css" => "css/",
        "sql" => "sql/",
        "ai" => "ai/default.asp",
        "machine learning" => "ai/ai_machine_learning.asp"
    ];

    $matched = false;
    foreach ($topicsMap as $key => $path) {
        if (stripos($keyword, $key) !== false) {
            $newLink = $baseUrl . $path;
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        $newLink = $baseUrl;
    }

    if ($newTitle && $newLink) {
        $_SESSION['bookmarks'][] = ["title" => $newTitle, "link" => $newLink];
        $activity = "Bookmarked: $newTitle";
        array_unshift($_SESSION['recent_activity'], $activity);

        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO recent_activity (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$user_id, $activity]);
        }
    }
}

// ✅ Limit session activity to 10
$_SESSION['recent_activity'] = array_slice($_SESSION['recent_activity'], 0, 10);

// ✅ Get activity from database if available
if ($pdo) {
    $stmt = $pdo->prepare("SELECT activity FROM recent_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $recentActivity = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'activity');
} else {
    $recentActivity = $_SESSION['recent_activity'];
}

$user = $_SESSION['username'] ?? 'Guest';
$bookmarks = $_SESSION['bookmarks'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - DocRepo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-200 via-purple-200 to-pink-200 flex flex-col min-h-screen text-white">

<header class="bg-gray-600 text-white py-4 shadow-md flex justify-center items-center relative">
    <h1 class="text-xl font-bold">Your Coding Dashboard</h1>
    <a href="project.html" class="absolute right-4 text-white hover:underline">HomePage</a>
</header>

<div class="container mx-auto px-6 py-8">
    <h2 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($user); ?>!</h2>

    <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
        <h2 class="text-black font-bold text-lg">🔖 Bookmarked Topics</h2>
        <ul class="space-y-2">
            <?php foreach ($bookmarks as $index => $bm): ?>
                <li class="flex justify-between items-center">
                    <a href="<?= $bm['link'] ?>" target="_blank" class="text-black hover:underline"><?= htmlspecialchars($bm['title']) ?></a>
                    <form method="post" class="inline-block">
                        <input type="hidden" name="bookmark_index" value="<?= $index ?>">
                        <button type="submit" name="remove_bookmark" class="bg-gray-200 text-black px-3 py-1 rounded-md hover:bg-gray-400">Remove</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="post" class="mt-4">
            <input type="text" name="bookmark_title" placeholder="Topic Title" required class="p-2 border rounded-md text-gray-800">
            <button type="submit" name="add_bookmark" class="bg-gray-200 text-black px-4 py-2 rounded-md hover:bg-gray-400">Add Bookmark</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
        <h2 class="text-black font-bold text-lg">🕘 Recent Activity</h2>
        <ul class="space-y-2">
            <?php foreach ($recentActivity as $activity): ?>
                <li><?= htmlspecialchars($activity) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
        <h2 class="text-black font-bold text-lg">📚 Personalized Suggestions</h2>
        <p>Keep exploring new topics and grow your coding skills!</p>
        <a href="https://www.w3schools.com/" target="_blank" class="text-black hover:underline">Go to W3Schools Homepage</a>
    </div>
</div>

</body>
</html>
