<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use MagentoEse\B2bOrderApprovalSampleData\Model\AddRoles;

class AddVandelayRoles implements DataPatchInterface
{

    /** @var AddRoles  */
    private $addRoles;


    public function __construct(AddRoles $addRoles)
    {
        $this->addRoles = $addRoles;
    }

    public function apply()
    {
        $this->addRoles->install(['MagentoEse_B2bOrderApprovalSampleData::fixtures/vandelay_roles.csv']);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}