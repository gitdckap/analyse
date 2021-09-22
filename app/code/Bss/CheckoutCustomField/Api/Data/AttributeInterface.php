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
 
interface AttributeInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const BSS_ATTRIBUTE_ID = 'attribute_id';
    const BSS_ATTRIBUTE_CODE = 'attribute_code';
    const BSS_BACKEND_LABEL = 'backend_label';
    const BSS_FRONTEND_LABEL = 'frontend_label';
    const BSS_FRONTEND_CLASS = 'frontend_class';
    const BSS_FRONTEND_INPUT = 'frontend_input';
    const BSS_IS_REQUIRED = 'is_required';
    const BSS_STORE_ID = 'store_id';
    const BSS_IS_USER_DEFIEND = 'is_user_defined';
    const BSS_DEFAULT_VALUE = 'default_value';
    const BSS_VISIBLE_FRONTEND = 'visible_frontend';
    const BSS_VISIBLE_BACKEND = 'visible_backend';
    const BSS_SHOW_IN_SHIPPING = 'show_in_shipping';
    const BSS_SORT_ORDER = 'sort_order';
    const BSS_SAVE_IN_FUTURE = 'save_in_future';
    const BSS_SHOW_GIRD = 'show_gird';
    const BSS_SHOW_IN_ORDER = 'show_in_order';
    const BSS_SHOW_IN_PDF = 'show_in_pdf';
    const BSS_SHOW_IN_EMAIL = 'show_in_email';
 
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
     * Get ATTRIBUTE .
     *
     * @return varchar
     */
    public function getAttributeCode();
 
    /**
     * Set ATTRIBUTE CODE.
     */
    public function setAttributeCode($attributeCode);
 
    /**
     * Get BACKEND LABEL.
     *
     * @return varchar
     */
    public function getBackendLabel();
 
    /**
     * Set BACKEND LABEL.
     */
    public function setBackendLabel($backendLabel);

    /**
     * Get FRONTEND LABEL.
     *
     * @return varchar
     */
    public function getFrontendLabel($storeId = null);
 
    /**
     * Set FRONTEND LABEL.
     */
    public function setFrontendLabel($fontendLabel);

    /**
     * Get FRONTEND CLASS.
     *
     * @return varchar
     */
    public function getFrontendClass();
 
    /**
     * Set FRONTEND CLASS.
     */
    public function setFrontendClass($fontendClass);

    /**
     * Get FRONTEND INPUT.
     *
     * @return varchar
     */
    public function getFrontendInput();
 
    /**
     * Set FRONTEND INPUT.
     */
    public function setFrontendInput($fontendInput);

    /**
     * Get IS REQUIRED.
     *
     * @return int
     */
    public function getIsRequired();
 
    /**
     * Set IS REQUIRED.
     */
    public function setIsRequired($isRequired);

    /**
     * Get STORE ID.
     *
     * @return int
     */
    public function getStoreId();
 
    /**
     * Set STORE ID.
     */
    public function setStoreId($storeId);

    /**
     * Get IS USER DEFIEND.
     *
     * @return int
     */
    public function getIsUserDefiend();
 
    /**
     * Set IS USER DEFIEND.
     */
    public function setIsUserDefiend($isUserDefiend);
    
    /**
     * Get DEFAULT VALUE.
     *
     * @return varchar
     */
    public function getDefaultValue();
 
    /**
     * Set DEFAULT VALUE.
     */
    public function setDefaultValue($defaultValue);

    /**
     * Get VISIBLE FRONTEND.
     *
     * @return int
     */
    public function getVisibleFrontend();
 
    /**
     * Set VISIBLE FRONTEND.
     */
    public function setVisibleFrontend($visibleFrontend);

    /**
     * Get VISIBLE BACKEND.
     *
     * @return int
     */
    public function getVisibleBackend();
 
    /**
     * Set VISIBLE BACKEND.
     */
    public function setVisibleBackend($visibleBackend);

    /**
     * Get SHOW IN.
     *
     * @return int
     */
    public function getShowIn();
 
    /**
     * Set SHOW IN.
     */
    public function setShowIn($showIn);

    /**
     * Get SORT ORDER.
     *
     * @return int
     */
    public function getSortOrder();
 
    /**
     * Set SORT ORDER.
     */
    public function setSortOrder($sortOrder);

    /**
     * Get SAVE IN FUTURE.
     *
     * @return int
     */
    public function getSaveInFuture();
 
    /**
     * Set SAVE IN FUTURE.
     */
    public function setSaveInFuture($saveInFuture);

    /**
     * Get SHOW GIRD.
     *
     * @return int
     */
    public function getShowGird();
 
    /**
     * Set SHOW GIRD.
     */
    public function setShowGird($showGird);

    /**
     * Get SHOW IN ORDER.
     *
     * @return int
     */
    public function getShowInOrder();
 
    /**
     * Set SHOW IN ORDER.
     */
    public function setShowInOrder($showInOrder);

    /**
     * Get SHOW IN PDF.
     *
     * @return int
     */
    public function getShowInPdf();
 
    /**
     * Set SHOW IN PDF.
     */
    public function setShowInPdf($showInPdf);

    /**
     * Get SHOW IN EMAIL.
     *
     * @return int
     */
    public function getShowInEmail();
 
    /**
     * Set SHOW IN EMAIL.
     */
    public function setShowInEmail($showInEmail);
}
