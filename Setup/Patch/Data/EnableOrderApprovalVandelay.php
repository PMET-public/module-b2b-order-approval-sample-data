<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use MagentoEse\B2bOrderApprovalSampleData\Model\EnableOrderApproval;

class EnableOrderApprovalVandelay implements DataPatchInterface
{
    /** @var EnableOrderApproval  */
    private $enableOrderApproval;

    public function __construct(EnableOrderApproval $enableOrderApproval)
    {
        $this->enableOrderApproval = $enableOrderApproval;
    }

    public function apply()
    {
        $this->enableOrderApproval->enable('Vandelay Industries');
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