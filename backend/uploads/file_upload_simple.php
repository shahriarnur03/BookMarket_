<?php
/**
 * Simplified File Upload API
 * This is a temporary version to test basic functionality
 */

// Prevent any output before JSON response
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer
    ob_clean();
    
    // Disable error display (only log to file)
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Log the incoming request
    error_log("File Upload Request: " . json_encode($_POST));
    error_log("Files: " . json_encode($_FILES));
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload_book_images':
                if (!isset($_FILES['images'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No files uploaded'
                    ]);
                    break;
                }
                
                $uploadedFiles = [];
                $errors = [];
                
                // Handle single file upload
                if (!is_array($_FILES['images']['name'])) {
                    $_FILES['images'] = [
                        'name' => [$_FILES['images']['name']],
                        'type' => [$_FILES['images']['type']],
                        'tmp_name' => [$_FILES['images']['tmp_name']],
                        'error' => [$_FILES['images']['error']],
                        'size' => [$_FILES['images']['size']]
                    ];
                }
                
                // Process each file
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    $fileName = $_FILES['images']['name'][$i];
                    $fileType = $_FILES['images']['type'][$i];
                    $fileTmpName = $_FILES['images']['tmp_name'][$i];
                    $fileError = $_FILES['images']['error'][$i];
                    $fileSize = $_FILES['images']['size'][$i];
                    
                    // Skip if no file uploaded
                    if ($fileError === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    
                    // Basic validation
                    if ($fileError !== UPLOAD_ERR_OK) {
                        $errors[] = $fileName . ': Upload error code ' . $fileError;
                        continue;
                    }
                    
                    // Check file size (5MB limit)
                    if ($fileSize > 5 * 1024 * 1024) {
                        $errors[] = $fileName . ': File size exceeds 5MB limit';
                        continue;
                    }
                    
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($fileType, $allowedTypes)) {
                        $errors[] = $fileName . ': Invalid file type. Only images are allowed';
                        continue;
                    }
                    
                    // Generate unique filename
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = 'file_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
                    
                                                    // Create upload directory if it doesn't exist
                $uploadDir = '/Applications/XAMPP/xamppfiles/htdocs/BookMarket_/uploads/books/';
                error_log("Upload directory path: $uploadDir");
                
                if (!is_dir($uploadDir)) {
                    error_log("Creating upload directory: $uploadDir");
                    $result = mkdir($uploadDir, 0755, true);
                    error_log("Directory creation result: " . ($result ? 'success' : 'failed'));
                } else {
                    error_log("Upload directory already exists: $uploadDir");
                }
                    
                                    // Determine upload path
                $uploadPath = $uploadDir . $uniqueFileName;
                error_log("Full upload path: $uploadPath");
                error_log("Temp file path: $fileTmpName");
                
                // Move uploaded file
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    error_log("File moved successfully to: $uploadPath");
                    $uploadedFiles[] = [
                        'original_name' => $fileName,
                        'file_name' => $uniqueFileName,
                        'file_path' => 'uploads/books/' . $uniqueFileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType
                    ];
                } else {
                    $errorMsg = $fileName . ': Failed to move uploaded file';
                    error_log("File move failed: $errorMsg");
                    error_log("PHP error: " . error_get_last()['message'] ?? 'No error info');
                    $errors[] = $errorMsg;
                }
                }
                
                if (empty($uploadedFiles)) {
                    // No files uploaded successfully, return error
                    error_log("No files uploaded successfully");
                    $errorMessage = 'File upload failed. ';
                    if (!empty($errors)) {
                        $errorMessage .= 'Errors: ' . implode(', ', $errors);
                    }
                    
                    echo json_encode([
                        'success' => false,
                        'message' => $errorMessage,
                        'files' => [],
                        'errors' => $errors
                    ]);
                } else {
                    $response = [
                        'success' => true,
                        'message' => 'Files uploaded successfully',
                        'files' => $uploadedFiles
                    ];
                    
                    if (!empty($errors)) {
                        $response['message'] = 'Some files uploaded with errors';
                        $response['errors'] = $errors;
                    }
                    
                    error_log("Upload response: " . json_encode($response));
                    echo json_encode($response);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        error_log("General error in file upload: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
