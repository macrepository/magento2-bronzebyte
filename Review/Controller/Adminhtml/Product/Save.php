<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Save.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use BronzeByte\Review\Helper\VideoUploader;
use Magento\Review\Model\Review;
use BronzeByte\Review\Block\Adminhtml\Field\Video;

class Save extends \Magento\Review\Controller\Adminhtml\Product\Save
{
    /**
     * Video Uploader Helper
     *
     * @var VideoUploader
     */
    protected $videoUploader;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param VideoUploader $videoUploader
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        VideoUploader $videoUploader
    ) {
        $this->videoUploader = $videoUploader;
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
    }

    public function execute()
    {
        try {
            $files = $this->getRequest()->getFiles();

            if (!empty($files[Video::FIELD_ID]['name'])) {
                // Get original video path, Delete if edit was successful
                $oldVideoPath = $this->getModel()->getVideo();

                // Upload the review video
                $fileName = $this->videoUploader->uploadVideo(Video::FIELD_ID, $files[Video::FIELD_ID]['name']);

                if (!$fileName) {
                    $this->messageManager->addErrorMessage(__('An error occured. Review video is not saved. Please ensure to upload a valid video type'));
                    return parent::execute();
                }

                // Bind the video file path to the post request to include saving to the DB
                $this->getRequest()->setPostValue('video', $fileName);

                // Process parent codes
                $redirect = parent::execute();

                // Get latest video path
                $newVideoPath = $this->getModel()->getVideo();

                // Process deletion on old video
                if (($fileName == $newVideoPath) && $newVideoPath != $oldVideoPath) {
                    $oldVideoFullPath = $this->videoUploader->getVideoPath($oldVideoPath);

                    if ($this->videoUploader->isVideoPathExist($oldVideoFullPath)) {
                        $this->videoUploader->removeVideo($oldVideoFullPath);
                    }
                }

                return $redirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage() . " Review video is not saved. Please ensure to upload a valid video type");
        }

        return parent::execute();
    }

    /**
     * Returns requested model.
     *
     * @return Review
     */
    private function getModel(): Review
    {
        return $this->reviewFactory->create()
            ->load($this->getRequest()->getParam('id', false));
    }
}
