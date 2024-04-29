<?php

namespace BronzeByte\Review\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class VideoUploader extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REVIEW_VIDEO_DIR = 'catalog/product/review/';

    /**
     * Filesystem facade
     *
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * Store Manager Interface
     *
     * @var StoreManagerInterface
     */

    protected $_storeManager;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Upload Video
     *
     * @param string $fileId
     * @param string $orignalFileName
     * @return string
     */
    public function uploadVideo($fileId, $orignalFileName)
    {
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
        } catch (\Exception $e) {
            return '';
        }

        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::REVIEW_VIDEO_DIR
        );
        $uploader->save($path, uniqid() . '_' . basename($orignalFileName));

        return $uploader->getUploadedFileName();
    }

    /**
     * Upload video via base64 content
     *
     * @param string $base64Content
     * @return string
     */
    public function uploadBase64Video($base64Content)
    {
        // Decode the base64 content
        $videoData = base64_decode(preg_replace('#^data:video/\w+;base64,#i', '', $base64Content));
        if (!$videoData) {
            throw new \Exception(__('Video content data is corrupted'));
        }

        $fileExt = $this->getFileExtensionFromBase64($base64Content);

        if (!$fileExt) {
            throw new \Exception(__('Video content data is invalid. Please include the Data URI of the base64 string, and ensure the video file is in one of the following formats: mp4, avi, mov, wmv, flv, mkv, or webm.'));
        }

        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::REVIEW_VIDEO_DIR
        );

        // Create a file name
        $filename = uniqid() . '_' . basename('review_video') . ".{$fileExt}";
        $videoFilePath = $path . $filename;

        // save the decoded to a file
        $mediaDir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDir->writeFile($videoFilePath, $videoData);

        // Check if file was created successfully
        if (!$this->isVideoPathExist($videoFilePath)) {
            throw new \Exception(__('An error occured. Video content data is not uploaded'));
        }

        return $filename;
    }

    /**
     * Get File Extension From Base64 string
     *
     * @param string $base64String
     * @return string|null
     */
    public function getFileExtensionFromBase64($base64String)
    {
        $pattern = "/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+);base64,/";
        if (preg_match($pattern, $base64String, $matches)) {
            // Extract MIME type
            $mimeType = $matches[1];

            // Convert MIME type to file extension
            return $this->mimeToExtension($mimeType);
        }

        return null;
    }

    /**
     * Dtermine File Extension
     *
     * @param string $mimeType
     * @return string|null
     */
    public function mimeToExtension($mimeType)
    {
        $mimeMap = [
            'video/mp4'         => 'mp4',
            'video/avi'         => 'avi',
            'video/quicktime'   => 'mov',
            'video/x-ms-wmv'    => 'wmv',
            'video/x-flv'       => 'flv',
            'video/x-matroska'  => 'mkv',
            'video/webm'        => 'webm'
        ];

        return $mimeMap[$mimeType] ?? null;
    }

    /**
     * Get Video Full Path
     *
     * @param string $filePath
     * @return string
     */
    public function getVideoPath($filePath)
    {
        return $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::REVIEW_VIDEO_DIR . $filePath
        );
    }

    /**
     * Get Video Full Path URL
     *
     * @param string $filePath
     * @return string|null
     */
    public function getVideoUrl($filePath)
    {
        $videoUrl = null;
        $videoFullPath = $this->getVideoPath($filePath);

        if ($this->isVideoPathExist($videoFullPath)) {
            $store = $this->_storeManager->getStore();
            $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $videoUrl = $mediaUrl . str_replace('//', '/', self::REVIEW_VIDEO_DIR . $filePath);
        }

        return $videoUrl;
    }

    /**
     * Check video path existence
     *
     * @param string $videoPath
     * @return boolean
     */
    public function isVideoPathExist($videoPath)
    {
        $mediaDirectory = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        );

        return $mediaDirectory->isFile($videoPath);
    }

    /**
     * Remove video from media storage
     *
     * @param strin $videoPath
     * @return boolean
     */
    public function removeVideo($videoPath)
    {
        $isDeleted = false;

        try {
            $writer = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $result = $writer->delete($videoPath);

            if ($result) {
                $isDeleted = true;
            }
        } catch (\Exception $e) {
            // Add error handler
        }

        return $isDeleted;
    }
}
