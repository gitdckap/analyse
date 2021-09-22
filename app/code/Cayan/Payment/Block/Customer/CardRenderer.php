<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Block\Customer;

use Cayan\Payment\Model\Ui\CreditCardConfigProvider;
use Cayan\Payment\Helper\Data as CayanHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

/**
 * Credit Card Renderer Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\CcConfigProvider $iconsProvider
     * @param \Cayan\Payment\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CcConfigProvider $iconsProvider,
        CayanHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $iconsProvider, $data);

        $this->helper = $helper;
    }

    /**
     * Check if the specified token can be rendered
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === CreditCardConfigProvider::METHOD_CODE;
    }

    /**
     * Retrieve the last four digits of the credit card number
     *
     * @return string
     */
    public function getNumberLast4Digits()
    {
        $tokenDetails = $this->getTokenDetails();

        return array_key_exists('maskedCC', $tokenDetails) ? $tokenDetails['maskedCC'] : '';
    }

    /**
     * Retrieve the credit card's expiration date
     *
     * @return string
     */
    public function getExpDate()
    {
        $token = $this->getToken();

        if (is_null($token)) {
            return '';
        }

        return date('m/Y', strtotime($token->getExpiresAt()));
    }

    /**
     * Retrieve the credit card image URL
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * Retrieve the credit card image height
     *
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * Retrieve the credit card image width
     *
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }

    /**
     * Retrieve the credit card type
     *
     * @return string
     */
    public function getCardType()
    {
        return $this->helper->getCardType($this->getTokenDetails()['type']);
    }

    /**
     * Retrieve the credit card image URL
     *
     * @return string
     */
    public function getCardImage()
    {
        $cardImages = $this->helper->getFlagImages();

        return $cardImages[(int)$this->getTokenDetails()['type']];
    }
}
