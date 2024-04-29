<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Video.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Block\Adminhtml\Field;

class Video extends \Magento\Backend\Block\Template
{
    const FIELD_ID = 'review_video';

    /**
     * Field Video template name
     *
     * @var string
     */
    protected $_template = 'BronzeByte_Review::field/video.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize review video field data
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->_coreRegistry->registry('review_data')) {
            $review = $this->_coreRegistry->registry('review_data');
            $this->setReviewId($review->getReviewId());
            $this->setVideoPath($review->getVideo());
        }

        $this->setFieldId(self::FIELD_ID);
    }
}
