<?php
// Ensure session is started if not already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>LearnHub</title>
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
<body class="bg-background-light dark:bg-background-dark font-display text-black dark:text-white">
    <div class="flex flex-col min-h-screen">
        <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm border-b border-primary/10 dark:border-primary/20">
            <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <a class="flex items-center gap-2" href="index.php">
                            <img src="assets/images/WhatsApp%20Image.jpg.jpg" alt="LearnHub Logo" class="h-10">
                            <span class="text-xl font-bold text-primary dark:text-white">LearnHub</span>
                        </a>
                    </div>
                    <div class="hidden md:flex items-center space-x-8">
                        <a class="flex items-center gap-1 text-sm font-medium hover:text-primary dark:hover:text-white transition-colors nav-link-underline" href="index.php"><span class="material-symbols-outlined">home</span>Home</a>
                        <a class="flex items-center gap-1 text-sm font-medium hover:text-primary dark:hover:text-white transition-colors nav-link-underline" href="notes.php"><span class="material-symbols-outlined">search</span>Search</a>
                        <a class="flex items-center gap-1 text-sm font-medium hover:text-primary dark:hover:text-white transition-colors nav-link-underline" href="upload.php"><span class="material-symbols-outlined">upload</span>Upload</a>
                        <a class="flex items-center gap-1 text-sm font-medium hover:text-primary dark:hover:text-white transition-colors nav-link-underline" href="dashboard.php"><span class="material-symbols-outlined">space_dashboard</span>My Notes</a>
                        
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-4">
                            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                <a href="admin_blog.php" class="flex items-center gap-1 text-sm font-medium hover:text-primary dark:hover:text-white transition-colors nav-link-underline"><span class="material-symbols-outlined">edit_note</span>Admin Blog</a>
                                <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-sm font-medium text-black"><?php echo substr($_SESSION['name'] ?? 'U', 0, 1); ?></span>
                                </div>
                                <a href="logout.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 transition-all transform hover:scale-105"><span class="material-symbols-outlined">logout</span>Logout</a>
                            <?php else: ?>
                                <a href="login.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 transition-all transform hover:scale-105">Login</a>
                                <a href="register.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 transition-all transform hover:scale-105">Register</a>
                            <?php endif; ?>
                        </div>
                        <!-- Mobile menu button-->
                        <div class="flex items-center md:hidden">
                            <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-primary/70 dark:text-white/70 hover:text-primary dark:hover:text-white focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
                                <span class="sr-only">Open main menu</span>
                                <svg class="block h-6 w-6 menu-open-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                <svg class="hidden h-6 w-6 menu-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu, show/hide based on menu state. -->
                <div class="md:hidden hidden" id="mobile-menu">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="index.php" class="flex items-center gap-1 block px-3 py-2 rounded-md text-base font-medium text-primary dark:text-white hover:bg-primary/5 dark:hover:bg-white/10"><span class="material-symbols-outlined">home</span>Home</a>
                        <a href="notes.php" class="flex items-center gap-1 block px-3 py-2 rounded-md text-base font-medium text-primary dark:text-white hover:bg-primary/5 dark:hover:bg-white/10"><span class="material-symbols-outlined">search</span>Search</a>
                        <a href="upload.php" class="flex items-center gap-1 block px-3 py-2 rounded-md text-base font-medium text-primary dark:text-white hover:bg-primary/5 dark:hover:bg-white/10"><span class="material-symbols-outlined">upload</span>Upload</a>
                        <a href="dashboard.php" class="flex items-center gap-1 block px-3 py-2 rounded-md text-base font-medium text-primary dark:text-white hover:bg-primary/5 dark:hover:bg-white/10"><span class="material-symbols-outlined">space_dashboard</span>My Notes</a>
                        
                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <a href="admin_blog.php" class="flex items-center gap-1 block px-3 py-2 rounded-md text-base font-medium text-primary dark:text-white hover:bg-primary/5 dark:hover:bg-white/10"><span class="material-symbols-outlined">edit_note</span>Admin Blog</a>
                            <hr class="my-2 border-primary/10 dark:border-white/10"/>
                            <div class="px-3 py-2">
                                <a href="logout.php" class="w-full text-left block px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm"><span class="material-symbols-outlined">logout</span>Logout</a>
                            </div>
                        <?php else: ?>
                            <hr class="my-2 border-primary/10 dark:border-white/10"/>
                            <div class="px-3 py-2 space-y-2">
                                <a href="login.php" class="w-full text-center block px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm">Login</a>
                                <a href="register.php" class="w-full text-center block px-4 py-2 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </header>
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 fade-in">
<?php include_once 'includes/chatbot.php'; ?>
<script src="assets/js/chatbot.js" defer></script>
<script src="assets/js/main.js" defer></script>