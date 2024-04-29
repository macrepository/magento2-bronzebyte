<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Collection.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\ResourceModel\Review\Product;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Product\Collection
{
    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        parent::_joinFields();
        $reviewDetailTable = $this->_resource->getTableName('review_detail');

        $this->getSelect()->join(
            ['rd' => $reviewDetailTable],
            'rd.review_id = rt.review_id',
            ['video' => 'rd.video']
        );

        return $this;
    }
}
