<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\B2bOrderApprovalSampleData\Model;


use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\Data\CompanyInterface;

class EnableOrderApproval
{

    /** @var CompanyRepositoryInterface  */
    private $companyRepository;

    /** @var SearchCriteriaBuilder  */
    private $searchCriteriaBuilder;


    public function __construct(CompanyRepositoryInterface $companyRepository, SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder =  $searchCriteriaBuilder;
    }


    public function enable($companyName)
    {
        $search = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::NAME,$companyName,'eq')
            ->setPageSize(1)->setCurrentPage(1)->create();
        $companyList = $this->companyRepository->getList($search)->getItems();
        foreach($companyList as $company){
            $this->enableOrderApproval($company->getId());
        }
    }

    /**
     * @param $companyId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function enableOrderApproval($companyId)
    {
        $company = $this->companyRepository->get($companyId);
        $company->setIsOrderapprovalsEnable(1);
        $this->companyRepository->save($company);
    }

}