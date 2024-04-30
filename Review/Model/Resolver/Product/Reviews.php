<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: Reviews.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Model\DataProvider\AggregatedReviewsDataProvider;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;

class Reviews implements ResolverInterface
{
    /**
     * @var AggregatedReviewsDataProvider
     */
    private $aggregatedReviewsDataProvider;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
     * @param ReviewsConfig $reviewsConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        AggregatedReviewsDataProvider $aggregatedReviewsDataProvider,
        ReviewsConfig $reviewsConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->aggregatedReviewsDataProvider = $aggregatedReviewsDataProvider;
        $this->reviewsConfig = $reviewsConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Resolves the product reviews
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     *
     * @throws GraphQlInputException
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
            return ['items' => []];
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $reviewsCollection = $this->getData(
            (int) $product->getId(),
            $args['currentPage'],
            $args['pageSize'],
            (int) $context->getExtensionAttributes()->getStore()->getId()
        );

        return $this->aggregatedReviewsDataProvider->getData($reviewsCollection);
    }

    /**
     * Get product reviews
     *
     * @param int $productId
     * @param int $currentPage
     * @param int $pageSize
     * @param int $storeId
     * @return Collection
     */
    public function getData(int $productId, int $currentPage, int $pageSize, int $storeId): Collection
    {
        /** @var Collection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create()
            ->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $productId)
            ->setPageSize($pageSize)
            ->setCurPage($currentPage)
            ->addStoreFilter($storeId)
            ->setDateOrder();

        // Join review_detail table, in order to filter
        $reviewsCollection->getSelect()->join(
            ['rd' => $reviewsCollection->getTable('review_detail')],
            'main_table.review_id = rd.review_id',
            ['video']
        );

        // Add filter to video to select only column video that have data
        $reviewsCollection->addFieldToFilter('rd.video', ['neq' => 'null']);

        $reviewsCollection->getSelect()->join(
            ['cpe' => $reviewsCollection->getTable('catalog_product_entity')],
            'cpe.entity_id = main_table.entity_pk_value',
            ['sku']
        );
        $reviewsCollection->addRateVotes();

        return $reviewsCollection;
    }
}
