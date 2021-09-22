<?php

namespace Dckap\MultiAccount\Block\Overwrite\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Grid extends \Magento\Customer\Block\Address\Grid
{
    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    private $addressCollection;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $session;

    /**
     * Grid constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CountryFactory $countryFactory
     * @param \Magento\Customer\Model\SessionFactory $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        \Magento\Customer\Model\SessionFactory $session,
        array $data = []
    ) {
        parent::__construct($context, $currentCustomer, $addressCollectionFactory, $countryFactory, $data);
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->session = $session;
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 102.0.1
     */
    protected function _prepareLayout(): void
    {
//        parent::_prepareLayout();
        $this->preparePager();
    }

    /**
     * Get current additional customer addresses
     *
     * Return array of address interfaces if customer has additional addresses and false in other cases
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     * @since 102.0.1
     */
    public function getAdditionalAddresses(): array
    {
        $additional = [];
        $addresses = $this->getAddressCollection();
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array((int)$address->getId(), $primaryAddressIds, true)) {
                $additional[] = $address->getDataModel();
            }
        }
        return $additional;
    }

    /**
     * Get default billing address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling(): int
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultBilling();
    }

    /**
     * Get default shipping address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping(): int
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultShipping();
    }

    /**
     * Get pager layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function preparePager(): void
    {
        $addressCollection = $this->getAddressCollection();
        if (null !== $addressCollection) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.addresses.pager'
            )->setCollection($addressCollection);
            $this->setChild('pager', $pager);
        }
    }

    /**
     * Get customer addresses collection.
     *
     * Filters collection by customer id
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @throws NoSuchEntityException
     */
    private function getAddressCollection(): \Magento\Customer\Model\ResourceModel\Address\Collection
    {
        if (null === $this->addressCollection) {
            if (null === $this->getCustomer()) {
                throw new NoSuchEntityException(__('Customer not logged in'));
            }
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->addressCollectionFactory->create();
            $collection->setOrder('entity_id', 'desc');
            $collection->addFieldToFilter(
                'entity_id',
                ['nin' => [$this->getDefaultBilling(), $this->getDefaultShipping()]]
            );
            $collection->setCustomerFilter([$this->getCustomer()->getId()]);
            $customerSession = $this->session->create();
            $customerSessionData = $customerSession->getCustomData();
            $accountNumber = $customerSessionData['accountNumber'];
//            $collection->addAttributeToFilter('erp_account_number', array('in' => array($accountNumber)));
            $collection->addAttributeToFilter([
                ['attribute' => 'erp_account_number', 'in' => $accountNumber],
                ['attribute' => 'erp_account_number', 'null' => true]
            ],
            '',
            'left');
            $this->addressCollection = $collection;
        }
        return $this->addressCollection;
    }
}
