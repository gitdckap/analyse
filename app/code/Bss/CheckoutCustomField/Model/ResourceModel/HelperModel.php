<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CheckoutCustomField\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class HelperModel extends AbstractDb
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * HelperModel constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->resources = $context->getResources();
    }

    /**
     * Construct
     * @return void
     */
    public function _construct()
    {
        $this->_init('quote', 'entity_id');
    }

    /**
     * @param int|null $quoteId
     * @return string|bool
     */
    public function getBssCustomFieldByQuote($quoteId)
    {
        if ($quoteId) {
            $connection = $this->resources->getConnection();
            $channelTable = $this->resources->getTableName('quote');
            $select = $connection->select()
                ->from(
                    $channelTable,
                    ['bss_customfield']
                )->where('entity_id =?', $quoteId);
            $row = $connection->fetchOne($select);
            return $row;
        }
        return false;
    }
}
