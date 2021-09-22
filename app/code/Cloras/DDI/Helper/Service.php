<?php

namespace Cloras\DDI\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Service extends AbstractHelper
{
    protected $scopeConfig;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getServiceList() {
        $types = $this->scopeConfig->getValue('clorasbase/api/list');
        $types = $this->unserialize($types);
        $list = array();
        if (is_array($types)) {
            foreach ($types as $type) {
                $list[$type['service']] = $type;
            }
        }
        return $list;
    }

    public function getClorasBaseUrl() {
        return $this->scopeConfig->getValue('clorasbase/general/baseurl');
    }

    public function getAuthorizeToken() {
        return $this->scopeConfig->getValue('clorasbase/general/authorization');
    }

    /**
     * Deprecated
     *
     * @return array
     */
    public function getLamdaList() {
        $types = $this->scopeConfig->getValue('clorasbase/lamda/list');
        $types = $this->unserialize($types);
        $list = array();
        if (is_array($types)) {
            foreach ($types as $type) {
                $list[$type['service']] = $type;
            }
        }
        return $list;
    }

    protected function unserialize($value) {
        $data = [];
        if (!$value) {
            return $data;
        }
        try {
            $data = unserialize($value);
        } catch (\Exception $exception) {
            $data = [];
        }
        if (empty($data) && json_decode($value)) {
            $data = json_decode($value, true);
        }
        return $data;
    }
}
