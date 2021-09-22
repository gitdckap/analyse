<?php

namespace Cloras\DDI\Model;

use Cloras\DDI\Api\ShiptoInterface;

class Shipto implements ShiptoInterface
{
    protected $customerRepository;
    protected $addressRepository;
    protected $addressData;
    protected $regionFactory;
    protected $address;
    protected $addressFactory;
    protected $_customer;
    protected $_countryCollectionFactory;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterface $addressData,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\Address $address,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    )
    {
        $this->_customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->addressData = $addressData;
        $this->regionFactory = $regionFactory;
        $this->address = $address;
        $this->addressFactory = $addressFactory;
        $this->_customer = $customers;
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    public function updateShipTos($addressList = array(), $email = false, $accountNumber = false, $websiteId = false)
    {
        $customerRepository = $this->_customerRepository->get($email, $websiteId);
        $countryData = $this->getCountryData();
        $response = array();
        try {
            $shippingList = array();
            foreach ($addressList as $erpAddress) {
                if ($erpAddress['shipNumber'] != '' && $erpAddress['shipState'] != '' && $erpAddress['shipCity'] != '') {
                    $shippingList[] = $erpAddress['shipNumber'];
                    $addresses = $this->address->getCollection()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('parent_id', array('eq' => $customerRepository->getId()))
                        ->addAttributeToFilter('erp_account_number', array('eq' => $accountNumber))
                        ->addAttributeToFilter('ddi_ship_number', array('eq' => $erpAddress['shipNumber']));
                    if ($addresses && $addresses->count()) {
                        foreach ($addresses as $customerAddress) {
                            $ddiShipNumber = $customerAddress->getData('ddi_ship_number');
                            $erpAccountNumber = $customerAddress->getData('erp_account_number');
                            if ($ddiShipNumber == $erpAddress['shipNumber'] && $erpAccountNumber == $accountNumber) {
                                $firstName = $customerAddress->getFirstname();
                                $lastName = $customerAddress->getLastname();
                                if (isset($erpAddress['shipName']) && $erpAddress['shipName'] != '') {
                                    $shipCompanyName = explode(' ', $erpAddress['shipName']);
                                    if (count($shipCompanyName) > 1) {
                                        $firstName = $shipCompanyName[0];
                                        unset($shipCompanyName[0]);
                                        $lastName = implode(' ', $shipCompanyName);
                                    } else {
                                        $firstName = $shipCompanyName[0];
                                        $lastName = $shipCompanyName[0];
                                    }
                                }
                                if (isset($erpAddress['shipName']) && $erpAddress['shipName'] == '') {
                                    $firstName = "Attn:";
                                    $lastName = "Not Provided";
                                }

                                $region = $this->regionFactory->create();
                                $countryId = 'US';
                                if (isset($erpAddress['shipCountry']) && $erpAddress['shipCountry'] != '' && in_array($erpAddress['shipCountry'], $countryData)) {
                                    $countryId = $erpAddress['shipCountry'];
                                }
                                $regionId = $region->loadByCode($erpAddress['shipState'], $countryId)->getId();

                                $street = array();
                                if (isset($erpAddress['shipAddress1']))
                                    $street[] = $erpAddress['shipAddress1'];
                                if (isset($erpAddress['shipAddress2']))
                                    $street[] = $erpAddress['shipAddress2'];
                                if (isset($erpAddress['shipAddress3']))
                                    $street[] = $erpAddress['shipAddress3'];
                                $phone = (isset($erpAddress['shipPhone']) && $erpAddress['shipPhone'] != '') ? $erpAddress['shipPhone'] : '1234567890';
                                if ($countryId == 'CA' || $countryId == 'CAN') {
                                    $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '000 000';
                                } else {
                                    $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '00000';
                                }

                                $updateAddress = $this->addressRepository->getById($customerAddress->getId());
                                $updateAddress->setId($customerAddress->getId());
                                $updateAddress->setCustomerId($customerAddress->getCustomerId());
                                $updateAddress->setPrefix('');
                                $updateAddress->setFirstname($firstName);
                                $updateAddress->setLastname($lastName);
                                $updateAddress->setCompany($erpAddress['shipCompanyName']);
                                $updateAddress->setCountryId($countryId);
                                $updateAddress->setPostcode($postCode);
                                $updateAddress->setCity($erpAddress['shipCity']);
                                $updateAddress->setTelephone($phone);
                                $updateAddress->setStreet($street);
                                /*if (isset($givenAddr['default_billing'])) {
                                    $updateAddress->setIsDefaultBilling($givenAddr['default_billing']);
                                }
                                if (isset($givenAddr['default_shipping'])) {
                                    $updateAddress->setIsDefaultShipping($givenAddr['default_shipping']);
                                }*/
                                if ($regionId) {
                                    $updateAddress->setRegionId($regionId);
                                }
                                $updateAddress->setCustomAttribute('ddi_ship_number', $erpAddress['shipNumber']);
                                $updateAddress->setCustomAttribute('erp_account_number', $accountNumber);
                                $this->addressRepository->save($updateAddress);
                                $response['update'][] = $updateAddress->getId().' - '.$erpAddress['shipNumber'].' updated';
                            }
                        }
//                        echo 'Address updated successfully';
                    } else {
                        /* add as new shipping address */
                        $response['insert'][] = $erpAddress['shipNumber'].' will be inserted as new address';
                        $this->addNewAddress($customerRepository, $erpAddress, $accountNumber);
                    }
                }
            }

            /* delete shipping address */
            $addresses = $this->address->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('parent_id', array('eq' => $customerRepository->getId()))
                ->addAttributeToFilter('ddi_ship_number', array('neq' => ''))
                ->addAttributeToFilter('ddi_ship_number', array('nin' => $shippingList))
                ->addAttributeToFilter('erp_account_number', array('neq' => ''))
                ->addAttributeToFilter('erp_account_number', array('in' => $accountNumber))
                ->addAttributeToFilter('ddi_ship_number', array('neq' => '00000001'));
            if ($addresses && $addresses->count()) {
                foreach ($addresses as $address) {
                    $response['delete'][] = $address->getId() .' - '.$address->getDdiShipNumber().' deleted';
                    $this->addressRepository->deleteById($address->getId());
                }
            }
        } catch (Exception $e) {
            $response['error']['message'][] = $e->getMessage();
        }
        return $response;
    }

    protected function addNewAddress($customerRepository, $erpAddress, $erpAccountNumber)
    {
        $customerId = $customerRepository->getId();
        $firstName = $customerRepository->getFirstname();
        $lastName = $customerRepository->getLastname();
        if (isset($erpAddress['shipName']) && $erpAddress['shipName'] != '') {
            $shipCompanyName = explode(' ', $erpAddress['shipName']);
            if (count($shipCompanyName) > 1) {
                $firstName = $shipCompanyName[0];
                unset($shipCompanyName[0]);
                $lastName = implode(' ', $shipCompanyName);
            } else {
                $firstName = $shipCompanyName[0];
                $lastName = $shipCompanyName[0];
            }
        }
        if (isset($erpAddress['shipName']) && $erpAddress['shipName'] == '') {
            $firstName = "Attn:";
            $lastName = "Not Provided";
        }
        $countryData = $this->getCountryData();

        $region = $this->regionFactory->create();
        $countryId = 'US';
        if (isset($erpAddress['shipCountry']) && $erpAddress['shipCountry'] != '' && in_array($erpAddress['shipCountry'], $countryData)) {
            $countryId = $erpAddress['shipCountry'];
        }
        $regionId = $region->loadByCode($erpAddress['shipState'], $countryId)->getId();

        $street = array();
        if(isset($erpAddress['shipAddress1']))
            $street[] = $erpAddress['shipAddress1'];
        if(isset($erpAddress['shipAddress2']))
            $street[] = $erpAddress['shipAddress2'];
        if(isset($erpAddress['shipAddress3']))
            $street[] = $erpAddress['shipAddress3'];
        $phone = (isset($erpAddress['shipPhone']) && $erpAddress['shipPhone'] != '') ? $erpAddress['shipPhone'] : '1234567890';
        if ($countryId == 'CA' || $countryId == 'CAN') {
            $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '000 000';
        } else {
            $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '00000';
        }

        $address = $this->addressFactory->create();

        $address->setFirstname($firstName)
            ->setLastname($lastName)
            ->setCountryId($countryId)
            ->setRegionId($regionId)
            ->setRegion($erpAddress['shipState'])
            ->setCity($erpAddress['shipCity'])
            ->setPostcode($postCode)
            ->setCustomerId($customerId)
            ->setStreet($street)
            ->setCompany($erpAddress['shipCompanyName'])
            ->setTelephone($phone)
            ->setSaveInAddressBook('1');
        try {
            $newAddress = $address->save();
            $updateAddress = $this->addressRepository->getById($newAddress->getId());
            $updateAddress->setCustomAttribute('ddi_ship_number', $erpAddress['shipNumber']);
            $updateAddress->setCustomAttribute('erp_account_number', $erpAccountNumber);
            $this->addressRepository->save($updateAddress);
        }  catch (\Exception $e) {
            echo $e->getMessage();
        }
        return true;
    }

    protected function getCountryData()
    {
        $res = array();
        $countryCollection = $this->_countryCollectionFactory->create()->loadByStore();
        if ($countryCollection && $countryCollection->count()) {
            foreach ($countryCollection as $country) {
                $res[] = $country->getCountryId();
            }
        }
        return $res;
    }

    public function getAllCustomers()
    {
        $customers = $this->_customer->getCollection()
            ->addAttributeToSelect("*")
            ->load();
        $res = array();
        if ($customers && $customers->count()) {
            foreach ($customers as $customer) {
                $arr = array();
                $arr['entity_id'] = $customer->getId();
                $arr['email'] = $customer->getEmail();
                $arr['website_id'] = $customer->getWebsiteId();
                $res[] = $arr;
            }
        }
        return $res;
    }

    /**
     * Setup default shipping and billing address from ValidateUser response
     *
     * @param mixed $validateUser
     * @param string $email
     * @param string $websiteId
     *
     * @return array|void|null
     */
    public function setupShipTo($validateUser, $email, $websiteId)
    {
        $customerRepository = $this->_customerRepository->get($email, $websiteId);
        if (isset($validateUser['user']) && count($validateUser['user'])) {
            $createDefaultShipTo = $this->addDefaultShipto($customerRepository, $validateUser['user'][0]);
            $createDefaultBillTo = $this->addDefaultBillto($customerRepository, $validateUser['user'][0]);
            if ($createDefaultShipTo['status'] && $createDefaultBillTo['status']) {
                return [['status' => true, 'msg' => 'Shipto and Billto successfully creatd']];
            } else {
                return [['status' => false, 'msg' => $createDefaultShipTo['msg'].' -&- '.$createDefaultBillTo['msg']]];
            }
        }
        return false;
    }

    protected function addDefaultShipto($customerRepository, $erpAddress)
    {
        $msg = '';
        $status = false;
        $customerId = $customerRepository->getId();
        $firstName = $customerRepository->getFirstname();
        $lastName = $customerRepository->getLastname();
        if (isset($erpAddress['shipCompanyName']) && $erpAddress['shipCompanyName'] != '') {
            $shipCompanyName = explode(' ', $erpAddress['shipCompanyName']);
            if (count($shipCompanyName) > 1) {
                $firstName = $shipCompanyName[0];
                unset($shipCompanyName[0]);
                $lastName = implode(' ', $shipCompanyName);
            } else {
                $firstName = $shipCompanyName[0];
                $lastName = $shipCompanyName[0];
            }
        }
        $countryData = $this->getCountryData();

        $region = $this->regionFactory->create();
        $countryId = 'US';
        if (isset($erpAddress['shipCountry']) && $erpAddress['shipCountry'] != '' && in_array($erpAddress['shipCountry'], $countryData)) {
            $countryId = $erpAddress['shipCountry'];
        }
        $regionId = $region->loadByCode($erpAddress['shipState'], $countryId)->getId();

        $street = array();
        if(isset($erpAddress['shipAddress1']))
            $street[] = $erpAddress['shipAddress1'];
        if(isset($erpAddress['shipAddress2']))
            $street[] = $erpAddress['shipAddress2'];
        if(isset($erpAddress['shipAddress3']))
            $street[] = $erpAddress['shipAddress3'];
        $phone = (isset($erpAddress['shipPhone']) && $erpAddress['shipPhone'] != '') ? $erpAddress['shipPhone'] : '1234567890';
        if ($countryId == 'CA' || $countryId == 'CAN') {
            $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '000 000';
        } else {
            $postCode = (isset($erpAddress['shipPostCode']) && $erpAddress['shipPostCode'] != '') ? $erpAddress['shipPostCode'] : '00000';
        }

        $address = $this->addressFactory->create();

        $address->setFirstname($firstName)
            ->setLastname($lastName)
            ->setCountryId($countryId)
            ->setRegionId($regionId)
            ->setRegion($erpAddress['shipState'])
            ->setCity($erpAddress['shipCity'])
            ->setPostcode($postCode)
            ->setCustomerId($customerId)
            ->setStreet($street)
            ->setCompany($erpAddress['shipCompanyName'])
            ->setTelephone($phone)
            ->setIsDefaultShipping(true)
            ->setSaveInAddressBook('1');
        try {
            $newAddress = $address->save();
            $updateAddress = $this->addressRepository->getById($newAddress->getId());
            $updateAddress->setCustomAttribute('ddi_ship_number', $erpAddress['shiptoId']);
            $updateAddress->setCustomAttribute('erp_account_number', $erpAddress['accountNumber']);
            $updatedAddress = $this->addressRepository->save($updateAddress);
            $msg = 'shipto created successfully';
            $status = true;
        }  catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        return ['status' => $status, 'msg' => $msg];
    }

    protected function addDefaultBillto($customerRepository, $erpAddress)
    {
        $msg = '';
        $status = false;
        $customerId = $customerRepository->getId();
        $firstName = $customerRepository->getFirstname();
        $lastName = $customerRepository->getLastname();
        if (isset($erpAddress['billCompanyName']) && $erpAddress['billCompanyName'] != '') {
            $shipCompanyName = explode(' ', $erpAddress['billCompanyName']);
            if (count($shipCompanyName) > 1) {
                $firstName = $shipCompanyName[0];
                unset($shipCompanyName[0]);
                $lastName = implode(' ', $shipCompanyName);
            } else {
                $firstName = $shipCompanyName[0];
                $lastName = $shipCompanyName[0];
            }
        }
        $countryData = $this->getCountryData();

        $region = $this->regionFactory->create();
        $countryId = 'US';
        if (isset($erpAddress['billCountry']) && $erpAddress['billCountry'] != '' && in_array($erpAddress['billCountry'], $countryData)) {
            $countryId = $erpAddress['billCountry'];
        }
        $regionId = $region->loadByCode($erpAddress['billState'], $countryId)->getId();

        $street = array();
        if(isset($erpAddress['billAddress1']))
            $street[] = $erpAddress['billAddress1'];
        if(isset($erpAddress['billAddress2']))
            $street[] = $erpAddress['billAddress2'];
        if(isset($erpAddress['billAddress3']))
            $street[] = $erpAddress['billAddress3'];
        $phone = (isset($erpAddress['billPhone']) && $erpAddress['billPhone'] != '') ? $erpAddress['billPhone'] : '1234567890';
        if ($countryId == 'CA' || $countryId == 'CAN') {
            $postCode = (isset($erpAddress['billPostCode']) && $erpAddress['billPostCode'] != '') ? $erpAddress['billPostCode'] : '000 000';
        } else {
            $postCode = (isset($erpAddress['billPostCode']) && $erpAddress['billPostCode'] != '') ? $erpAddress['billPostCode'] : '00000';
        }

        $address = $this->addressFactory->create();

        $address->setFirstname($firstName)
            ->setLastname($lastName)
            ->setCountryId($countryId)
            ->setRegionId($regionId)
            ->setRegion($erpAddress['billState'])
            ->setCity($erpAddress['billCity'])
            ->setPostcode($postCode)
            ->setCustomerId($customerId)
            ->setStreet($street)
            ->setCompany($erpAddress['billCompanyName'])
            ->setTelephone($phone)
            ->setIsDefaultBilling(true)
            ->setSaveInAddressBook('1');
        try {
            $newAddress = $address->save();
            $updateAddress = $this->addressRepository->getById($newAddress->getId());
            $updateAddress->setCustomAttribute('erp_account_number', $erpAddress['accountNumber']);
            $updatedAddress = $this->addressRepository->save($updateAddress);
            $msg = 'Billto created successfully';
            $status = true;
        }  catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        return ['status' => $status, 'msg' => $msg];
    }
}
