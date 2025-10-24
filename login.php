<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

require_once "config/database.php";

$database = new Database(); // Instantiate the Database class
$pdo = $database->getConnection(); // Get the PDO connection

$errors = [];
$email = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $errors[] = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($errors)) {
        $sql = "SELECT id, name, email, password FROM users WHERE email = :email";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $name = $row["name"];
                        $hashed_password = $row["password"];
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["name"] = $name;
                            $_SESSION["email"] = $email;

                            header("location: dashboard.php");
                        } else {
                            $errors[] = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $errors[] = "No account found with that email.";
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
            }
            unset($stmt);
        }
    }
    unset($pdo);
}
?>
<?php require_once 'includes/header.php'; ?>
<main class="flex-grow flex items-center justify-center p-4">
    <div class="min-h-screen flex flex-col lg:flex-row-reverse w-full">
        <!-- Image Container -->
        <div class="hidden lg:block lg:w-1/2 bg-cover bg-center flex-1"
            style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.4) 100%), url("https://w0.peakpx.com/wallpaper/96/604/HD-wallpaper-moon-moon-night.jpg");'>
        </div>

        <!-- Content Container -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md mx-auto bg-background-light dark:bg-background-dark p-8 md:p-10 border-2 border-primary/30 dark:border-primary/40 rounded-xl shadow-lg dark:shadow-xl">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold tracking-tight">Welcome Back👋🏽.</h1>
                    <p class="text-black/60 dark:text-white/60 mt-2">Log in to continue your learning journey.</p>
                </div>
                <?php
                if (!empty($errors)) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
                    foreach ($errors as $error) {
                        echo "<span class='block sm:inline'>$error</span><br>";
                    }
                    echo '</div>';
                }
                ?>
                <form class="space-y-6" action="login.php" method="POST">
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="email">Email address:</label>
                        <div class="mt-1">
                            <input autocomplete="email" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="email" name="email" placeholder="you@example.com" required="" type="email"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="password">Password:</label>
                        <div class="mt-1">
                            <input autocomplete="current-password" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="password" name="password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>
                    <div>
                        <button class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white shadow-md transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg dark:bg-white dark:text-primary dark:hover:shadow-white/20" type="submit">Sign-In</button>
                    </div>
                    <div class="mt-6 text-center">
                        <p class="text-sm text-black/60 dark:text-white/60">Or</p>
                        <a href="gauth.php?v=<?php echo time(); ?>" class="w-full inline-flex items-center justify-center py-3 px-4 border border-primary/20 dark:border-white/20 rounded-lg shadow-sm text-sm font-bold text-primary dark:text-white bg-white dark:bg-background-dark hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <img class="h-5 w-5 mr-2" src="https://www.svgrepo.com/show/355037/google.svg" alt="Google sign-in">
                            Sign-in with Google
                        </a>
                    </div>
                </form>
                <p class="mt-8 text-center text-sm text-primary/60 dark:text-white/60">
                    Don't have an account?
                    <a class="font-medium text-primary underline-offset-4 hover:underline dark:text-white" href="register.php">Sign-up</a>
                </p>
            </div>
        </div>
    </div>
</main>

</body></html>