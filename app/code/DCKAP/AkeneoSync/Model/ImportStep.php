<?php

namespace DCKAP\AkeneoSync\Model;

use Akeneo\Connector\Api\Data\LogInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;


class ImportStep extends AbstractModel
{
    /**
     * Import cache tag
     *
     * @var string CACHE_TAG
     */
    const CACHE_TAG = 'dckap_akeneo_connector_import_log_step';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'dckap_akeneo_connector_import_log_step';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('DCKAP\AkeneoSync\Model\ResourceModel\ImportStep');
    }


}
