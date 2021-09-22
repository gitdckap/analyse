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

namespace Bss\CheckoutCustomField\Api\Data;
 
interface AttributeOptionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const BSS_VALUE_ID = 'value_id';
    const BSS_ATTRIBUTE_ID = 'attribute_id';
    const BSS_OPTION_ID = 'option_id';
    const BSS_STORE_ID = 'store_id';
    const BSS_VALUE = 'value';
    const BSS_IS_DEFAULT = 'is_default';

    /**
     * Get Value ID.
     *
     * @return int
     */
    public function getValueId();
 
    /**
     * Set Value Id.
     */
    public function setValueId($valueId);

    /**
     * Get ATTRIBUTE ID.
     *
     * @return int
     */
    public function getAttributeId();
 
    /**
     * Set Id.
     */
    public function setAttributeId($attributeId);

    /**
     * Get Option Id.
     *
     * @return int
     */
    public function getOptionId();
 
    /**
     * Set Option Id.
     */
    public function setOptionId($optionId);

    /**
     * Get Store ID.
     *
     * @return int
     */
    public function getStoreId();
 
    /**
     * Set Store Id.
     */
    public function setStoreId($storeId);

    /**
     * Get Value.
     *
     * @return varchar
     */
    public function getValue();
 
    /**
     * Set Value.
     */
    public function setValue($value);

    /**
     * Get Is Default.
     *
     * @return varchar
     */
    public function getIsDefault();
 
    /**
     * Set Is Default.
     */
    public function setIsDefault($isDefault);
}
