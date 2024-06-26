<?php

namespace BronzeByte\Review\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Helper\Data as ReviewHelper;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Mapper\ReviewDataMapper;
use Magento\ReviewGraphQl\Model\Review\AddReviewToProduct;
use Magento\Store\Api\Data\StoreInterface;
use BronzeByte\Review\Helper\VideoUploader;

/**
 * Create product review resolver
 */
class CreateProductReview implements ResolverInterface
{
    /**
     * @var ReviewHelper
     */
    private $reviewHelper;

    /**
     * @var AddReviewToProduct
     */
    private $addReviewToProduct;

    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    protected $videoUploader;

    /**
     * @param AddReviewToProduct $addReviewToProduct
     * @param ReviewDataMapper $reviewDataMapper
     * @param ReviewHelper $reviewHelper
     * @param ReviewsConfig $reviewsConfig
     * @param VideoUploader $videoUploader
     */
    public function __construct(
        AddReviewToProduct $addReviewToProduct,
        ReviewDataMapper $reviewDataMapper,
        ReviewHelper $reviewHelper,
        ReviewsConfig $reviewsConfig,
        VideoUploader $videoUploader
    ) {

        $this->addReviewToProduct = $addReviewToProduct;
        $this->reviewDataMapper = $reviewDataMapper;
        $this->reviewHelper = $reviewHelper;
        $this->reviewsConfig = $reviewsConfig;
        $this->videoUploader = $videoUploader;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array[]|Value|mixed
     *
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $this->reviewsConfig->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Creating product reviews are not currently available.'));
        }

        $input = $args['input'];
        $customerId = null;

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int) $context->getUserId();
        }

        if (!$customerId && !$this->reviewHelper->getIsGuestAllowToWrite()) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to add product reviews.'));
        }

        $sku = $input['sku'];
        $ratings = $input['ratings'];
        $data = [
            'nickname' => $input['nickname'],
            'title' => $input['summary'],
            'detail' => $input['text'],
        ];

        $fileName = '';
        
        try {
            $fileName = $this->videoUploader->uploadBase64Video($input['video']);
        } catch (\Exception $e) {
            throw new GraphQlAuthorizationException(__($e->getMessage()));
        }

        $data['video'] = $fileName;

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $review = $this->addReviewToProduct->execute($data, $ratings, $sku, $customerId, (int) $store->getId());

        return ['review' => $this->reviewDataMapper->map($review)];
    }
}
