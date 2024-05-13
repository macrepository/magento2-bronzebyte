<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: SetCustomerGroup.php
 * @description: Customer Dealer
 */

namespace BronzeByte\Dealer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use BronzeByte\Dealer\Setup\Patch\Data\InstallCustomerGroup;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SetCustomerGroup implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param ScopeConfigInterface $scopeInterface
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeInterface,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_scopeConfig = $scopeInterface;
        $this->customerRepository = $customerRepository;
    }

    /**
     * customer save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer) {
            return;
        }

        if ($customer->getIsDealer()) {
            $configCustomerGroupId = $this->getCustomerGroupConfig();

            if ($configCustomerGroupId != null && $customer->getGroupId() != $configCustomerGroupId) {
                $customer->setGroupId($configCustomerGroupId);
                $customer->save();
            }
        }
    }

    /**
     * Get System Config Customer Group
     *
     * @return int
     */
    private function getCustomerGroupConfig()
    {
        return $this->_scopeConfig
            ->getValue(
                InstallCustomerGroup::CUSTOMER_GROUP_CONFIG_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
