<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: ProductCustomAttribute.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class ProductCustomAttribute implements ResolverInterface
{
    protected $attributeSetFactory;
    protected $attributeCollectionFactory;

    public function __construct(
        AttributeSetFactory $attributeSetFactory,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @inheritdoc
     *
     * Format product's custom attribute to GraphQL schema
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

        $attributeSetId = $product->getAttributeSetId();
        $attributes = $this->attributeCollectionFactory->create()
            ->setAttributeSetFilter($attributeSetId)
            ->addVisibleFilter();

        $customAttributes = [];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeValue = $product->getData($attributeCode);
            $attributeLabel = $attribute->getStoreLabel();

            if (!$attributeCode || is_array($attributeValue) || !$attributeLabel) {
                continue;
            }

            $customAttributes[] = [
                'code' => $attributeCode,
                'label' => $attributeLabel,
                'value' => $attributeValue
            ];
        }

        return $customAttributes;
    }
}