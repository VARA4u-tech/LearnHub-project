<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/api_keys.php';

function get_google_client() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope('email');
    $client->addScope('profile');
    $client->addScope('https://www.googleapis.com/auth/drive.file');
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setDeveloperKey(GOOGLE_API_KEY);

    if (isset($_SESSION['google_access_token']) && $_SESSION['google_access_token']) {
        $client->setAccessToken($_SESSION['google_access_token']);
    }

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $token_response = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            if (isset($token_response['error'])) {
                // Refresh token failed, clear session and redirect for re-authentication
                session_destroy();
                header('Location: /LearnHub/gauth.php');
                exit;
            }
            $_SESSION['google_access_token'] = $token_response;
        }
    }

    return $client;
}

function get_drive_service() {
    $client = get_google_client();
    if ($client) {
        return new Google_Service_Drive($client);
    }
    return null;
}

function upload_file_to_drive($tmp_path, $file_name, $file_type) {
    $drive_service = get_drive_service();
    if (!$drive_service) {
        return null;
    }
    $folderId = GOOGLE_DRIVE_FOLDER_ID;

    $file = new Google_Service_Drive_DriveFile([
        'name' => $file_name,
        'parents' => [$folderId]
    ]);

    $content = file_get_contents($tmp_path);

    $createdFile = $drive_service->files->create($file, [
        'data' => $content,
        'mimeType' => $file_type,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    return $createdFile->id;
}

function download_file_from_drive($file_id) {
    $drive_service = get_drive_service();
    if (!$drive_service) {
        return;
    }
    
    $response = $drive_service->files->get($file_id, ['alt' => 'media']);
    return $response->getBody()->getContents();
}

function delete_file_from_drive($file_id) {
    $drive_service = get_drive_service();
    if (!$drive_service) {
        return false;
    }

    try {
        $drive_service->files->delete($file_id);
        return true;
    } catch (Exception $e) {
        error_log('Google Drive file deletion failed: ' . $e->getMessage());
        return false;
    }
}
?>