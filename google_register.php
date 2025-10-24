<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['google_email']) || !isset($_SESSION['google_name'])) {
    header("location: register.php");
    exit;
}

$database = new Database();
$pdo = $database->getConnection();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $college = trim($_POST['college']);

    if (empty($college)) {
        $errors[] = "College is required";
    }

    if (empty($errors)) {
        $name = $_SESSION['google_name'];
        $email = $_SESSION['google_email'];
        $placeholder_password = password_hash(uniqid(), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, college) VALUES (:name, :email, :password, :college)";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $placeholder_password, PDO::PARAM_STR);
            $stmt->bindParam(":college", $college, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $pdo->lastInsertId();
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;

                unset($_SESSION['google_name']);
                unset($_SESSION['google_email']);

                header("location: dashboard.php");
                exit;
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
            unset($stmt);
        }
    }
    unset($pdo);
}
?>
<?php require_once 'includes/header.php'; ?>
<main class="flex-grow flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto bg-background-light dark:bg-background-dark p-8 md:p-10 border-2 border-primary/30 dark:border-primary/40 rounded-xl shadow-lg dark:shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight">One More Step...</h1>
            <p class="text-black/60 dark:text-white/60 mt-2">Please provide your college name to complete your registration.</p>
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
        <form class="space-y-6" action="google_register.php" method="POST">
            <div>
                <label class="block text-sm font-medium text-primary dark:text-white" for="college">College:</label>
                <div class="mt-1">
                    <input autocomplete="organization" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="college" name="college" placeholder="Enter your college name" required="" type="text"/>
                </div>
            </div>
            <div>
                <button class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white shadow-md transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg dark:bg-white dark:text-primary dark:hover:shadow-white/20" type="submit">Complete Registration</button>
            </div>
        </form>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
