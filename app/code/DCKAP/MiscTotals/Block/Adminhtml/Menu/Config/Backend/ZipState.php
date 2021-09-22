<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Adminhtml\Menu\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

class ZipState extends ConfigValue
{
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        /** @var array $value */
        $value = $this->getValue();
        unset($value['__empty']);
        $encodedValue = $this->serializer->serialize($value);

        $this->setValue($encodedValue);
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        /** @var string $value */
        $value = $this->getValue();
        if ($value) {
            $decodedValue = $this->serializer->unserialize($value);

            $this->setValue($decodedValue);
        }
    }
}
