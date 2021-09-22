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

namespace Cayan\Payment\Model\Ui\Adminhtml;

use Cayan\Payment\Block\Vault as VaultBlock;
use Cayan\Payment\Model\Ui\CreditCardConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Token Admin UI Provider
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var \Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory
     */
    private $componentFactory;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory $componentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        UrlInterface $urlBuilder
    ) {
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $data = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => CreditCardConfigProvider::METHOD_VAULT_CODE,
                    'nonceUrl' => $this->getNonceRetrieveUrl(),
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $data,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'template' => 'Cayan_Payment::form/vault.phtml'
                ],
                'name' => VaultBlock::class
            ]
        );

        return $component;
    }

    /**
     * Get URL to retrieve payment method nonce
     *
     * @return string
     */
    private function getNonceRetrieveUrl()
    {
        return $this->urlBuilder->getUrl('cayan/payment/nonce', ['_secure' => true]);
    }
}
