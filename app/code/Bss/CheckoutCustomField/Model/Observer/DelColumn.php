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
namespace Bss\CheckoutCustomField\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class DelColumn
 *
 * @package Bss\CheckoutCustomField\Model\Observer
 */
class DelColumn implements ObserverInterface
{
    /**
     * @var \Bss\CheckoutCustomField\Model\ResourceModel\UpdateBookMark
     */
    protected $updateBookMark;

    /**
     * AddColumn constructor.
     * @param \Bss\CheckoutCustomField\Model\ResourceModel\UpdateBookMark $updateBookMark
     */
    public function __construct(
        \Bss\CheckoutCustomField\Model\ResourceModel\UpdateBookMark $updateBookMark
    ) {
        $this->updateBookMark = $updateBookMark;
    }

    /**
     * @param EventObserver $model
     */
    public function execute(EventObserver $model)
    {
        $attribute = $model->getAttribute();
        $this->updateBookMark->deleteColumn($attribute->getAttributeCode());
        $this->updateBookMark->updateBookMark(2, $attribute->getAttributeCode());
    }
}
