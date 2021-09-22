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

use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;

/**
 * Token UI Component Provider
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
     * @param \Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory $componentInterfaceFactory
     */
    public function __construct(TokenUiComponentInterfaceFactory $componentInterfaceFactory)
    {
        $this->componentFactory = $componentInterfaceFactory;
    }

    /**
     * Get UI component for token
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken
     * @return \Magento\Vault\Model\Ui\TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => CreditCardConfigProvider::METHOD_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'expiration_date' => $paymentToken->getExpiresAt()
                ],
                'name' => 'Cayan_Payment/js/view/vault'
            ]
        );

        return $component;
    }
}
