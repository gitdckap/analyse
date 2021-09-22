<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\Data\IntegrationItemsInterfaceFactory;
use Cloras\Base\Api\IntegrationInterface;
use Cloras\Base\Model\ResourceModel\Integration\CollectionFactory as IntegrationCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Request\Http;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Api\AuthorizationServiceInterface;
use Magento\Integration\Model\Oauth\Token;

class Integration implements IntegrationInterface
{
    private $integrationItemFactory;

    private $tokenCollectionFactory;

    private $integrationCollection;

    private $logger;

    public function __construct(
        Json $jsonHelper,
        \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory $tokenCollectionFactory,
        IntegrationItemsInterfaceFactory $integrationItemFactory,
        Http $request,
        IntegrationCollectionFactory $integrationCollection,
        \Psr\Log\LoggerInterface $logger,
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        AuthorizationServiceInterface $authorizationService,
        Token $oauthToken
    ) {
        $this->jsonHelper             = $jsonHelper;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->integrationItemFactory = $integrationItemFactory;
        $this->request                = $request;
        $this->integrationCollection  = $integrationCollection;
        $this->logger                 = $logger;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->oauthToken = $oauthToken;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface
     */
    public function testCredentials()
    {
        $integrationItems = $this->integrationItemFactory->create();

        $status = ['status' => 'success'];

        $integrationItems->setResponseMessage($status);

        return $integrationItems;
    }//end testCredentials()

    /**
     * @param string $batchInfo
     *
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface
     */
    public function createIntegration($batchInfo)
    {
        try {
            $integrationItems = $this->integrationItemFactory->create();

            $status = [];

            $integrations = $this->jsonHelper->unserialize($batchInfo);

            $this->integrationCollection->create()->createIntegration($integrations);
            $status = ['status' => 'success'];
        } catch (\Exception $e) {
            $status = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }

        $integrationItems->setResponseMessage($status);

        return $integrationItems;
    }//end createIntegration()


    public function createNewIntegration($name = 'Cloras', $email = 'info@cloras.com')
    {

        $integrationItems = $this->integrationItemFactory->create();

        $responseMsg = [];
        $endpoint = '';

        $integrationExists = $this->integrationService->findByName($name)->getData();

        if (empty($integrationExists)) {
            $integrationData = [
                'name' => $name,
                'email' => $email,
                'status' => '1',
                'endpoint' => $endpoint,
                'setup_type' => '0'
            ];
            try {
                // Code to create Integration
                $integration = $this->integrationService->create($integrationData);
                $integrationId = $integration->getId();
                $consumerName = 'Integration' . $integrationId;


                // Code to create consumer
                $consumer = $this->oauthService->createConsumer(['name' => $consumerName]);
                $consumerId = $consumer->getId();
                $integration->setConsumerId($consumer->getId());
                $integration->save();


                // Code to grant permission
                $this->authorizationService->grantAllPermissions($integrationId);


                // Code to Activate and Authorize
                $token = $this->oauthToken;
                $uri = $token->createVerifierToken($consumerId);
                $token->setType('access');
                $token->save();
                $responseMsg = ['status' => 'success', 'message' => $name .  ' Integration created successfully'];
            } catch (\Exception $e) {
                $responseMsg = ['status' => 'failure', 'message' => $e->getMessage()];
                
            }
        } else {
            $responseMsg = ['status' => 'success', 'message' => "The ". $name .  ' Integration already created'];
        }

        $integrationItems->setResponseMessage($responseMsg);

        return $integrationItems;
    }
}//end class
