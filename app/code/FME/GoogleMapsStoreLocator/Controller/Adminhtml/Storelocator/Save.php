<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Controller\Adminhtml\Storelocator;

use Magento\Backend\App\Action;
use FME\GoogleMapsStoreLocator\Model\Storelocator;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'FME_GoogleMapsStoreLocator::save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    protected $model;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    protected $_storeManager;
    const ALL_STORE_VIEWS = '0';
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        Storelocator $model,
        DataPersistorInterface $dataPersistor,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->model = $model;
        $this->serializer = $serializer;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $arrStoreIds = $arrPostStoreView = [];
       $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);

            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Storelocator::STATUS_ENABLED;
            }

            if (empty($data['gmaps_id'])) {
                $data['gmaps_id'] = null;
            }

            $arrPostStoreView = (array) $data['store_id'];
            if(array_key_exists( SELF::ALL_STORE_VIEWS, array_flip( $arrPostStoreView ) )) {
                $stores_list = $this->_storeManager->getStores(true, true);
                foreach ($stores_list as $storekey => $storevalue) {
                    if ($storevalue->getIsActive() && ($storevalue->getId() != SELF::ALL_STORE_VIEWS)) {
                        array_push($arrStoreIds, $storevalue->getId());
                    }
                }
            }else{
                $arrStoreIds = $data['store_id'];
            }

           $data['store_id'] = $this->serializer->serialize($arrStoreIds);

            $id = $this->getRequest()->getParam('gmaps_id');
            if ($id) {
                $this->model->load($id);
            }

            $this->model->setData($data);

            $this->_eventManager->dispatch(
                'googlemapsstorelocator_storelocator_prepare_save',
                ['Storelocator' => $this->model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['gmaps_id' => $this->model->getId(), '_current' => true]);
            }

            try {
                $this->model->save();
                $this->messageManager->addSuccess(__('You saved the store.'));
                $this->dataPersistor->clear('googlemapsstorelocator');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['gmaps_id' => $this->model->getId(),
                         '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the store.'));
            }

            $this->dataPersistor->set('googlemapsstorelocator', $data);
            return $resultRedirect->setPath('*/*/edit', ['gmaps_id' => $this->getRequest()->getParam('gmaps_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
