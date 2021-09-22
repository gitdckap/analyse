<?php
namespace FME\GoogleMapsStoreLocator\Controller;

use FME\GoogleMapsStoreLocator\Helper\Data;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Router
 * @package FME\GoogleMapsStoreLocator\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{

    /**
     * @var ActionFactory
     */
    protected $actionFactory;
    /**
     * @var ResponseInterface
     */
    protected $_response;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * Router constructor.
     * @param ActionFactory $actionFactory
     * @param RequestInterface $request
     * @param Data $helper
     * @param PageRepositoryInterface $pageRepository
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        RequestInterface $request,
        Data $helper,
        PageRepositoryInterface $pageRepository,
        ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_request = $request;
        $this->pageRepository = $pageRepository;
        $this->_response = $response;
        $this->googleMapsStoreHelper = $helper;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|void
     */
    public function match(RequestInterface $request)
    {
        $route = $this->googleMapsStoreHelper->getGMapSeoIdentifier();
        $suffix = $this->googleMapsStoreHelper->getGMapSeoSuffix();
        $identifier = trim($request->getPathInfo(), '/');
        $identifie = $route.$suffix;

        if (strcmp($identifie, $identifier)==0) {
            $request->setModuleName('storelocator')->setControllerName('Index')->setActionName('Index');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } else {
              return;
        }
                
        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}
