<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ProfileController extends Controller
{
    public function accessRules()
    {
        return [
            ['allow', 'actions' => ['index', 'upload', 'delete', 'download'], 'users' => ['*']],
            ['deny', 'users' => ['*']],
        ];
    }

    private function getS3Client()
    {
        return new S3Client([
            'version' => 'latest',
            'region' => getenv('R2_REGION') ?: 'auto',
            'endpoint' => getenv('R2_ENDPOINT'),
            'credentials' => [
                'key' => getenv('R2_ACCESS_KEY'),
                'secret' => getenv('R2_SECRET_KEY'),
            ],
            'suppress_php_deprecation_warning' => true,
        ]);
    }

    /**
     * Upload profile picture - UPDATED VERSION
     */
    public function actionUpload()
    {
        header('Content-Type: application/json');
        
        // Enable Docker logging
        ini_set('log_errors', 1);
        ini_set('error_log', 'php://stderr');
        
        $log = function($msg) {
            error_log("[ProfileUpload] " . $msg);
        };

        $log("=== Profile Upload Started ===");
        
        try {
            // Log what we received
            $log("POST data: " . print_r($_POST, true));
            $log("FILES data: " . print_r($_FILES, true));
            
            // Get employeeId from POST (if provided via FormData)
            $employeeId = Yii::app()->request->getPost('employeeId');
        if (!$employeeId) {
            $log("employeeId missing", "ERROR");
            echo json_encode([
                'success' => false,
                'message' => 'employeeId is required'
            ]);
            Yii::app()->end();
        }
            $log("Employee ID: " . ($employeeId ?: 'NOT PROVIDED'));

            if (!isset($_FILES['profile']) || $_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
                $log("No file uploaded or upload error");
                echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
                Yii::app()->end();
            }

            $file = $_FILES['profile'];
            $log("File: {$file['name']}, Size: {$file['size']}, Type: {$file['type']}");

            if ($file['size'] == 0) {
                echo json_encode(['success' => false, 'message' => 'File is empty']);
                Yii::app()->end();
            }

            // Max 5MB for profile pic
            $maxFileSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxFileSize) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
                Yii::app()->end();
            }

            // Allowed types: JPG, PNG, WEBP
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $log("Detected MIME type: $mimeType");

            if (!in_array($mimeType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WEBP files allowed']);
                Yii::app()->end();
            }

            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');
            $log("Using bucket: $bucket");

            // Generate unique filename
            $originalName = basename($file['name']);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $fileName = 'profile/' . time() . '-' . uniqid() . '-' . $safeName;
            $log("Generated filename: $fileName");

            $fileContent = file_get_contents($file['tmp_name']);

            // Upload to S3/R2
            $log("Uploading to S3...");
            $s3->putObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
                'Body' => $fileContent,
                'ContentType' => $mimeType,
                'ACL' => 'private',
            ]);
            $log("S3 upload successful");

            // If employeeId provided, update the employee record
            $employeeUpdated = false;
            $profilePicUrl = null;
            
            if ($employeeId) {
                $log("Updating employee record for ID: $employeeId");
                $employee = Employee::model()->findByAttributes(['employeeId' => $employeeId]);
                
                if ($employee) {
                    $employee->profilePicture = $fileName;
                    if ($employee->save()) {
                        $employeeUpdated = true;
                        $log("Employee record updated successfully");
                        
                        // Generate presigned URL
                        $profilePicUrl = $this->generatePresignedUrl($fileName);
                        $log("Generated presigned URL");
                    } else {
                        $log("Failed to update employee: " . json_encode($employee->errors));
                    }
                } else {
                    $log("Employee not found with ID: $employeeId");
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'filePath' => $fileName,
                'fileName' => $originalName,
                'fileSize' => $file['size'],
                'fileType' => $mimeType,
                'uploadedAt' => date('Y-m-d H:i:s'),
                'employeeUpdated' => $employeeUpdated,
                'profilePicture' => $profilePicUrl, // Include presigned URL if employee was updated
                'fileUrl' => $profilePicUrl // Alias for compatibility
            ]);
            Yii::app()->end();

        } catch (AwsException $e) {
            $log("S3 Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
            Yii::app()->end();
        } catch (Exception $e) {
            $log("General Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
            Yii::app()->end();
        }
    }
    
    /**
     * Generate presigned URL for a file
     */
    private function generatePresignedUrl($filePath)
    {
        try {
            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => ltrim($filePath, '/')
            ]);

            return (string) $s3->createPresignedRequest($cmd, '+20 minutes')->getUri();
        } catch (AwsException $e) {
            error_log("S3 Presigned URL Error: " . $e->getMessage());
            return null;
        }
    }

    // ... rest of your existing methods (index, delete, download) ...
}