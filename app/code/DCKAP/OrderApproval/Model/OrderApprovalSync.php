<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Model;

use DCKAP\OrderApproval\Api\OrderApprovalInterface;

/**
 * Class OrderApprovalSync
 * @package DCKAP\OrderApproval\Model
 */
class OrderApprovalSync implements OrderApprovalInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var OrderApprovalFactory
     */
    protected $orderApprovalFactory;

    /**
     * @var \DCKAP\OrderApproval\Helper\Data
     */
    protected $orderApprovalHelper;

    /**
     * OrderApprovalSync constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param OrderApprovalFactory $orderApprovalFactory
     * @param \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        OrderApprovalFactory $orderApprovalFactory,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->orderApprovalFactory = $orderApprovalFactory;
        $this->orderApprovalHelper = $orderApprovalHelper;
    }

    /**
     * @param array $addressList
     * @param bool $email
     * @param bool $accountNumber
     * @param bool $websiteId
     * @param bool $userId
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateShipToApproval($addressList = [], $email = false, $accountNumber = false, $websiteId = false, $userId = false) {
//        $websiteId = $this->orderApprovalHelper->getCurrentWebsiteId();
        $customerRepository = $this->customerRepository->get($email, $websiteId);
        $response = [];
        try {
            /**
             * Delete existing items based on email, websiteId and account number
             */
            $collections = $this->orderApprovalFactory->create()->getCollection()
                ->addFieldToFilter('customer_email', ['eq' => $email])
                ->addFieldToFilter('erp_account_number', ['eq' => $accountNumber])
                ->addFieldToFilter('website_id', ['eq' => $websiteId]);
            if ($collections && $collections->getSize()) {
                foreach ($collections as $item) {
                    $item->delete();
                }
            }
            foreach ($addressList as $erpAddress) {
                if ($erpAddress['shipNumber'] != '' && $erpAddress['shipState'] != '' && $erpAddress['shipCity'] != '') {
                    $data['customer_id'] = $customerRepository->getId();
                    $data['erp_user_id'] = $userId;
                    $data['customer_email'] = $email;
                    $data['erp_account_number'] = $accountNumber;
                    $data['ship_to_number'] = $erpAddress['shipNumber'];
                    $data['website_id'] = $websiteId;
                    $data['order_approval'] = 0;
                    if (isset($erpAddress['approveSalesOrder']) && $erpAddress['approveSalesOrder'] == 'yes') {
                        $data['order_approval'] = 1;
                    }

                    /**
                     * check whether the customer already have records or not
                     */
                    $modelSave = $this->orderApprovalFactory->create();
                    $modelSave->setData($data);
                    $modelSave->save();
                    $response['success']['message'][] = $erpAddress['shipNumber'].' - stored';
                }
            }
        } catch (\Exception $e) {
            $response['error']['message'][] = $e->getMessage();
        }
        return $response;
    }

    /**
     * @return array
     */
    public function getAllCustomers()
    {
        $customers = $this->customerFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->load();
        $res = [];
        if ($customers && !empty($customers->getData())) {
            foreach ($customers as $customer) {
                $arr = [];
                $arr['entity_id'] = $customer->getId();
                $arr['email'] = $customer->getEmail();
                $arr['website_id'] = $customer->getWebsiteId();
                $res[] = $arr;
            }
        }
        return $res;
    }
}
