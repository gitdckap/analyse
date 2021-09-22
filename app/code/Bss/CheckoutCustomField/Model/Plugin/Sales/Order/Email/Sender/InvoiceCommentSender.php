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
namespace Bss\CheckoutCustomField\Model\Plugin\Sales\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class InvoiceCommentSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender
{
    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;
    /**
     * @param Template $templateContainer
     * @param InvoiceCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        InvoiceCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $eventManager
        );
    }

    /**
     * @param Invoice $invoice
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Invoice $invoice, $notify = true, $comment = '')
    {
        $order = $invoice->getOrder();
        $transport = [
            'order' => $order,
            'invoice' => $invoice,
            'comment' => $comment,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'bss_custom_field' => $this->helper->getVariableEmailHtml($order->getBssCustomfield()),
        ];

        $this->eventManager->dispatch(
            'email_invoice_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport);

        return $this->checkAndSend($order, $notify);
    }
}
