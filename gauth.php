<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';
require_once 'includes/google_api_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$client = get_google_client();

if (isset($_GET['code'])) {
    // Exchange authorization code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['google_access_token'] = $token;
    $client->setAccessToken($token['access_token']);

    // Get user profile information
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    // Check if user exists in your database
    $sql = "SELECT id, name, email FROM users WHERE email = :email";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                // User exists, log them in
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];

                header("location: dashboard.php");
                exit;
            } else {
                // User does not exist, redirect to google_register.php
                $_SESSION['google_email'] = $email;
                $_SESSION['google_name'] = $name;
                header("location: google_register.php");
                exit;
            }
        } else { // Error executing select statement
            $_SESSION['error'] = "Oops! Something went wrong during login. Please try again later.";
            header("location: login.php");
            exit;
        }
    } else { // Error preparing select statement
        $_SESSION['error'] = "Database error during login. Please try again later.";
        header("location: login.php");
        exit;
    }
} else {
    // If not a callback, redirect to Google's login page
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}
?>