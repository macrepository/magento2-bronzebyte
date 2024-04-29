<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Post.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use BronzeByte\Review\Helper\VideoUploader;
use BronzeByte\Review\Block\Adminhtml\Field\Video;

class Post extends \Magento\Review\Controller\Adminhtml\Product\Post
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
        $files = $this->getRequest()->getFiles();

        if (!empty($files[Video::FIELD_ID]['name'])) {
            // Upload the review video
            $fileName = $this->videoUploader->uploadVideo(Video::FIELD_ID, $files[Video::FIELD_ID]['name']);

            if (!$fileName) {
                $this->messageManager->addErrorMessage(__('An error occured. Review video is not saved. Please ensure to upload a valid video type'));
                return parent::execute();
            }

            // Bind the video file path to the post request to include saving to the DB
            $this->getRequest()->setPostValue('video', $fileName);
        }

        return parent::execute();
    }
}
