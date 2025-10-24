<?php
session_start();
require_once 'config/database.php';

$database = new Database(); // Instantiate the Database class
$pdo = $database->getConnection(); // Get the PDO connection

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $college = trim($_POST['college']);

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = :email";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                $errors[] = "This email is already taken";
            }
        }
    } else {
        $errors[] = "Oops! Something went wrong. Please try again later.";
    }
    unset($stmt);

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, college) VALUES (:name, :email, :password, :college)";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(":college", $college, PDO::PARAM_STR);

            if ($stmt->execute()) {
                header("location: login.php");
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
    <div class="min-h-screen flex flex-col lg:flex-row w-full">
        <!-- Image Container -->
        <div class="hidden lg:block lg:w-1/2 bg-cover bg-center flex-1"
            style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.4) 100%), url("https://w0.peakpx.com/wallpaper/96/604/HD-wallpaper-moon-moon-night.jpg");'>
        </div>

        <!-- Content Container -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md mx-auto bg-background-light dark:bg-background-dark p-8 md:p-10 border-2 border-primary/30 dark:border-primary/40 rounded-xl shadow-lg dark:shadow-xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-primary dark:text-white">Create your account</h2>
                    <p class="mt-2 text-sm text-primary/60 dark:text-white/60">Join LearnHub and start sharing your knowledge.</p>
                    <p class="mt-2 text-sm text-primary/60 dark:text-white/60">💥USER DISCLAIMER:"Please be sure you remember your login credentials before signing in, coz our platform does not provide password reset link.we highly recommend using the "Continue with Google"option".</p>
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
                <form action="register.php" class="space-y-6" method="POST">
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="name">Name:</label>
                        <div class="mt-1">
                            <input autocomplete="name" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="name" name="name" placeholder="Enter your name" required="" type="text"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="email">Email address:</label>
                        <div class="mt-1">
                            <input autocomplete="email" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="email" name="email" placeholder="you@example.com" required="" type="email"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="password">Password:</label>
                        <div class="mt-1">
                            <input autocomplete="new-password" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="password" name="password" placeholder="••••••••" required="" type="password"/>
                        </div>
                        <div class="mt-2">
                            <div class="h-1 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-1 rounded-full bg-primary dark:bg-white" id="password-strength" style="width: 10%"></div>
                            </div>
                            <p class="mt-1 text-xs text-primary/60 dark:text-white/60" id="password-strength-text">Weak</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="confirm-password">Confirm Password:</label>
                        <div class="mt-1">
                            <input autocomplete="new-password" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="confirm-password" name="confirm_password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-primary dark:text-white" for="name">College:</label>
                        <div class="mt-1">
                            <input autocomplete="name" class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white" id="name" name="name" placeholder="Enter your college name" required="" type="text"/>
                        </div>
                    </div>
                    <div>
                        <button class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white shadow-md transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg dark:bg-white dark:text-primary dark:hover:shadow-white/20" type="submit">Sign-up</button>
                    </div>
                    <div class="mt-6 text-center">
                        <p class="text-sm text-black/60 dark:text-white/60">Or</p>
                        <a href="gauth.php?v=<?php echo time(); ?>" class="w-full inline-flex items-center justify-center py-3 px-4 border border-primary/20 dark:border-white/20 rounded-lg shadow-sm text-sm font-bold text-primary dark:text-white bg-white dark:bg-background-dark hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <img class="h-5 w-5 mr-2" src="https://www.svgrepo.com/show/355037/google.svg" alt="Google sign-up">
                            Sign-up with Google
                        </a>
                    </div>
                </form>
                <p class="mt-8 text-center text-sm text-primary/60 dark:text-white/60">
                    Already have an account?
                    <a class="font-medium text-primary underline-offset-4 hover:underline dark:text-white" href="login.php">Log-in</a>
                </p>
            </div>
        </div>
    </div>
</main>
</body></html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('password-strength');
        const passwordStrengthText = document.getElementById('password-strength-text');

        console.log('Password input element:', passwordInput);
        console.log('Strength bar element:', passwordStrengthBar);
        console.log('Strength text element:', passwordStrengthText);

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                console.log('Password input event fired. Current password:', password);
                let strength = 0;
                let feedback = 'Weak';
                let barWidth = '10%';
                let barColor = 'red';

                // Criteria for strength
                const hasLowercase = /[a-z]/.test(password);
                const hasUppercase = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                const isLongEnough = password.length >= 8;

                if (isLongEnough) {
                    strength++;
                }
                if (hasLowercase) {
                    strength++;
                }
                if (hasUppercase) {
                    strength++;
                }
                if (hasNumber) {
                    strength++;
                }
                if (hasSpecialChar) {
                    strength++;
                }

                // Determine feedback and bar style
                if (strength <= 2) {
                    feedback = 'Weak';
                    barWidth = '30%';
                    barColor = 'red';
                } else if (strength === 3 || strength === 4) {
                    feedback = 'Medium';
                    barWidth = '60%';
                    barColor = 'orange';
                } else if (strength === 5) {
                    feedback = 'Strong';
                    barWidth = '100%';
                    barColor = 'green';
                }

                passwordStrengthBar.style.width = barWidth;
                passwordStrengthBar.style.backgroundColor = barColor;
                passwordStrengthText.textContent = feedback;
                passwordStrengthText.style.color = barColor;
            });
        }
    });
</script>