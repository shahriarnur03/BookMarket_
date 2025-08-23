<?php
/**
 * File Upload System
 * Handles file uploads for book images and profile pictures
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Prevent any output before JSON response
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Include required files
require_once __DIR__ . '/../config/session.php';

/**
 * File Upload Manager Class
 * Manages all file upload operations
 */
class FileUploadManager {
    private $uploadDir;
    private $maxFileSize;
    private $allowedImageTypes;
    
    public function __construct() {
        // Set upload directory to the root uploads folder
        $this->uploadDir = dirname(dirname(__DIR__)) . '/uploads/';
        
        // Debug logging
        error_log("Upload directory path: " . $this->uploadDir);
        error_log("Upload directory exists: " . (is_dir($this->uploadDir) ? 'Yes' : 'No'));
        error_log("Upload directory writable: " . (is_writable($this->uploadDir) ? 'Yes' : 'No'));
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            $created = mkdir($this->uploadDir, 0777, true);
            error_log("Created upload directory: " . ($created ? 'Yes' : 'No'));
        }
        
        // Create subdirectories
        $subdirs = ['books', 'profiles', 'temp'];
        foreach ($subdirs as $subdir) {
            $path = $this->uploadDir . $subdir . '/';
            if (!is_dir($path)) {
                $created = mkdir($path, 0777, true);
                error_log("Created subdirectory {$subdir}: " . ($created ? 'Yes' : 'No'));
            }
            
            // Ensure directory is writable
            if (!is_writable($path)) {
                chmod($path, 0777);
                error_log("Changed permissions for {$subdir} to 777");
            }
            
            error_log("Subdirectory {$subdir} path: " . $path . " (exists: " . (is_dir($path) ? 'Yes' : 'No') . ", writable: " . (is_writable($path) ? 'Yes' : 'No') . ")");
        }
        
        // Set upload limits - check PHP configuration
        $phpMaxUploadSize = min(
            $this->parseSize(ini_get('upload_max_filesize')),
            $this->parseSize(ini_get('post_max_size')),
            5 * 1024 * 1024 // 5MB hard limit
        );
        $this->maxFileSize = $phpMaxUploadSize;
        error_log("Max file size set to: " . ($this->maxFileSize / (1024 * 1024)) . "MB");
        error_log("PHP upload_max_filesize: " . ini_get('upload_max_filesize'));
        error_log("PHP post_max_size: " . ini_get('post_max_size'));
        $this->allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    }
    
    /**
     * Upload book image(s)
     * @param array $files $_FILES array
     * @param string $uploadType Type of upload ('cover' or 'additional')
     * @return array Result with success status and file paths
     */
    public function uploadBookImages($files, $uploadType = 'cover') {
        try {
            error_log("Starting uploadBookImages with type: " . $uploadType);
            error_log("Files structure: " . print_r($files, true));
            
            $uploadedFiles = [];
            $errors = [];
            
            // Handle single file upload
            if (!is_array($files['name'])) {
                $files = [
                    'name' => [$files['name']],
                    'type' => [$files['type']],
                    'tmp_name' => [$files['tmp_name']],
                    'error' => [$files['error']],
                    'size' => [$files['size']]
                ];
            }
            
            // Process each file
            for ($i = 0; $i < count($files['name']); $i++) {
                $fileName = $files['name'][$i];
                $fileType = $files['type'][$i];
                $fileTmpName = $files['tmp_name'][$i];
                $fileError = $files['error'][$i];
                $fileSize = $files['size'][$i];
                
                // Skip if no file uploaded
                if ($fileError === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                
                // Validate file
                error_log("Validating file: " . $fileName . " (type: " . $fileType . ", size: " . $fileSize . ", error: " . $fileError . ")");
                $validation = $this->validateFile($fileName, $fileType, $fileSize, $fileError, $fileTmpName);
                error_log("Validation result: " . print_r($validation, true));
                
                if (!$validation['valid']) {
                    $errors[] = $fileName . ': ' . $validation['message'];
                    continue;
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = $this->generateUniqueFileName($fileExtension);
                
                // Determine upload path
                $uploadPath = $this->uploadDir . 'books/' . $uniqueFileName;
                error_log("Upload path: " . $uploadPath);
                error_log("Upload directory: " . $this->uploadDir);
                error_log("Full path: " . realpath($this->uploadDir));
                
                // Move uploaded file
                error_log("Moving file from " . $fileTmpName . " to " . $uploadPath);
                
                // Check if destination directory is writable
                $destDir = dirname($uploadPath);
                if (!is_writable($destDir)) {
                    $errorMsg = "Destination directory not writable: " . $destDir;
                    error_log($errorMsg);
                    $errors[] = $fileName . ': ' . $errorMsg;
                    continue;
                }
                
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    error_log("File moved successfully");
                    $uploadedFiles[] = [
                        'original_name' => $fileName,
                        'file_name' => $uniqueFileName,
                        'file_path' => 'uploads/books/' . $uniqueFileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType
                    ];
                } else {
                    $lastError = error_get_last();
                    $errorMsg = "Failed to move uploaded file";
                    if ($lastError) {
                        $errorMsg .= ": " . $lastError['message'];
                    }
                    error_log($errorMsg);
                    $errors[] = $fileName . ': ' . $errorMsg;
                }
            }
            
            if (empty($uploadedFiles)) {
                $errorMessage = 'No files were uploaded successfully';
                if (!empty($errors)) {
                    $errorMessage .= '. Errors: ' . implode(', ', $errors);
                }
                return ['success' => false, 'message' => $errorMessage, 'errors' => $errors];
            }
            
            if (!empty($errors)) {
                return [
                    'success' => true,
                    'message' => 'Some files uploaded with errors',
                    'files' => $uploadedFiles,
                    'errors' => $errors
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Files uploaded successfully',
                'files' => $uploadedFiles
            ];
            
        } catch (Exception $e) {
            error_log("Upload Book Images Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'File upload failed. Please try again.'];
        }
    }
    
    /**
     * Upload profile picture
     * @param array $file $_FILES array for single file
     * @return array Result with success status and file path
     */
    public function uploadProfilePicture($file) {
        try {
            // Validate file
            $validation = $this->validateFile(
                $file['name'],
                $file['type'],
                $file['size'],
                $file['error'],
                $file['tmp_name']
            );
            
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFileName = $this->generateUniqueFileName($fileExtension);
            
            // Determine upload path
            $uploadPath = $this->uploadDir . 'profiles/' . $uniqueFileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return [
                    'success' => true,
                    'message' => 'Profile picture uploaded successfully',
                    'file_name' => $uniqueFileName,
                    'file_path' => 'uploads/profiles/' . $uniqueFileName
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to move uploaded file'];
            }
            
        } catch (Exception $e) {
            error_log("Upload Profile Picture Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile picture upload failed. Please try again.'];
        }
    }
    
    /**
     * Delete uploaded file
     * @param string $filePath File path relative to uploads directory
     * @return array Result with success status and message
     */
    public function deleteFile($filePath) {
        try {
            // Validate file path (prevent directory traversal)
            if (strpos($filePath, '..') !== false || strpos($filePath, '/') === 0) {
                return ['success' => false, 'message' => 'Invalid file path'];
            }
            
            $fullPath = $this->uploadDir . $filePath;
            
            if (!file_exists($fullPath)) {
                return ['success' => false, 'message' => 'File not found'];
            }
            
            if (unlink($fullPath)) {
                return ['success' => true, 'message' => 'File deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete file'];
            }
            
        } catch (Exception $e) {
            error_log("Delete File Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete file. Please try again.'];
        }
    }
    
    /**
     * Validate uploaded file
     * @param string $fileName File name
     * @param string $fileType File MIME type
     * @param int $fileSize File size in bytes
     * @param int $fileError Upload error code
     * @param string $fileTmpName Temporary file path
     * @return array Validation result
     */
    private function validateFile($fileName, $fileType, $fileSize, $fileError, $fileTmpName) {
        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $message = $errorMessages[$fileError] ?? 'Unknown upload error';
            return ['valid' => false, 'message' => $message];
        }
        
        // Check file size
        if ($fileSize > $this->maxFileSize) {
            $maxSizeMB = $this->maxFileSize / (1024 * 1024);
            return ['valid' => false, 'message' => "File size exceeds {$maxSizeMB}MB limit"];
        }
        
        // Check file type
        if (!in_array($fileType, $this->allowedImageTypes)) {
            return ['valid' => false, 'message' => 'Invalid file type. Only images are allowed'];
        }
        
        // Check file extension
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file extension. Only JPG, PNG, GIF, and WebP are allowed'];
        }
        
        // Additional security check: verify file is actually an image
        if (!getimagesize($fileTmpName)) {
            return ['valid' => false, 'message' => 'File is not a valid image'];
        }
        
        return ['valid' => true, 'message' => 'File is valid'];
    }
    
    /**
     * Generate unique filename
     * @param string $extension File extension
     * @return string Unique filename
     */
    private function generateUniqueFileName($extension) {
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        return "file_{$timestamp}_{$randomString}.{$extension}";
    }
    
    /**
     * Resize image to specified dimensions
     * @param string $sourcePath Source image path
     * @param string $destinationPath Destination image path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param int $quality JPEG quality (1-100)
     * @return bool True on success, false on failure
     */
    public function resizeImage($sourcePath, $destinationPath, $maxWidth = 800, $maxHeight = 600, $quality = 85) {
        try {
            // Get image information
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $imageType = $imageInfo[2];
            
            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = round($originalWidth * $ratio);
            $newHeight = round($originalHeight * $ratio);
            
            // Create image resource
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize image
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save resized image
            $success = false;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $success = imagejpeg($newImage, $destinationPath, $quality);
                    break;
                case IMAGETYPE_PNG:
                    $success = imagepng($newImage, $destinationPath, 9);
                    break;
                case IMAGETYPE_GIF:
                    $success = imagegif($newImage, $destinationPath);
                    break;
                case IMAGETYPE_WEBP:
                    $success = imagewebp($newImage, $destinationPath, $quality);
                    break;
            }
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Resize Image Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse size string (e.g., "2M", "8M") to bytes
     * @param string $size Size string
     * @return int Size in bytes
     */
    private function parseSize($size) {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
    
    /**
     * Create thumbnail from image
     * @param string $sourcePath Source image path
     * @param string $destinationPath Destination thumbnail path
     * @param int $thumbSize Thumbnail size (square)
     * @param int $quality JPEG quality (1-100)
     * @return bool True on success, false on failure
     */
    public function createThumbnail($sourcePath, $destinationPath, $thumbSize = 150, $quality = 85) {
        return $this->resizeImage($sourcePath, $destinationPath, $thumbSize, $thumbSize, $quality);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Disable error display (only log to file)
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Log the incoming request
    error_log("File Upload Request: " . json_encode($_POST));
    error_log("Files: " . json_encode($_FILES));
    error_log("Request headers: " . json_encode(getallheaders()));
    error_log("Cookie data: " . json_encode($_COOKIE));
    
    try {
        // Debug logging
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("Session ID: " . session_id());
        error_log("Session name: " . session_name());
        
        $uploadManager = new FileUploadManager();
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload_book_images':
                error_log("Checking if user is logged in...");
                error_log("Session status: " . session_status());
                error_log("Session data: " . print_r($_SESSION, true));
                
                if (!isLoggedIn()) {
                    error_log("User not logged in for file upload");
                    sendErrorResponse('User not logged in', 401);
                }
                
                error_log("User is logged in, proceeding with upload...");
                
                if (!isset($_FILES['images'])) {
                    sendErrorResponse('No files uploaded');
                }
                
                $uploadType = $_POST['upload_type'] ?? 'cover';
                error_log("Files array structure: " . print_r($_FILES['images'], true));
                error_log("Upload type: " . $uploadType);
                $result = $uploadManager->uploadBookImages($_FILES['images'], $uploadType);
                error_log("Upload result: " . print_r($result, true));
                sendJSONResponse($result);
                break;
                
            case 'upload_profile_picture':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            
            if (!isset($_FILES['profile_picture'])) {
                sendErrorResponse('No file uploaded');
            }
            
            $result = $uploadManager->uploadProfilePicture($_FILES['profile_picture']);
            sendJSONResponse($result);
            break;
            
        case 'delete_file':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            
            $filePath = $_POST['file_path'] ?? '';
            if (empty($filePath)) {
                sendErrorResponse('File path is required');
            }
            
            $result = $uploadManager->deleteFile($filePath);
            sendJSONResponse($result);
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
        }
    } catch (Exception $e) {
        error_log("General error in file upload: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendErrorResponse('Server error: ' . $e->getMessage(), 500);
    }
}
?>
