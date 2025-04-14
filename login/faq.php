<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$formSubmitted = false;
$formError = '';
$formSuccess = '';
$newQuestion = '';
$newEmail = '';
$selectedCategory = '';

$userQuestionsFile = 'user_questions.json';
$adminAnswersFile = 'admin_answers.json';

// Load user questions
$userQuestions = [];
if (file_exists($userQuestionsFile)) {
    $userQuestions = json_decode(file_get_contents($userQuestionsFile), true) ?: [];
}

// Load admin answers (which will be added to FAQs)
$adminAnswers = [];
if (file_exists($adminAnswersFile)) {
    $adminAnswers = json_decode(file_get_contents($adminAnswersFile), true) ?: [];
}

$faqCategories = [
    'JavaScript' => [
        [
            'question' => 'What is JavaScript?',
            'answer' => 'JavaScript is a scripting language used to create and control dynamic website content.'
        ],
        [
            'question' => 'How do I declare variables in JavaScript?',
            'answer' => 'You can declare variables using var, let, or const keywords.'
        ],
        [
            'question' => 'What is the difference between == and === in JavaScript?',
            'answer' => '== compares values with type coercion, while === compares both value and type without coercion.'
        ],
        [
            'question' => 'What are JavaScript promises?',
            'answer' => 'Promises are objects representing the eventual completion or failure of an asynchronous operation.'
        ],
        [
            'question' => 'How does JavaScript handle asynchronous code?',
            'answer' => 'JavaScript handles async code using callbacks, promises, and async/await syntax.'
        ]
    ],
    'PHP' => [
        [
            'question' => 'What is PHP used for?',
            'answer' => 'PHP is a server-side scripting language designed for web development.'
        ],
        [
            'question' => 'How do you connect to a MySQL database in PHP?',
            'answer' => 'You can use mysqli or PDO extensions to connect to MySQL in PHP.'
        ],
        [
            'question' => 'What are PHP sessions?',
            'answer' => 'Sessions are a way to preserve data across subsequent HTTP requests.'
        ],
        [
            'question' => 'How do you prevent SQL injection in PHP?',
            'answer' => 'Use prepared statements with parameterized queries via PDO or mysqli.'
        ],
        [
            'question' => 'What is Composer in PHP?',
            'answer' => 'Composer is a dependency management tool for PHP.'
        ]
    ],
    'Python' => [
        [
            'question' => 'What is Python good for?',
            'answer' => 'Python is versatile and used for web development, data analysis, AI, and more.'
        ],
        [
            'question' => 'How do you create a virtual environment in Python?',
            'answer' => 'Use "python -m venv envname" to create a virtual environment.'
        ],
        [
            'question' => 'What are Python decorators?',
            'answer' => 'Decorators are functions that modify the behavior of other functions.'
        ],
        [
            'question' => 'How do you handle exceptions in Python?',
            'answer' => 'Use try-except blocks to handle exceptions in Python.'
        ],
        [
            'question' => 'What is the difference between lists and tuples?',
            'answer' => 'Lists are mutable while tuples are immutable.'
        ]
    ],
    'HTML/CSS' => [
        [
            'question' => 'What is the difference between HTML and CSS?',
            'answer' => 'HTML structures content while CSS styles it.'
        ],
        [
            'question' => 'What are semantic HTML elements?',
            'answer' => 'Elements like <header>, <footer>, <article> that clearly describe their meaning.'
        ],
        [
            'question' => 'How does CSS Flexbox work?',
            'answer' => 'Flexbox is a layout model that allows responsive elements within a container.'
        ],
        [
            'question' => 'What is CSS Grid?',
            'answer' => 'A 2D layout system for the web that lets you create complex responsive designs.'
        ],
        [
            'question' => 'How do you center a div in CSS?',
            'answer' => 'Use margin: auto with fixed width, or flexbox/grid centering techniques.'
        ]
    ],
    'SQL' => [
        [
            'question' => 'What is SQL?',
            'answer' => 'Structured Query Language used to manage relational databases.'
        ],
        [
            'question' => 'What is the difference between WHERE and HAVING?',
            'answer' => 'WHERE filters rows before grouping, HAVING filters after grouping.'
        ],
        [
            'question' => 'What are SQL joins?',
            'answer' => 'Joins combine rows from two or more tables based on related columns.'
        ],
        [
            'question' => 'What is normalization in databases?',
            'answer' => 'The process of organizing data to minimize redundancy.'
        ],
        [
            'question' => 'What is an SQL index?',
            'answer' => 'A database structure that improves the speed of data retrieval.'
        ]
    ]
];

// Add admin answers to FAQ categories
foreach ($adminAnswers as $answer) {
    if (isset($faqCategories[$answer['category']])) {
        $faqCategories[$answer['category']][] = [
            'question' => $answer['question'],
            'answer' => $answer['answer']
        ];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['question'])) {
            throw new Exception('Question is required');
        }
        
        if (empty($_POST['email'])) {
            throw new Exception('Email is required');
        }

        $newQuestion = htmlspecialchars(trim($_POST['question']));
        $newEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $selectedCategory = htmlspecialchars($_POST['category'] ?? '');

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }

        if (!isset($faqCategories[$selectedCategory])) {
            $selectedCategory = 'General';
        }
        
        $userQuestions[] = [
            'question' => $newQuestion,
            'category' => $selectedCategory,
            'email' => $newEmail,
            'date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'answer' => ''
        ];
        
        // Save to JSON file
        file_put_contents($userQuestionsFile, json_encode($userQuestions, JSON_PRETTY_PRINT));
        
        $formSubmitted = true;
        $formSuccess = 'Thank you for your question! Our team will review it and get back to you.';
        
        // Reset form fields
        $newQuestion = '';
        $newEmail = '';
    } catch (Exception $e) {
        $formError = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-200 via-purple-200 to-pink-200 min-h-screen text-white">
   
    <nav class="bg-gray-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Knowledge Base</h1>
            <div class="space-x-4">
                <a href="admin.php" class="relative inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg group bg-gradient-to-br from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black shadow-lg transform transition-all duration-300 hover:scale-105">
                    Admin Panel
                </a>
                <a href="project.html" class="relative inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg group bg-gradient-to-br from-white to-gray-100 hover:from-gray-100 hover:to-gray-200 text-black shadow-lg transform transition-all duration-300 hover:scale-105 border border-black">
                    Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto py-8 px-4 max-w-6xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-black">FAQs</h1>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach (array_keys($faqCategories) as $index => $category): ?>
                <button onclick="showCategory('<?php echo htmlspecialchars($category); ?>')" 
                        class="category-tab px-4 py-2 rounded-lg border border-white text-black hover:bg-white hover:text-blue-600 transition <?php echo $index === 0 ? 'bg-white text-blue-600' : ''; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- FAQ Categories -->
        <?php foreach ($faqCategories as $category => $faqs): ?>
            <div id="category-<?php echo htmlspecialchars($category); ?>" class="faq-category mb-8 <?php echo $category !== array_key_first($faqCategories) ? 'hidden' : ''; ?>">
                <h2 class="text-2xl font-bold mb-4 text-white"><?php echo htmlspecialchars($category); ?> Questions</h2>
                <div class="bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 rounded-lg shadow-md overflow-hidden text-gray-900">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="faq-item border-b border-gray-600 last:border-b-0">
                            <div class="faq-question flex justify-between items-center p-4 cursor-pointer hover:bg-white hover:text-gray-900 transition-colors duration-200" 
                                 onclick="toggleAnswer('<?php echo htmlspecialchars($category); ?>', <?php echo $index; ?>)">
                                <h3 class="font-bold"><?php echo htmlspecialchars($faq['question']); ?></h3>
                                <span id="icon-<?php echo htmlspecialchars($category); ?>-<?php echo $index; ?>" class="text-xl font-light">+</span>
                            </div>
                            <div id="answer-<?php echo htmlspecialchars($category); ?>-<?php echo $index; ?>" class="faq-answer hidden px-4 pb-4 pt-2 bg-white text-gray-900">
                                <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Ask a Question Section -->
        <div class="bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 rounded-lg shadow-md p-6 mt-8 text-gray-900">
            <h2 class="text-2xl font-bold mb-4">Ask a Question</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label for="category" class="block font-medium mb-2">Language/Category*</label>
                    <select id="category" name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <?php foreach (array_keys($faqCategories) as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="question" class="block font-medium mb-2">Your Question*</label>
                    <textarea id="question" name="question" rows="3" 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div>
                    <label for="email" class="block font-medium mb-2">Your Email*</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <button type="submit" class="w-full bg-gray-200 text-black py-2 px-4 rounded-lg hover:bg-gray-400 transition">
                    Submit Question
                </button>
            </form>
        </div>
    </div>

    <footer class="bg-gray-900 text-white text-center p-4 text-sm fixed bottom-0 w-full">
        <p>&copy; 2025 Knowledge Management Portal</p>
    </footer>

    <script>
        // Show selected category
        function showCategory(category) {
            document.querySelectorAll('.faq-category').forEach(el => {
                el.classList.add('hidden');
            });
            document.getElementById('category-' + category).classList.remove('hidden');
            
            // Update active tab styling
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('bg-white', 'text-blue-600');
            });
            event.target.classList.add('bg-white', 'text-blue-600');
        }

        function toggleAnswer(category, index) {
            const answer = document.getElementById('answer-' + category + '-' + index);
            const icon = document.getElementById('icon-' + category + '-' + index);
            
            answer.classList.toggle('hidden');
            
            if (answer.classList.contains('hidden')) {
                icon.textContent = '+';
            } else {
                icon.textContent = '−';
            }
        }
    </script>

</body>
</html>
