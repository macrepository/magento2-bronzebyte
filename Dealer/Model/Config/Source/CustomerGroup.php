<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: CustomerGroup.php
 * @description: Customer Dealer
 */

namespace BronzeByte\Dealer\Model\Config\Source;

class CustomerGroup extends \Magento\Customer\Model\Customer\Source\Group
{
    /**
     * Return array of customer groups
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = parent::toOptionArray();

        if ($customerGroups[0]['label'] == __('ALL GROUPS')) {
            unset($customerGroups[0]);
        }

        return $customerGroups;
    }
}
