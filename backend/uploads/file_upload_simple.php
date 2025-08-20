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
                    $uploadDir = dirname(dirname(dirname(__DIR__))) . '/uploads/books/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Determine upload path
                    $uploadPath = $uploadDir . $uniqueFileName;
                    
                    // Move uploaded file
                    if (move_uploaded_file($fileTmpName, $uploadPath)) {
                        $uploadedFiles[] = [
                            'original_name' => $fileName,
                            'file_name' => $uniqueFileName,
                            'file_path' => 'uploads/books/' . $uniqueFileName,
                            'file_size' => $fileSize,
                            'file_type' => $fileType
                        ];
                    } else {
                        $errors[] = $fileName . ': Failed to move uploaded file';
                    }
                }
                
                if (empty($uploadedFiles)) {
                    // Allow empty uploads for admin book creation
                    echo json_encode([
                        'success' => true,
                        'message' => 'No files uploaded (optional)',
                        'files' => []
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
