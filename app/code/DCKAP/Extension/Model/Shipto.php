<?php

namespace DCKAP\Extension\Model;

use Cloras\DDI\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Option\ArrayInterface;

/**
 * @api
 * @since 100.0.2
 */
class Shipto implements ArrayInterface
{

    protected $clorasHelper;
    protected $clorasDDIHelper;
    private $customerSession;

    public function __construct(
        \Cloras\Base\Helper\Data $clorasHelper,
        Data $clorasDDIHelper,
        Session $customerSession
    ) {
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->customerSession = $customerSession;
    }

    public function getShiptoItems()
    {
        $shiptoItems = $this->customerSession->getShiptoItems();
        if ($shiptoItems && !empty($shiptoItems)) {
            return $shiptoItems;
        } else {
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('ship_to');
            if ($status) {
                $responseData = $this->clorasDDIHelper->getShiptoItems($integrationData);
                if ($responseData && !empty($responseData)) {
                    $this->customerSession->setShiptoItems($responseData);
                    return $responseData;
                }
            }
        }

        return false;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $shiptoItems = $this->getShiptoItems();
        $resArray = [];
        if ($shiptoItems && !empty($shiptoItems)) {
            foreach ($shiptoItems as $shiptoItem) {
                $arr = [];
                $arr['value'] = $shiptoItem['shipNumber'];
                $arr['label'] = $shiptoItem['shipCompanyName'];
                $resArray[] = $arr;
            }
        }
        return $resArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $shiptoItems = $this->getShiptoItems();
        $resArray = [];
        if ($shiptoItems && !empty($shiptoItems)) {
            foreach ($shiptoItems as $shiptoItem) {
                $resArray[$shiptoItem['shipNumber']] = $shiptoItem['shipCompanyName'];
            }
        }
        return $resArray;
    }
}
