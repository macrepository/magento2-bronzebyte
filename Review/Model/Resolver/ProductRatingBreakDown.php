<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: ProductRatingBreakDown.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReviewGraphQl\Model\DataProvider\ReviewRatingsDataProvider;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;

class ProductRatingBreakDown implements ResolverInterface
{
    /**
     * @var ReviewRatingsDataProvider
     */
    protected $reviewRatingsDataProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ReviewRatingsDataProvider $reviewRatingsDataProvider
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ReviewRatingsDataProvider $reviewRatingsDataProvider,
        CollectionFactory $collectionFactory
    ) {
        $this->reviewRatingsDataProvider = $reviewRatingsDataProvider;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     *
     * Format product's overall ratings
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];

        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();

        /** @var Collection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create()
            ->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter(Review::ENTITY_PRODUCT_CODE, (int) $product->getId())
            ->addStoreFilter($storeId)
            ->setDateOrder();

        $ratingBreakDown = [];
        foreach ($reviewsCollection as $review) {
            $reviewId = (int) $review->getId();
            $ratings = $this->reviewRatingsDataProvider->getData($reviewId);

            foreach ($ratings as $rating) {
                if (isset($ratingBreakDown[$rating['name']])) {
                    $ratingBreakDown[$rating['name']]['sum'] += $rating['value'];
                    $ratingBreakDown[$rating['name']]['count'] += 1;
                } else {
                    $ratingBreakDown[$rating['name']]['sum'] = $rating['value'];
                    $ratingBreakDown[$rating['name']]['count'] = 1;
                }
            }
        }

        $items = [];
        foreach ($ratingBreakDown as $name => $rating) {
            $items[] = [
                'name'          => $name,
                'value'         => number_format((float) ($rating['sum'] / $rating['count']), 2),
                'rating_count'  => (int) $rating['count']
            ];
        }

        return ['items' => $items];
    }
}
