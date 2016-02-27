<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Scriptureforge\Typesetting\TypesettingAssetModel;
use Api\Model\Scriptureforge\TypesettingProjectModel;
use Api\Model\Shared\Command\ErrorResult;
use Api\Model\Shared\Command\UploadResponse;
use Palaso\Utilities\FileUtilities;

class TypesettingUploadCommands
{
    /**
     * Upload a file
     *
     * @param string $projectId
     * @param string $mediaType
     * @param string $tmpFilePath
     * @throws \Exception
     * @return UploadResponse
     */
    public static function uploadFile($projectId, $mediaType, $tmpFilePath)
    {
        if (! $tmpFilePath) {
            throw new \Exception("Upload controller did not move the uploaded file.");
        }
        $file = $_FILES['file'];
        $fileName = $file['name'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $tmpFilePath);
        finfo_close($finfo);

        $fileName = FileUtilities::replaceSpecialCharacters($fileName);

        $fileExt = (false === $pos = strrpos($fileName, '.')) ? '' : substr($fileName, $pos);

        $allowedTypes = array(
            "image/png"
        );
        $allowedExtensions = array(
            ".png"
        );

        $response = new UploadResponse();
        if (in_array(strtolower($fileType), $allowedTypes) && in_array(strtolower($fileExt), $allowedExtensions)) {

            // make the folders if they don't exist
            $project = new TypesettingProjectModel($projectId);
            $folderPath = $project->getAssetsFolderPath();
            FileUtilities::createAllFolders($folderPath);

            // move uploaded file from tmp location to assets
            $filePath = self::mediaFilePath($folderPath, '', $fileName);
            $moveOk = copy($tmpFilePath, $filePath);
            @unlink($tmpFilePath);

            // construct server response
            if ($moveOk && $tmpFilePath) {
                $data = new TypesettingMediaResult();
                $data->path = '/' . $project->getAssetsRelativePath();
                $data->fileName = $fileName;
                $response->result = true;
            } else {
                $data = new ErrorResult();
                $data->errorType = 'UserMessage';
                $data->errorMessage = "$fileName could not be saved to the right location. Contact your Site Administrator.";
                $response->result = false;
            }
        } else {
            $allowedExtensionsStr = implode(", ", $allowedExtensions);
            $data = new ErrorResult();
            $data->errorType = 'UserMessage';
            if (count($allowedExtensions) < 1) {
                $data->errorMessage = "$fileName is not an allowed $mediaType file. No $mediaType file formats are currently enabled, contact your Site Administrator.";
            } elseif (count($allowedExtensions) == 1) {
                $data->errorMessage = "$fileName is not an allowed $mediaType file. Ensure the file is a $allowedExtensionsStr.";
            } else {
                $data->errorMessage = "$fileName is not an allowed $mediaType file. Ensure the file is one of the following types: $allowedExtensionsStr.";
            }
            $response->result = false;
        }

        $response->data = $data;

        if ($response->result) {
            $project = new TypesettingProjectModel($projectId);
            $asset = new TypesettingAssetModel($project);
            $asset->name = $response->data->fileName;
            $asset->path = $response->data->path;
            $asset->type = $mediaType;
            $asset->uploaded = true;
            $assetId = $asset->write();
            $response->data->assetId = $assetId;
        }
        return $response;
    }

    /**
     *
     * @param string $folderPath
     * @param string $fileNamePrefix
     * @param string $originalFileName
     * @return string
     */
    public static function mediaFilePath($folderPath, $fileNamePrefix, $originalFileName)
    {
        if (! $fileNamePrefix) {
            return $folderPath . '/' . $originalFileName;
        }
        return $folderPath . '/' . $fileNamePrefix . '_' . $originalFileName;
    }

    /**
     * cleanup (remove) previous files of any allowed extension for files with the given filename prefix in the given folder path
     *
     * @param string $folderPath
     * @param string $fileNamePrefix
     * @param array $allowedExtensions
     */
    public static function cleanupFiles($folderPath, $fileNamePrefix, $allowedExtensions)
    {
        $cleanupFiles = glob($folderPath . '/' . $fileNamePrefix . '*[' . implode(', ', $allowedExtensions) . ']');
        foreach ($cleanupFiles as $cleanupFile) {
            @unlink($cleanupFile);
        }
    }

    /**
     * Import a project zip file
     *
     * @param string $projectId
     * @param string $mediaType
     * @param string $tmpFilePath
     * @throws \Exception
     * @return UploadResponse
     */
    public static function importProjectZip($projectId, $mediaType, $tmpFilePath)
    {
        if ($mediaType != 'usfm-zip') {
            throw new \Exception("Unsupported upload type.");
        }
        if (! $tmpFilePath) {
            throw new \Exception("Upload controller did not move the uploaded file.");
        }

        $file = $_FILES['file'];
        $fileName = $file['name'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $tmpFilePath);
        finfo_close($finfo);

        $fileName = FileUtilities::replaceSpecialCharacters($fileName);

        $fileExt = (false === $pos = strrpos($fileName, '.')) ? '' : substr($fileName, $pos);

        $allowedTypes = array(
            "application/zip",
            "application/octet-stream",
            "application/x-7z-compressed"
        );
        $allowedExtensions = array(
            ".zip",
            ".zipx",
            ".7z"
        );

        $response = new UploadResponse();
        if (in_array(strtolower($fileType), $allowedTypes) && in_array(strtolower($fileExt), $allowedExtensions)) {

            // make the folders if they don't exist
            $project = new TypesettingProjectModel($projectId);
            $folderPath = $project->getAssetsFolderPath();
            FileUtilities::createAllFolders($folderPath);

            // move uploaded file from tmp location to assets
            $filePath = $folderPath . '/' . $fileName;
            $moveOk = copy($tmpFilePath, $filePath);
            @unlink($tmpFilePath);

            // import zip
            if ($moveOk) {
                $extractedFilePaths = self::extractZip($filePath, $folderPath);

                $response->result = true;
                $data = new TypesettingImportResult();
                $data->path = '/' . $project->getAssetsRelativePath();
                $data->fileName = $fileName;
                foreach ($extractedFilePaths as $extractedFilePath) {
                    $extractedFilePath = substr($extractedFilePath, strlen(APPPATH) - 1);
                    $asset = new TypesettingAssetModel($project);
                    $asset->name = basename($extractedFilePath);
                    $asset->path = dirname($extractedFilePath);
                    $asset->type = 'usfm';
                    $asset->uploaded = true;
                    $assetId = $asset->write();
                    $data->extractedAssetIds[] = $assetId;
                }

                // TODO: import extracted USFM files into RaPuMa project

            } else {
                $data = new ErrorResult();
                $data->errorType = 'UserMessage';
                $data->errorMessage = "$fileName could not be saved to the right location. Contact your Site Administrator.";
                $response->result = false;
            }
        } else {
            $allowedExtensionsStr = implode(", ", $allowedExtensions);
            $data = new ErrorResult();
            $data->errorType = 'UserMessage';
            if (count($allowedExtensions) < 1) {
                $data->errorMessage = "$fileName is not an allowed compressed file. No compressed file formats are currently enabled, contact your Site Administrator.";
            } elseif (count($allowedExtensions) == 1) {
                $data->errorMessage = "$fileName is not an allowed compressed file. Ensure the file is a $allowedExtensionsStr.";
            } else {
                $data->errorMessage = "$fileName is not an allowed compressed file. Ensure the file is one of the following types: $allowedExtensionsStr.";
            }
            $response->result = false;
        }

        $response->data = $data;
        return $response;
    }

    /**
     * TODO: This is duplicated from Api\Model\languageforge\lexicon\LiftImport; move both into Palaso\Utilities\FileUtilities. IJH 2015-01
     *
     * @param string $zipFilePath
     * @param string $extractFolderPath
     * @throws \Exception
     * @return array<string> of extracted file paths
     */
    public static function extractZip($zipFilePath, $extractFolderPath)
    {
        // Use absolute path for archive file
        $realpathResult = realpath($zipFilePath);
        if ($realpathResult) {
            $zipFilePath = $realpathResult;
        } else {
            throw new \Exception("Error receiving uploaded file");
        }
        if (! file_exists($realpathResult)) {
            throw new \Exception("Error file '$zipFilePath' does not exist.");
        }

        $basename = basename($zipFilePath);
        $pathinfo = pathinfo($basename);
        $extension_1 = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'NOEXT';
        // Handle .tar.gz, .tar.bz2, etc. by checking if there's another extension "inside" the first one
        $basename_without_ext = $pathinfo['filename'];
        $pathinfo = pathinfo($basename_without_ext);
        $extension_2 = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'NOEXT';
        // $extension_2 will be 'tar' if the file was a .tar.gz, .tar.bz2, etc.
        if ($extension_2 == "tar") {
            // We don't handle tarball formats... yet.
            throw new \Exception("Sorry, the ." . $extension_2 . "." . $extension_1 . " format isn't supported");
        }
        switch ($extension_1) {
            case "zip":
                $command = 'unzip -o ' . escapeshellarg($zipFilePath) . " -d " . escapeshellarg($extractFolderPath);
                break;
            case "zipx":
            case "7z":
                $command = '7z x -y ' . escapeshellarg($zipFilePath) . " -o" . escapeshellarg($extractFolderPath);
                break;
            default:
                throw new \Exception("Sorry, the ." . $extension_1 . " format isn't allowed");
                break;
        }

        FileUtilities::createAllFolders($extractFolderPath);

        // ensure non-roman filesnames are returned
        $command = 'LANG="en_US.UTF-8" ' . $command;
        $output = array();
        $returnCode = 0;
        exec($command, $output, $returnCode);
        if ($returnCode) {
            if (($returnCode != 1) || ($returnCode == 1 && strstr(end($output), 'failed setting times/attribs') == false)) {
                throw new \Exception("Uncompressing archive file failed: " . print_r($output, true));
            }
        }

        $extractedFilePaths = array();
        foreach ($output as $outputLine) {
            switch ($extension_1) {
                case "zip":
                    if (substr($outputLine, 0, 13) === "  inflating: ") {
                        // remove comment above
                        $extractedFilePath = substr($outputLine, 13);
                        $extractedFilePaths[] = $extractedFilePath;
                    }
                    break;
                case "zipx":
                case "7z":
                    if (substr($outputLine, 0, 12) === "Extracting  ") {
                        // remove comment above; add the rest of the path
                        $extractedFilePath = $extractFolderPath . '/' . substr($outputLine, 12);
                        $extractedFilePaths[] = $extractedFilePath;
                    }
                    break;
                default:
                    break;
            }
        }
        return $extractedFilePaths;
    }

    public static function deleteFile($projectId, $fileName)
    {
        $response = new UploadResponse();
        $response->result = false;
        $project = new TypesettingProjectModel($projectId);
        $folderPath = $project->getAssetsFolderPath();
        $filePath = $folderPath . '/' . $fileName;
        if (file_exists($filePath) and ! is_dir($filePath)) {
            if (@unlink($filePath)) {
                $data = new TypesettingMediaResult();
                $data->path = '/' . $project->getAssetsRelativePath();
                $data->fileName = $fileName;
                $response->data = $data;
                $response->result = true;
            } else {
                $data = new ErrorResult();
                $data->errorType = 'UserMessage';
                $data->errorMessage = "$fileName could not be deleted. Contact your Site Administrator.";
            }
            $response->data = $data;
            return $response;
        }
        $data = new ErrorResult();
        $data->errorType = 'UserMessage';
        $data->errorMessage = "$fileName does not exist in this project. Contact your Site Administrator.";
        $response->data = $data;
        return $response;
    }
}
