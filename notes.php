<?php
session_start();
require_once "config/database.php";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$database = new Database(); // Instantiate the Database class
$pdo = $database->getConnection(); // Get the PDO connection

// Fetch unique subjects for filter dropdown
$subjects = [];
$sql_subjects = "SELECT DISTINCT subject FROM notes ORDER BY subject ASC";
if ($stmt_subjects = $pdo->prepare($sql_subjects)) {
    if ($stmt_subjects->execute()) {
        $subjects = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($stmt_subjects);
}

$search_term = "";
if (isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

$selected_subject = "";
if (isset($_GET['subject'])) {
    $selected_subject = trim($_GET['subject']);
}

$sort_by = "created_at_desc"; // Default sort
if (isset($_GET['sort_by'])) {
    $sort_by = trim($_GET['sort_by']);
}

$user_id = $_SESSION['id'] ?? null;

// Fetch notes
$notes = [];
$sql = "SELECT n.id, n.title, n.subject, n.uploader_id, n.uploader_name, n.college, n.created_at, n.download_count, n.like_count, CASE WHEN nl.id IS NOT NULL THEN 1 ELSE 0 END AS user_liked, CASE WHEN nb.id IS NOT NULL THEN 1 ELSE 0 END AS user_bookmarked
        FROM notes n
        LEFT JOIN note_likes nl ON n.id = nl.note_id AND nl.user_id = :user_id
        LEFT JOIN note_bookmarks nb ON n.id = nb.note_id AND nb.user_id = :user_id";

if (!empty($search_term)) {
    $conditions[] = "(n.title LIKE :search_term OR n.subject LIKE :search_term)";
}

if (!empty($selected_subject)) {
    $conditions[] = "n.subject = :selected_subject";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Add sorting
switch ($sort_by) {
    case 'created_at_asc':
        $sql .= " ORDER BY n.created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY n.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY n.title DESC";
        break;
    case 'created_at_desc':
    default:
        $sql .= " ORDER BY n.created_at DESC";
        break;
}

if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    if (!empty($search_term)) {
        $stmt->bindValue(":search_term", "%" . $search_term . "%");
    }
    if (!empty($selected_subject)) {
        $stmt->bindParam(":selected_subject", $selected_subject);
    }

    if ($stmt->execute()) {
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}
// Unset $pdo after all database operations are done
unset($pdo);
?>
<?php require_once 'includes/header.php'; ?>

        <main class="flex-grow flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="w-full">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-primary dark:text-white mb-8">Browse All Notes</h2>

                <!-- Search Form -->
                <div class="mb-8 max-w-lg mx-auto">
                    <form action="notes.php" method="GET" class="flex flex-col gap-4">
                        <div class="relative">
                            <input type="text" name="search" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" placeholder="Search by title or subject..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 p-2 rounded-full text-primary dark:text-white hover:bg-primary/10 dark:hover:bg-white/10 transition-colors">
                                <i class="material-symbols-outlined">search</i>
                            </button>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <select name="subject" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subject_option): ?>
                                    <option value="<?php echo htmlspecialchars($subject_option); ?>" <?php echo ($selected_subject == $subject_option) ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject_option); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="sort_by" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white">
                                <option value="created_at_desc" <?php echo ($sort_by == 'created_at_desc') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="created_at_asc" <?php echo ($sort_by == 'created_at_asc') ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="title_asc" <?php echo ($sort_by == 'title_asc') ? 'selected' : ''; ?>>Title (A-Z)</option>
                                <option value="title_desc" <?php echo ($sort_by == 'title_desc') ? 'selected' : ''; ?>>Title (Z-A)</option>
                            </select>
                        </div>
                        <button type="submit" class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white shadow-md transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg dark:bg-white dark:text-primary dark:hover:shadow-white/20">Apply Filters</button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if (empty($notes)): ?>
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-background-light dark:bg-background-dark p-6 rounded-lg shadow-md border border-primary/10 dark:border-primary/20 text-center">
                            <p class="text-black/60 dark:text-white/60 text-lg font-medium">No notes found matching your search criteria.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Try adjusting your filters or search term.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="bg-background-light dark:bg-background-dark p-6 rounded-lg shadow-lg dark:shadow-xl flex flex-col justify-between border-2 border-primary/30 dark:border-primary/40">
                                <div>
                                    <h3 class="text-xl font-bold text-primary dark:text-white mb-2"><?php echo htmlspecialchars($note['title']); ?></h3>
                                    <p class="text-black/60 dark:text-white/60 mb-2">Subject: <?php echo htmlspecialchars($note['subject']); ?></p>
                                    <p class="text-black/60 dark:text-white/60 mb-2">College: <?php echo htmlspecialchars($note['college']); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Uploaded by <?php echo htmlspecialchars($note['uploader_name']); ?> on <?php echo date('F j, Y', strtotime($note['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center justify-between mt-4 flex-wrap">
                                    <div class="flex items-center gap-4">
                                        <a href="download.php?id=<?php echo $note['id']; ?>" class="text-primary hover:underline dark:text-white flex-shrink-0 transition-all duration-200 hover:scale-110 hover:shadow-md"><span class="material-symbols-outlined text-base mr-1">download</span>Download</a>
                                        <?php if (isset($_SESSION['id']) && $_SESSION['id'] == $note['uploader_id']): ?>
                                            <a href="delete_note.php?id=<?php echo $note['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="text-red-500 hover:underline flex-shrink-0 text-xs transition-all duration-200 hover:scale-110 hover:shadow-md" onclick="return confirm('Are you sure you want to delete this note?');"><span class="material-symbols-outlined text-xs mr-1">delete</span>Delete</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center gap-4 min-w-0">
                                        <span class="text-black/60 dark:text-white/60 flex items-center">
                                            <span class="material-symbols-outlined text-base mr-1">download</span>
                                            <?php echo htmlspecialchars($note['download_count']); ?>
                                        </span>
                                        <button class="like-btn <?php echo $note['user_liked'] ? 'text-red-500' : 'text-gray-500'; ?> flex items-center" data-note-id="<?php echo $note['id']; ?>">
                                            <span class="material-symbols-outlined text-base mr-1">favorite</span>
                                            <span class="like-count"><?php echo htmlspecialchars($note['like_count']); ?></span>
                                        </button>
                                        <?php
$bookmark_class = "bookmark-btn";
$bookmark_class .= (($note['user_bookmarked'] ?? 0) ? ' text-blue-500' : ' text-gray-500');
?>
                                        <button class="<?php echo $bookmark_class; ?>" data-note-id="<?php echo $note['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21.75a.75.75 0 01-1.06.676L12 20.042l-7.359 4.359a.75.75 0 01-1.06-.676V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

<?php require_once 'includes/footer.php'; ?>