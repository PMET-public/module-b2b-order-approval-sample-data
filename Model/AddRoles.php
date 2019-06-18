<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Model;


use Magento\Company\Api\Data\RoleInterfaceFactory as RoleInterface;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Company\Api\Data\PermissionInterfaceFactory as PermissionInterface;
use Accorin\OrderApprovals\Model\RoleLevelFactory as RoleLevel;
use Magento\Company\Model\UserRoleManagement;
use Accorin\OrderApprovals\Model\ApprovalRulesFactory;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class AddRoles
{


    /** @var RoleInterface  */
    private $roleInterface;

    /** @var RoleRepositoryInterface  */
    private $roleRepository;

    /** @var PermissionInterface  */
    private $permissionInterface;

    /** @var RoleLevel */
    private $roleLevel;

    /** @var UserRoleManagement  */
    private $roleManagement;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvReader;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /** @var CompanyRepositoryInterface  */
    private $companyRepository;

    /** @var SearchCriteriaBuilder  */
    private $searchCriteriaBuilder;

    /** @var CustomerRepositoryInterface  */
    private $customerRepository;


    public function __construct(RoleInterface $roleInterface, RoleRepositoryInterface $roleRepository,
                                PermissionInterface $permissionInterface,RoleLevel $roleLevel,
                                UserRoleManagement $roleManagement,SampleDataContext $sampleDataContext,
                                CompanyRepositoryInterface $companyRepository, SearchCriteriaBuilder $searchCriteriaBuilder,
                                CustomerRepositoryInterface $customerRepository)
    {
        $this->roleInterface = $roleInterface;
        $this->roleRepository = $roleRepository;
        $this->permissionInterface = $permissionInterface;
        $this->roleLevel = $roleLevel;
        $this->roleManagement = $roleManagement;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder =  $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
    }

    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                throw new Exception('File not found: '.$fileName);
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;

                $this->createRole($row['role_name'],$row['company_name'],
                    explode(',',$row['users']),$row['purchasing_level'],explode(',',$row['resource_ids']));
            }
        }

    }

    public function createRole($roleName,$companyName,array $users,$purchasingLevel,array $resourceIds)
    {



        //create role
        $role = $this->addRole($roleName, $this->getCompanyIdByName($companyName));

        $permission = array();
        foreach($resourceIds as $resourceId){
            $permission[] = $this->createPermission($resourceId);
        }
        $role->setPermissions($permission);
        $this->roleRepository->save($role);


        //set purchasing level
        $this->setPurchasingLevel($role, $purchasingLevel);

        //assign user to role
        foreach($users as $user){
            $this->assignUserToRole($this->customerRepository->get($user)->getId(), $role);
        }


    }

     /**
     * @param $rolename
     * @param $companyid
     * @return \Magento\Company\Api\Data\RoleInterface
     */
    public function addRole($rolename, $companyid)
    {
        $role = $this->roleInterface->create();
        $role->setRoleName($rolename);
        $role->setCompanyId($companyid);
        return $role;
    }

    /**
     * @param $resourceId
     * @return \Magento\Company\Api\Data\PermissionInterface
     */
    public function createPermission($resourceId)
    {
        $permission = $this->permissionInterface->create();
        $permission->setResourceId($resourceId);
        $permission->setPermission('allow');
        return $permission;
    }

    /**
     * @param \Magento\Company\Api\Data\RoleInterface $role
     * @param $purchasingLevel
     * @throws \Exception
     */
    public function setPurchasingLevel(\Magento\Company\Api\Data\RoleInterface $role, $purchasingLevel)
    {
        $roleLevel = $this->roleLevel->create();
        $roleLevel->setRoleId($role->getId());
        $roleLevel->setPurchasingLevel($purchasingLevel);
        $roleLevel->save();
    }

    /**
     * @param $userId
     * @param \Magento\Company\Api\Data\RoleInterface $role
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignUserToRole($userId, \Magento\Company\Api\Data\RoleInterface $role)
    {
        $this->roleManagement->assignRoles($userId, [$role]);
    }

    /**
     * @param $companyName
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyIdByName($companyName){
        $search = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::NAME,$companyName,'eq')
            ->setPageSize(1)->setCurrentPage(1)->create();
        $companyList = $this->companyRepository->getList($search)->getItems();
        foreach($companyList as $company){
            return $company->getId();
        }
    }


}