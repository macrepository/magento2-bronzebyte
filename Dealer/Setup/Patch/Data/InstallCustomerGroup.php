<?php

/**
 * Author: Shem L. Macapobres
 *
 * @file: InstallCustomerGroup.php
 * @description: Customer Dealer
 */

namespace BronzeByte\Dealer\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InstallCustomerGroup implements DataPatchInterface, PatchVersionInterface
{
    const CUSTOMER_GROUP_DELEAR         = 'Dealer';
    const CUSTOMER_GROUP_CONFIG_PATH    = 'defaultgroup/customergroup/selectedgroup';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupFactory $groupFactory
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupFactory $groupFactory,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupFactory = $groupFactory;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        try {
            $group = $this->groupFactory->create();
            $group
                ->setCode(self::CUSTOMER_GROUP_DELEAR)
                ->setTaxClassId(3)
                ->save();

            $customerGroupId = $group->getId();

            $this->setDefaultGroupConfig($customerGroupId);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Set System Config Default Value
     *
     * @param int $customerGroupId
     * @return void
     */
    private function setDefaultGroupConfig($customerGroupId)
    {
        $this->configWriter->save(
            self::CUSTOMER_GROUP_CONFIG_PATH,
            $customerGroupId,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
