<?php
/**
 * File Upload System
 * Handles file uploads for book images and profile pictures
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once '../config/session.php';

/**
 * File Upload Manager Class
 * Manages all file upload operations
 */
class FileUploadManager {
    private $uploadDir;
    private $maxFileSize;
    private $allowedImageTypes;
    
    public function __construct() {
        // Set upload directory (relative to this file)
        $this->uploadDir = dirname(__DIR__) . '/uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Create subdirectories
        $subdirs = ['books', 'profiles', 'temp'];
        foreach ($subdirs as $subdir) {
            $path = $this->uploadDir . $subdir . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        // Set upload limits
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
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
                $validation = $this->validateFile($fileName, $fileType, $fileSize, $fileError);
                if (!$validation['valid']) {
                    $errors[] = $fileName . ': ' . $validation['message'];
                    continue;
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = $this->generateUniqueFileName($fileExtension);
                
                // Determine upload path
                $uploadPath = $this->uploadDir . 'books/' . $uniqueFileName;
                
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
                return ['success' => false, 'message' => 'No files were uploaded successfully'];
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
                $file['error']
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
     * @return array Validation result
     */
    private function validateFile($fileName, $fileType, $fileSize, $fileError) {
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
        if (!getimagesize($file['tmp_name'])) {
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
    $uploadManager = new FileUploadManager();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'upload_book_images':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            
            if (!isset($_FILES['images'])) {
                sendErrorResponse('No files uploaded');
            }
            
            $uploadType = $_POST['upload_type'] ?? 'cover';
            $result = $uploadManager->uploadBookImages($_FILES['images'], $uploadType);
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
}
?>
