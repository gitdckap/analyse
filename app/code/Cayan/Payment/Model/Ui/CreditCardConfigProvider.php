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

namespace Cayan\Payment\Model\Ui;

use Cayan\Payment\Gateway\Config\Credit\Config as CreditApiConfig;
use Cayan\Payment\Gateway\Config\Gift\Config as GiftApiConfig;
use Cayan\Payment\Gateway\Config\Vault\Config as VaultConfig;
use Cayan\Payment\Gateway\Config\General as GeneralApiConfig;
use Cayan\Payment\Helper\Data as CayanHelper;
use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Credit Card Configuration UI Provider
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class CreditCardConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'cayancc';
    const METHOD_VAULT_CODE = 'cayancc_vault';
    const METHOD_CARD_CODE = 'cayancard';

    /**
     * @var \Cayan\Payment\Gateway\Config\General
     */
    private $generalApiConfig;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $creditApiConfig;
    /**
     * @var \Cayan\Payment\Gateway\Config\Gift\Config
     */
    private $giftCardApiConfig;
    /**
     * @var \Cayan\Payment\Gateway\Config\Vault\Config
     */
    private $vaultConfig;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $cardHelper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Cayan\Payment\Gateway\Config\General $generalApiConfig
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $creditApiConfig
     * @param \Cayan\Payment\Gateway\Config\Gift\Config $giftCardApiConfig
     * @param \Cayan\Payment\Gateway\Config\Vault\Config $vaultConfig
     * @param \Cayan\Payment\Helper\Data $helper
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        GeneralApiConfig $generalApiConfig,
        CreditApiConfig $creditApiConfig,
        GiftApiConfig $giftCardApiConfig,
        VaultConfig $vaultConfig,
        CayanHelper $helper,
        DiscountHelper $discountHelper,
        UrlInterface $url
    ) {
        $this->generalApiConfig = $generalApiConfig;
        $this->creditApiConfig = $creditApiConfig;
        $this->giftCardApiConfig = $giftCardApiConfig;
        $this->vaultConfig = $vaultConfig;
        $this->helper = $helper;
        $this->cardHelper = $discountHelper;
        $this->urlBuilder = $url;
    }

    /**
     * Retrieve the payment method configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'webApiKey' => $this->generalApiConfig->getWebApiKey(),
                    'isActive' => $this->creditApiConfig->isActive(),
                    'ccmonths' => $this->getMonths(),
                    'ccyears' => $this->getYears(),
                    'debug' => $this->creditApiConfig->debug(),
                    'mode' => $this->creditApiConfig->getPaymentAction(),
                    'flags' => $this->helper->getCardFlags(),
                    'flags_image' => $this->helper->getFlagImages(),
                    'ccv_image' => $this->helper->getCcvImage()
                ],
                self::METHOD_CARD_CODE => [
                    'giftcard_discount' => $this->cardHelper->getAmountDiscountInQuote(),
                    'giftcard_codes' => $this->cardHelper->getJsCodes(),
                    'giftcard_remove_url' => $this->urlBuilder->getUrl('cayan/code/remove'),
                    'giftcard_total' => $this->cardHelper->getTotalCardsAmount(),
                    'giftcard_check_url' => $this->urlBuilder->getUrl('cayan/code/validateCart', ['_secure' => true]),
                    'giftcard_add_url' => $this->urlBuilder->getUrl('cayan/code/add', ['_secure' => true]),
                    'giftcard_max_length' => $this->giftCardApiConfig->getMaxLength(),
                    'pin_enabled' => ($this->giftCardApiConfig->isPinEnabled()) ? 1 : 0
                ]
            ],
            'vault' => [
                self::METHOD_VAULT_CODE => [
                    'title' => $this->vaultConfig->getTitle()
                ]
            ]
        ];
    }

    /**
     * Retrieve  months to be used on frontend checkout
     *
     * @return array
     */
    private function getMonths()
    {
        return [
            ['value' => 1, 'month' => __('January (01)')],
            ['value' => 2, 'month' => __('February (02)')],
            ['value' => 3, 'month' => __('March (03)')],
            ['value' => 4, 'month' => __('April (04)')],
            ['value' => 5, 'month' => __('May (05)')],
            ['value' => 6, 'month' => __('June (06)')],
            ['value' => 7, 'month' => __('July (07)')],
            ['value' => 8, 'month' => __('August (08)')],
            ['value' => 9, 'month' => __('September (09)')],
            ['value' => 10, 'month' => __('October (10)')],
            ['value' => 11, 'month' => __('November (11)')],
            ['value' => 12, 'month' => __('December (12)')],
        ];
    }

    /**
     * Retrieve years to be used on frontend checkout
     *
     * @return array
     */
    private function getYears()
    {
        $years = [
            [
                'value' => date('Y'),
                'year' => date('y')
            ]
        ];

        $i = 1;

        while ($i <= 8) {
            $years[] = [
                'value' => date('Y', strtotime('+' . $i . ' years')),
                'year' => date('y', strtotime('+' . $i . ' years'))
            ];

            $i++;
        }

        return $years;
    }
}
