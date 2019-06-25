<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Model;


use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\RoleInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\App\ResourceConnection;
use Magento\Company\Api\RoleRepositoryInterface;
use Exception;

class AddApprovalRules
{

    /** @var CompanyRepositoryInterface  */
    private $companyRepository;


    /** @var SearchCriteriaBuilder  */
    private $searchCriteriaBuilder;

    /** @var \Magento\Framework\Setup\SampleData\FixtureManager  */
    private $fixtureManager;

    /**@var \Magento\Framework\File\Csv  */
    private $csvReader;

    /** @var ResourceConnection  */
    private $resourceConnection;

    public function __construct(CompanyRepositoryInterface $companyRepository, SearchCriteriaBuilder $searchCriteriaBuilder,
                                SampleDataContext $sampleDataContext, ResourceConnection $resourceConnection, RoleRepositoryInterface $roleRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder =  $searchCriteriaBuilder;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->resourceConnection = $resourceConnection;
        $this->roleRepository = $roleRepository;
    }


    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {

                    if($header[$key]=='company_name'){
                        $data['company_id'] = $this->getCompanyId($value);
                    }elseif($header[$key]=='approval_to'){
                        $data['approval_to'] = $this->getRoleId($value,$data['company_id']);
                    }else{
                        $data[$header[$key]] = $value;
                    }

                }
                $row = $data;
                //try {
                    $connection = $this->resourceConnection->getConnection();
                    $connection->beginTransaction();
                    $connection->insertMultiple('purchase_order_rule', $row);
                    $connection->commit();
                ///} catch(Exception $e) {
                //    $connection->rollBack();
                //}
            }
        }
    }



    /**
     * @param $companyName
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyId($companyName)
    {
        $search = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::NAME, $companyName, 'eq')
            ->setPageSize(1)->setCurrentPage(1)->create();
        $companyList = $this->companyRepository->getList($search)->getItems();
        foreach ($companyList as $company) {
            return $company->getId();
        }
    }

    /**
     * @param $roleName
     * @param $companyId
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRoleId($roleName,$companyId){
        $search = $this->searchCriteriaBuilder
            ->addFilter(RoleInterface::ROLE_NAME,$roleName,'eq')
            ->addFilter(RoleInterface::COMPANY_ID,$companyId,'eq')
            ->setPageSize(1)->setCurrentPage(1)->create();
        $roleList = $this->roleRepository->getList($search)->getItems();
        foreach($roleList as $role){
            return $role->getId();
        }
    }

}