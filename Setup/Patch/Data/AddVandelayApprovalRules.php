<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use MagentoEse\B2bOrderApprovalSampleData\Model\AddApprovalRules;

class AddVandelayApprovalRules implements DataPatchInterface
{

    /** @var AddApprovalRules  */
    private $addRules;


    public function __construct(AddApprovalRules $addRules)
    {
        $this->addRules = $addRules;
    }

    public function apply()
    {
        $this->addRules->install(['MagentoEse_B2bOrderApprovalSampleData::fixtures/vandelay_rules.csv']);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [EnableOrderApprovalVandelay::class,AddVandelayRoles::class];
    }

    public function getAliases()
    {
        return [];
    }
}