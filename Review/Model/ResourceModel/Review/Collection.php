<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Collection.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Model\ResourceModel\Review;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['reviewDetail' => $this->getReviewDetailTable()],
            'main_table.review_id = reviewDetail.review_id',
            ['video']
        );
        return $this;
    }
}
