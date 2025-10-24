<?php
session_start();
require_once 'includes/google_api_helper.php';
require_once 'config/database.php';

// Enhanced session validation
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$database = new Database();
$pdo = $database->getConnection();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $college = trim($_POST['college'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $uploader_id = $_SESSION['id'] ?? null;
    $uploader_name = $_SESSION['name'] ?? '';

    // Validate inputs
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($college)) {
        $errors[] = "College is required";
    }
    
    if (!$uploader_id) {
        $errors[] = "User session invalid. Please login again.";
    }

    // File upload validation
    if (isset($_FILES["note_file"]) && $_FILES["note_file"]["error"] == UPLOAD_ERR_OK) {
        $allowed_types = [
            'application/pdf', 
            'image/jpeg', 
            'image/png', 
            'image/jpg',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $max_size = 10 * 1024 * 1024; // 10MB

        $file_name = basename($_FILES["note_file"]["name"]);
        $file_type = $_FILES["note_file"]["type"];
        $file_size = $_FILES["note_file"]["size"];
        $file_tmp_name = $_FILES["note_file"]["tmp_name"];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Invalid file type. Only PDF, JPG, PNG, DOC, and DOCX are allowed.";
        }

        if ($file_size > $max_size) {
            $errors[] = "File size exceeds the maximum limit of 10MB.";
        }

        if ($file_size == 0) {
            $errors[] = "Uploaded file is empty.";
        }

    } else {
        $upload_error = $_FILES["note_file"]["error"] ?? UPLOAD_ERR_NO_FILE;
        switch ($upload_error) {
            case UPLOAD_ERR_INI_SIZE:
                $errors[] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "The uploaded file exceeds the MAX_FILE_SIZE directive in the form.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = "No file was uploaded. Please select a file.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errors[] = "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errors[] = "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errors[] = "A PHP extension stopped the file upload.";
                break;
            default:
                $errors[] = "Unknown upload error occurred.";
                break;
        }
    }

    if (empty($errors)) {
        $google_drive_file_id = upload_file_to_drive($file_tmp_name, $file_name, $file_type);

        if ($google_drive_file_id) {
            try {
                $sql = "INSERT INTO notes (title, subject, description, file_name, file_path, file_size, file_type, uploader_id, uploader_name, college) 
                        VALUES (:title, :subject, :description, :file_name, :file_path, :file_size, :file_type, :uploader_id, :uploader_name, :college)";

                $stmt = $pdo->prepare($sql);
                
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":subject", $subject, PDO::PARAM_STR);
                $stmt->bindParam(":description", $description, PDO::PARAM_STR);
                $stmt->bindParam(":file_name", $file_name, PDO::PARAM_STR);
                $stmt->bindParam(":file_path", $google_drive_file_id, PDO::PARAM_STR);
                $stmt->bindParam(":file_size", $file_size, PDO::PARAM_INT);
                $stmt->bindParam(":file_type", $file_type, PDO::PARAM_STR);
                $stmt->bindParam(":uploader_id", $uploader_id, PDO::PARAM_INT);
                $stmt->bindParam(":uploader_name", $uploader_name, PDO::PARAM_STR);
                $stmt->bindParam(":college", $college, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Note uploaded successfully!";
                    header("location: dashboard.php");
                    exit();
                } else {
                    $errors[] = "Database error: Failed to save note details.";
                    delete_file_from_drive($google_drive_file_id);
                }
                
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
                delete_file_from_drive($google_drive_file_id);
            }
        } else {
            $errors[] = "Failed to upload file to Google Drive.";
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

        <main class="flex-grow flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="w-full max-w-2xl mx-auto">
                <div class="text-center mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-primary dark:text-white">Upload Your Notes</h1>
                    <p class="mt-2 text-base text-black/60 dark:text-white/60">Share your knowledge with the community.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error(s) occurred:</strong>
                        <ul class="mt-1 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="bg-background-light dark:bg-background-dark p-8 md:p-10 border-2 border-primary/30 dark:border-primary/40 rounded-xl shadow-lg dark:shadow-xl">
                    <form class="space-y-6" action="upload.php" method="POST" enctype="multipart/form-data">
                        <div>
                            <label class="block text-sm font-medium text-primary dark:text-white mb-2" for="title">
                                Title *
                            </label>
                            <input
                                class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white"
                                id="title"
                                placeholder="e.g. Introduction to Calculus"
                                type="text"
                                name="title"
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary dark:text-white mb-2" for="subject">
                                Subject *
                            </label>
                            <input
                                class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white"
                                id="subject"
                                placeholder="e.g. Mathematics"
                                type="text"
                                name="subject"
                                value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary dark:text-white mb-2" for="college">
                                College
                            </label>
                            <input
                                class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white"
                                id="college"
                                placeholder="e.g. XYZ College of Engineering"
                                type="text"
                                name="college"
                                value="<?php echo htmlspecialchars($_POST['college'] ?? ''); ?>"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary dark:text-white mb-2" for="description">
                                Description
                            </label>
                            <textarea
                                class="block w-full appearance-none rounded-lg border border-primary/20 bg-background-light px-3 py-2 placeholder-primary/40 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-white/20 dark:bg-background-dark dark:placeholder-white/40 dark:focus:border-white dark:focus:ring-white"
                                id="description"
                                placeholder="A brief description of your notes..."
                                rows="4"
                                name="description"
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary dark:text-white mb-2" for="note_file">
                                File Upload *
                            </label>
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-primary/20 dark:border-white/20 border-dashed rounded-lg hover:border-primary transition-colors">
                                <div class="space-y-2 text-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500">cloud_upload</span>
                                    <div class="flex flex-col sm:flex-row text-sm text-primary/60 dark:text-white/60">
                                        <label class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary">
                                            <span>Choose a file</span>
                                            <input
                                                class="sr-only"
                                                id="note_file"
                                                name="note_file"
                                                type="file"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                required
                                            />
                                        </label>
                                        <p class="sm:pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-primary/60 dark:text-white/60">PDF, DOC, DOCX, JPG, PNG up to 10MB</p>
                                    <p id="file-selected" class="text-sm text-green-600 dark:text-green-400 font-medium hidden">File selected</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white shadow-md transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg dark:bg-white dark:text-primary dark:hover:shadow-white/20"
                                type="submit"
                            >
                                <span class="material-symbols-outlined mr-2">✅</span>
                                Upload Notes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // File selection feedback
        document.getElementById('note_file').addEventListener('change', function(e) {
            const fileSelected = document.getElementById('file-selected');
            if (this.files.length > 0) {
                fileSelected.textContent = 'Selected: ' + this.files[0].name;
                fileSelected.classList.remove('hidden');
            } else {
                fileSelected.classList.add('hidden');
            }
        });

        // Drag and drop functionality
        const dropArea = document.querySelector('.border-dashed');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('border-primary', 'bg-primary/10', 'shadow-lg');
        }

        function unhighlight() {
            dropArea.classList.remove('border-primary', 'bg-primary/10', 'shadow-lg');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            // Create a new DataTransfer object and add the dropped files
            const newDt = new DataTransfer();
            for (let i = 0; i < files.length; i++) {
                newDt.items.add(files[i]);
            }
            document.getElementById('note_file').files = newDt.files;
            
            const fileSelected = document.getElementById('file-selected');
            if (files.length > 0) {
                fileSelected.textContent = 'Selected: ' + files[0].name;
                fileSelected.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>