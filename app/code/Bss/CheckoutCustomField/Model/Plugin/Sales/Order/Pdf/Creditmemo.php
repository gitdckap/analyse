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
namespace Bss\CheckoutCustomField\Model\Plugin\Sales\Order\Pdf;

use Magento\Framework\Json\Helper\Data as JsonHelper;
/**
 * Sales Order Creditmemo PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class Creditmemo extends \Magento\Sales\Model\Order\Pdf\Creditmemo
{
    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        JsonHelper $jsonHelper,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
    }

    /**
     * @param array $creditmemos
     * @return \Zend_Pdf
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($creditmemos = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                $this->_localeResolver->emulate($creditmemo->getStoreId());
                $this->_storeManager->setCurrentStore($creditmemo->getStoreId());
            }
            $page = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Credit Memo # ') . $creditmemo->getIncrementId());

            /* Add custom field*/
            if($this->helper->showInPdf())
                $this->insertBssChekoutCustomField($page, $order);

            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        }
        $this->_afterGetPdf();
        if ($creditmemo->getStoreId()) {
            $this->_localeResolver->revert();
        }
        return $pdf;
    }

    /**
     * @param mixed $page
     * @param Order $order
     */
    public function insertBssChekoutCustomField(&$page, $order)
    {
        $customField = $order->getBssCustomfield();
        if ($customField && $this->helper->hasDataCustomFieldPdf($customField) && $this->helper->moduleEnabled()) {
            $bssCustomfield = $this->jsonHelper->jsonDecode($customField);

            $this->y -= -8;
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20);
            $yDraw = $this->returnYDraw($bssCustomfield);

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
            $page->drawRectangle(25, $this->y - 20, 570, $this->y - $yDraw);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
            $this->_setFontBold($page, 12);

            $page->drawText($this->helper->getTitle($order->getStore()->getId()), 35, $this->y - 13, 'UTF-8');
            $this->_setFontRegular($page, 10);

            $this->y -= $this->returnY($bssCustomfield, $page);
        }        
    }

    /**
     * @param array $bssCustomfield
     * @return int
     */
    private function returnYDraw($bssCustomfield)
    {
        $yDraw = 33;
        foreach($bssCustomfield as $field)
        {
            if($field['show_in_pdf'] == '1' && ((is_array($field['value']) && !empty($field['value'])) || $field['value'] != ""))
            {
                $yDraw += 20;
            }
        }
        return $yDraw;
    }

    /**
     * @param array $bssCustomfield
     * @param mixed $page
     * @return int
     */
    private function returnY($bssCustomfield, $page)
    {
        $y = 33;
        foreach($bssCustomfield as $field) {
            if($field['show_in_pdf'] == '1' && ((is_array($field['value']) && !empty($field['value'])) || $field['value'] != ""))
            {
                $fieldText = $field['frontend_label'] . ': ';
                if(is_array($field['value']))
                {
                    foreach($field['value'] as $value)
                    {
                        $fieldText .= $value . ",";
                    }
                } else {
                    $fieldText .= $field['value'];
                }
                $page->drawText($fieldText, 33, $this->y - $y, 'UTF-8');
                $y += 20;
            }
        }
        $y += 8;
        return $y;
    }
}
