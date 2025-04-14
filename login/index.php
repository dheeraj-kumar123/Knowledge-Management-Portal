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

// Get user_id from session
$user_id = $_SESSION['user_id'] ?? 0;

// ✅ Define w3Links array
$w3Links = [
    "HTML" => "https://www.w3schools.com/html/",
    "CSS" => "https://www.w3schools.com/css/",
    "JavaScript" => "https://www.w3schools.com/js/",
    "Python" => "https://www.w3schools.com/python/",
    "SQL" => "https://www.w3schools.com/sql/",
    "PHP" => "https://www.w3schools.com/php/",
    "Bootstrap" => "https://www.w3schools.com/bootstrap/",
    "Java" => "https://www.w3schools.com/java/",
    "C++" => "https://www.w3schools.com/cpp/",
    "C#" => "https://www.w3schools.com/cs/",
    "React" => "https://www.w3schools.com/react/",
    "Node.js" => "https://www.w3schools.com/nodejs/",
    "Data Science" => "https://www.w3schools.com/datascience/",
    "AI" => "https://www.w3schools.com/ai/default.asp"
];

$keywords = array_keys($w3Links);

// ✅ Handle search query
$search = $_GET['search'] ?? '';

// Function to handle keyword matching
function getW3Link($keyword) {
    global $w3Links;
    foreach ($w3Links as $key => $url) {
        if (stripos($keyword, $key) !== false) {
            return $url;
        }
    }
    return "https://www.w3schools.com/";
}

// ✅ Insert visit into the database
if ($search) {
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO visited_topics (user_id, topic) VALUES (?, ?)");
        $stmt->execute([$user_id, $search]);
    }
}

// ✅ Get visited topics from the database
$visitedTopics = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT topic FROM visited_topics WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $visitedTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DocRepo Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-200 via-purple-200 to-pink-200 text-white flex flex-col min-h-screen">

<header class="bg-gray-600 text-white py-4 shadow-md flex justify-center items-center relative">
    <h1 class="text-xl font-bold">KNOWLEDGE REPOSITORY</h1>
    <a href="project.html" class="absolute right-4 text-white hover:underline">HomePage</a>
</header>

<div class="keyword-nav flex overflow-x-auto bg-white p-2 scrollbar-hide">
    <?php foreach ($keywords as $kw): ?>
        <button onclick="handleKeywordClick('<?= $kw ?>', '<?= $w3Links[$kw] ?>')" class="bg-gray-200 text-black px-4 py-2 rounded-lg m-2 hover:bg-gray-400 transition">
            <?= $kw ?>
        </button>
    <?php endforeach; ?>
</div>

<h2 class="text-center text-black text-2xl mt-6 font-semibold">
    Learn to code. Explore. Build your future.
</h2>

<div class="flex justify-center mt-6">
    <form method="get" class="flex flex-col items-center">
        <input type="text" name="search" class="p-3 w-72 border rounded-md text-black focus:outline-none" placeholder="Search...">
        <button type="submit" class="mt-3 bg-gray-200 text-black px-6 py-2 rounded-md font-bold hover:bg-gray-400 transition">
            Search
        </button>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-lg mt-6 mx-auto w-4/5">
    <h3 class="text-black font-bold text-lg">📚 Recently Visited Topics</h3>
    <ul class="space-y-2">
        <?php foreach ($visitedTopics as $topic): ?>
            <li><a href="<?= $w3Links[$topic['topic']] ?>" target="_blank" class="text-black hover:underline"><?= htmlspecialchars($topic['topic']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    function handleKeywordClick(keyword, link) {
        window.open(link, '_blank');
        window.location.href = '?search=' + encodeURIComponent(keyword);
    }
</script>

</body>
</html>
