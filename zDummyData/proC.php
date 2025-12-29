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
     * Upload profile picture
     */
    public function actionUpload()
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_FILES['profile']) || $_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
                Yii::app()->end();
            }

            $file = $_FILES['profile'];

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

            // Allowed types: JPG, PNG
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WEBP files allowed']);
                Yii::app()->end();
            }

            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');

            // Unique filename
            $originalName = basename($file['name']);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $fileName = 'profile/' . time() . '-' . $safeName;

            $fileContent = file_get_contents($file['tmp_name']);

            $s3->putObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
                'Body' => $fileContent,
                'ContentType' => $mimeType,
                'ACL' => 'private',
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'filePath' => $fileName,
                'fileName' => $originalName,
                'fileSize' => $file['size'],
                'fileType' => $mimeType,
                'uploadedAt' => date('Y-m-d H:i:s')
            ]);
            Yii::app()->end();

        } catch (AwsException $e) {
            echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
            Yii::app()->end();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
            Yii::app()->end();
        }
    }

    /**
     * List all profile pictures
     */
    public function actionIndex()
    {
        try {
            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');

            $result = $s3->listObjectsV2(['Bucket' => $bucket, 'Prefix' => 'profile/']);

            $files = [];
            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $obj) {
                    if (substr($obj['Key'], -1) === '/') continue;

                    $cmd = $s3->getCommand('GetObject', ['Bucket' => $bucket, 'Key' => $obj['Key']]);
                    $presignedUrl = (string)$s3->createPresignedRequest($cmd, '+20 minutes')->getUri();

                    $files[] = [
                        'key' => $obj['Key'],
                        'size' => $obj['Size'],
                        'lastModified' => $obj['LastModified']->format('Y-m-d H:i:s'),
                        'url' => $presignedUrl,
                        'fileName' => basename($obj['Key']),
                    ];
                }
            }

            $this->render('index', ['profiles' => $files]);
        } catch (AwsException $e) {
            Yii::app()->user->setFlash('error', 'Error fetching profile pictures: ' . $e->getMessage());
            $this->render('index', ['profiles' => []]);
        }
    }

    /**
     * Delete a profile picture
     */
    public function actionDelete($key)
    {
        header('Content-Type: application/json');
        try {
            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');
            $key = urldecode($key);

            $s3->deleteObject(['Bucket' => $bucket, 'Key' => $key]);

            echo json_encode(['success' => true, 'message' => 'Profile picture deleted successfully']);
            Yii::app()->end();
        } catch (AwsException $e) {
            echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
            Yii::app()->end();
        }
    }

    /**
     * Download a profile picture (presigned URL)
     */
    public function actionDownload($key)
    {
        try {
            $s3 = $this->getS3Client();
            $bucket = getenv('R2_BUCKET');
            $key = urldecode($key);

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key,
                'ResponseContentDisposition' => 'attachment; filename="' . basename($key) . '"'
            ]);

            $presignedUrl = (string)$s3->createPresignedRequest($cmd, '+5 minutes')->getUri();

            $this->redirect($presignedUrl);
        } catch (AwsException $e) {
            Yii::app()->user->setFlash('error', 'Error generating download link: ' . $e->getMessage());
            $this->redirect(['index']);
        }
    }
}
