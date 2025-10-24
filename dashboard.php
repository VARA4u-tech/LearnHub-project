<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$database = new Database(); // Instantiate the Database class
$pdo = $database->getConnection(); // Get the PDO connection

// Fetch user's uploaded notes
$user_id = $_SESSION["id"];
$notes = [];

$sql = "SELECT id, title, subject, created_at, download_count, like_count FROM notes WHERE uploader_id = :uploader_id ORDER BY created_at DESC";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":uploader_id", $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}

// Fetch user's bookmarked notes
$bookmarked_notes = [];
$sql_bookmarked = "SELECT n.id, n.title, n.subject, n.uploader_name, n.created_at, n.download_count, n.like_count
                   FROM notes n
                   JOIN note_bookmarks nb ON n.id = nb.note_id
                   WHERE nb.user_id = :user_id
                   ORDER BY nb.created_at DESC";
if ($stmt_bookmarked = $pdo->prepare($sql_bookmarked)) {
    $stmt_bookmarked->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    if ($stmt_bookmarked->execute()) {
        $bookmarked_notes = $stmt_bookmarked->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt_bookmarked);
}

// Fetch total downloads and likes for the user's notes
$total_downloads = 0;
$total_likes = 0;
$sql_stats = "SELECT SUM(download_count) as total_downloads, SUM(like_count) as total_likes FROM notes WHERE uploader_id = :uploader_id";
if ($stmt_stats = $pdo->prepare($sql_stats)) {
    $stmt_stats->bindParam(":uploader_id", $user_id, PDO::PARAM_INT);
    if ($stmt_stats->execute()) {
        $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        $total_downloads = $stats['total_downloads'] ?? 0;
        $total_likes = $stats['total_likes'] ?? 0;
    }
    unset($stmt_stats);
}

// Fetch total bookmarks for the user
$total_bookmarks = 0;
$sql_total_bookmarks = "SELECT COUNT(id) as total_bookmarks FROM note_bookmarks WHERE user_id = :user_id";
if ($stmt_total_bookmarks = $pdo->prepare($sql_total_bookmarks)) {
    $stmt_total_bookmarks->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    if ($stmt_total_bookmarks->execute()) {
        $total_bookmarks_result = $stmt_total_bookmarks->fetch(PDO::FETCH_ASSOC);
        $total_bookmarks = $total_bookmarks_result['total_bookmarks'] ?? 0;
    }
    unset($stmt_total_bookmarks);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Unset $pdo after all database operations are done
unset($pdo);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>LearnHub - Dashboard</title>
    <link rel="icon" type="image/jpeg" href="assets/images/WhatsApp%20Image.jpg.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#3182CE",
                        "background-light": "#f7f7f7",
                        "background-dark": "#1A202C",
                    },
                    fontFamily: {
                        "display": ["Work Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .form-input:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
            box-shadow: 0 0 0 2px var(--tw-bg-primary);
        }
        .form-checkbox:checked {
            background-color: #000000;
            border-color: #000000;
        }
        #password-strength {
            transition: width 0.3s ease-in-out;
        }
        .nav-link-underline {
            position: relative;
            text-decoration: none;
        }
        .nav-link-underline::after {
            content: '';
            position: absolute;
            width: 100%;
            transform: scaleX(0);
            height: 1.5px;
            bottom: -4px;
            left: 0;
            background-color: #000000;
            transform-origin: bottom right;
            transition: transform 0.25s ease-out;
        }
        .nav-link-underline:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }
        .dark .nav-link-underline:hover::after {
            background-color: #ffffff;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-primary dark:text-background-light">
<div class="relative min-h-screen lg:flex">

<!-- Mobile Header -->
<header class="lg:hidden sticky top-0 z-20 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm border-b border-primary/10 dark:border-white/10">
    <div class="container mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
        <a class="flex items-center gap-2" href="index.php">
            <img src="assets/images/WhatsApp%20Image.jpg.jpg" alt="LearnHub Logo" class="h-10">
            <span class="text-xl font-bold text-primary dark:text-white">LearnHub</span>
        </a>
        <button id="dashboard-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-primary/70 dark:text-white/70 hover:text-primary dark:hover:text-white focus:outline-none">
            <span class="sr-only">Open sidebar</span>
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
    </div>
</header>

<!-- Backdrop -->
<div id="sidebar-backdrop" class="hidden lg:hidden fixed inset-0 z-20 bg-black/50"></div>

<!-- Sidebar -->
<aside id="dashboard-sidebar" class="fixed inset-y-0 left-0 z-30 w-72 bg-background-light dark:bg-background-dark border-r border-primary/10 dark:border-white/10 p-6 flex-col transform -translate-x-full transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:flex">
    <div class="mb-8">
        <a class="flex items-center gap-2" href="index.php">
            <img src="assets/images/WhatsApp%20Image.jpg.jpg" alt="LearnHub Logo" class="h-10">
            <span class="text-xl font-bold text-primary dark:text-white">LearnHub</span>
        </a>
    </div>
    <div class="flex items-center gap-3 mb-10">
        <div id="user-icon-display" class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center cursor-pointer">
            <span class="text-sm font-medium text-black"><?php echo substr($_SESSION['name'] ?? 'U', 0, 1); ?></span>
        </div>
        <h1 class="text-base font-bold text-primary dark:text-white"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h1>
    </div>

    <!-- User Details Modal/Section -->
    <div id="user-details-modal" class="hidden absolute top-0 left-0 w-full h-full bg-background-light dark:bg-background-dark p-6 rounded-lg shadow-lg z-40 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-primary dark:text-white">User Profile</h2>
            <button id="close-user-details" class="text-primary/70 dark:text-white/70 hover:text-primary dark:hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex flex-col gap-2">
            <p class="text-primary dark:text-white"><strong class="font-medium">Name:</strong> <?php echo htmlspecialchars($_SESSION['name'] ?? 'N/A'); ?></p>
            <p class="text-primary dark:text-white"><strong class="font-medium">Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></p>
            <p class="text-primary dark:text-white"><strong class="font-medium">College:</strong> <?php echo htmlspecialchars($_SESSION['college'] ?? 'N/A'); ?></p>
            <hr class="my-2 border-primary/10 dark:border-white/10">
            <p class="text-primary dark:text-white"><strong class="font-medium">Total Uploads:</strong> <?php echo count($notes); ?></p>
            <p class="text-primary dark:text-white"><strong class="font-medium">Total Likes Received:</strong> <?php echo $total_likes; ?></p>
            <p class="text-primary dark:text-white"><strong class="font-medium">Total Bookmarks:</strong> <?php echo $total_bookmarks; ?></p>
        </div>
    </div>

    <nav class="flex flex-col gap-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="index.php">
            <span class="material-symbols-outlined">home</span>
            <span class="text-sm font-medium">Home</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded bg-primary dark:bg-white text-white dark:text-primary" href="dashboard.php">
            <span class="material-symbols-outlined">space_dashboard</span>
            <span class="text-sm font-medium">My Notes</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="upload.php">
            <span class="material-symbols-outlined">upload</span>
            <span class="text-sm font-medium">Upload</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="notes.php">
            <span class="material-symbols-outlined">search</span>
            <span class="text-sm font-medium">Search</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="#bookmarked-notes">
            <span class="material-symbols-outlined">bookmark</span>
            <span class="text-sm font-medium">Bookmarks</span>
        </a>
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="admin_blog.php">
                <span class="material-symbols-outlined">edit_note</span>
                <span class="text-sm font-medium">Admin Blog</span>
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded text-primary/70 dark:text-white/70 transition-colors nav-link-underline" href="logout.php">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm font-medium">Logout</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>
<div class="flex-1">
    <main class="flex-grow min-h-screen p-6 md:p-8">
<div class="max-w-7xl mx-auto">
<header class="mb-8">
<h1 class="text-3xl font-bold text-primary dark:text-white flex items-center gap-2">
    <span class="material-symbols-outlined">account_circle</span>
    User's Profile-> My Notes 
</h1>
</header>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<div class="bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg p-4 flex flex-col gap-2 transition-all duration-300 hover:shadow-lg hover:scale-105 hover:bg-primary/5 dark:hover:bg-white/5">
<p class="text-3xl font-bold text-primary dark:text-white"><?php echo count($notes); ?></p>
<p class="text-sm text-primary/60 dark:text-white/60">Notes</p>
</div>
<div class="bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg p-4 flex flex-col gap-2 transition-all duration-300 hover:shadow-lg hover:scale-105 hover:bg-primary/5 dark:hover:bg-white/5">
<p class="text-3xl font-bold text-primary dark:text-white"><?php echo $total_downloads; ?></p>
<p class="text-sm text-primary/60 dark:text-white/60">Downloads</p>
</div>
<div class="bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg p-4 flex flex-col gap-2 transition-all duration-300 hover:shadow-lg hover:scale-105 hover:bg-primary/5 dark:hover:bg-white/5">
<p class="text-3xl font-bold text-primary dark:text-white"><?php echo $total_likes; ?></p>
<p class="text-sm text-primary/60 dark:text-white/60">Likes</p>
</div>
<div class="bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg p-4 flex flex-col gap-2 transition-all duration-300 hover:shadow-lg hover:scale-105 hover:bg-primary/5 dark:hover:bg-white/5">
<p class="text-3xl font-bold text-primary dark:text-white"><?php echo $total_bookmarks; ?></p>
<p class="text-sm text-primary/60 dark:text-white/60">Bookmarks</p>
</div>
</div>
<div>
<h2 class="text-2xl font-bold mb-4 text-primary dark:text-white">Uploaded Notes</h2>
<div class="overflow-x-auto bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg">
<table class="w-full text-left">
<thead class="border-b border-primary/10 dark:border-white/10">
<tr>
<th class="p-4 text-sm font-semibold text-primary dark:text-white">Title</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white hidden md:table-cell">Course</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white hidden lg:table-cell">Date</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white text-right">Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($notes)): ?>
<tr class="border-b border-primary/10 dark:border-white/10 last:border-b-0 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors">
<td class="p-4 text-sm text-primary/60 dark:text-white/60 text-center" colspan="4">You haven't uploaded any notes yet.</td>
</tr>
<?php else: ?>
<?php foreach ($notes as $note): ?>
<tr class="border-b border-primary/10 dark:border-white/10 last:border-b-0 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors">
<td class="p-4 text-sm font-medium text-primary dark:text-white"><?php echo htmlspecialchars($note['title']); ?></td>
<td class="p-4 text-sm text-primary/60 dark:text-white/60 hidden md:table-cell"><?php echo htmlspecialchars($note['subject']); ?></td>
<td class="p-4 text-sm text-primary/60 dark:text-white/60 hidden lg:table-cell"><?php echo date('Y-m-d', strtotime($note['created_at'])); ?></td>
<td class="p-4 text-right">
<div class="flex items-center justify-end gap-2">
<a href="download.php?id=<?php echo $note['id']; ?>" class="flex items-center justify-center w-8 h-8 rounded text-primary/60 dark:text-white/60 hover:bg-primary/10 dark:hover:bg-white/10 hover:text-primary dark:hover:text-white transition-all duration-200 hover:scale-110 hover:shadow-md">
<span class="material-symbols-outlined text-base">download</span>
</a>
<a href="delete_note.php?id=<?php echo $note['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" class="flex items-center justify-center w-8 h-8 rounded text-red-500 hover:bg-red-500/10 transition-all duration-200 hover:scale-110 hover:shadow-md" onclick="return confirm('Are you sure you want to delete this note?');">
<span class="material-symbols-outlined text-base">delete</span>
</a>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
<div class="mt-8" id="bookmarked-notes">
<h2 class="text-2xl font-bold mb-4 text-primary dark:text-white">Bookmarked Notes</h2>
<div class="overflow-x-auto bg-background-light dark:bg-background-dark border border-primary/10 dark:border-primary/20 rounded-lg">
<table class="w-full text-left">
<thead class="border-b border-primary/10 dark:border-white/10">
<tr>
<th class="p-4 text-sm font-semibold text-primary dark:text-white">Title</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white hidden md:table-cell">Subject</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white hidden lg:table-cell">Uploader</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white hidden lg:table-cell">Date Bookmarked</th>
<th class="p-4 text-sm font-semibold text-primary dark:text-white text-right">Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($bookmarked_notes)): ?>
<tr class="border-b border-primary/10 dark:border-white/10 last:border-b-0 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors">
<td class="p-4 text-sm text-primary/60 dark:text-white/60 text-center" colspan="5">haven't bookmarked any notes yet.</td>
</tr>
<?php else: ?>
<?php foreach ($bookmarked_notes as $note): ?>
<tr class="border-b border-primary/10 dark:border-white/10 last:border-b-0 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors">
<td class="p-4 text-sm font-medium text-primary dark:text-white"><?php echo htmlspecialchars($note['title']); ?></td>
<td class="p-4 text-sm text-primary/60 dark:text-white/60 hidden md:table-cell"><?php echo htmlspecialchars($note['subject']); ?></td>
<td class="p-4 text-sm text-primary/60 dark:text-white/60 hidden lg:table-cell"><?php echo htmlspecialchars($note['uploader_name']); ?></td>
<td class="p-4 text-sm text-primary/60 dark:text-white/60 hidden lg:table-cell"><?php echo date('Y-m-d', strtotime($note['created_at'])); ?></td>
<td class="p-4 text-right">
<div class="flex items-center justify-end gap-2">
<a href="download.php?id=<?php echo $note['id']; ?>" class="flex items-center justify-center w-8 h-8 rounded text-primary/60 dark:text-white/60 hover:bg-primary/10 dark:hover:bg-white/10 hover:text-primary dark:hover:text-white transition-all duration-200 hover:scale-110 hover:shadow-md">
<span class="material-symbols-outlined text-base">download</span>
</a>
<button class="unbookmark-btn flex items-center justify-center w-8 h-8 rounded text-red-500 hover:bg-red-500/10 transition-all duration-200 hover:scale-110 hover:shadow-md" data-note-id="<?php echo $note['id']; ?>" title="Unbookmark">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21.75a.75.75 0 01-1.06.676L12 20.042l-7.359 4.359a.75.75 0 01-1.06-.676V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
    </svg>
</button>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</main>
</div>

