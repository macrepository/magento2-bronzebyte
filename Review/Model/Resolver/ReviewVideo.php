<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: ReviewVideo.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\Review;
use BronzeByte\Review\Helper\VideoUploader;

class ReviewVideo implements ResolverInterface
{
    protected $videoUploader;

    public function __construct(
        VideoUploader $videoUploader
    ) {
        $this->videoUploader = $videoUploader;
    }

    /**
     * Return review video URL
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string|null
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $videoUrl = null;

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var Review $review */
        $review = $value['model'];

        $videoPath = $review->getVideo() ?? null;

        if ($videoPath) {
            $videoUrl = $this->videoUploader->getVideoUrl($videoPath);
        }

        return $videoUrl;
    }
}
