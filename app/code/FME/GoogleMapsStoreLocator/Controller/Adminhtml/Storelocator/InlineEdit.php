<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Controller\Adminhtml\Storelocator;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cms\Api\Data\PageInterface;
use FME\GoogleMapsStoreLocator\Model\Storelocator as ModelStorelocator;

/**
 * Class InlineEdit
 * @package FME\GoogleMapsStoreLocator\Controller\Adminhtml\Storelocator
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var ModelStorelocator
     */
    protected $StorelocatorModel;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param ModelStorelocator $StorelocatorModel
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        ModelStorelocator $StorelocatorModel,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->jsonFactory = $jsonFactory;
        $this->StorelocatorModel = $StorelocatorModel;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);

        if (!$this->getRequest()->getParam('isAjax') && empty($postItems)) {
            $messages[] = __('Please correct the data sent.');
            $error = true;
        }
        foreach (array_keys($postItems) as $array_key) {
            $Id = $array_key;
        }

        $Storelocator = $this->StorelocatorModel->load($Id);

        try {
            $Data = $this->filterPost($postItems[$Id]);
            $this->validatePost($Data, $Storelocator, $error, $messages);
            $extendedPageData = $Storelocator->getData();
            $this->setGoogleMapsStoreLocatorStorelocatorData($Storelocator, $extendedPageData, $Data);
            $this->StorelocatorModel->save($Storelocator);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages[] = $this->getErrorWithPageId($Storelocator, $e->getMessage());
            $error = true;
        } catch (\RuntimeException $e) {
            $messages[] = $this->getErrorWithPageId($Storelocator, $e->getMessage());
            $error = true;
        } catch (\Exception $e) {
            $messages[] = $this->getErrorWithPageId(
                $Storelocator,
                __('Something went wrong while saving the item.')
            );
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $pageData = $this->dataProcessor->filter($postData);
        $pageData['custom_theme'] = isset($pageData['custom_theme']) ? $pageData['custom_theme'] : null;
        $pageData['custom_root_template'] = isset($pageData['custom_root_template'])
            ? $pageData['custom_root_template']
            : null;
        return $pageData;
    }

    /**
     * @param array $pageData
     * @param ModelStorelocator $page
     * @param $error
     * @param array $messages
     */
    protected function validatePost(
        array $pageData,
        \FME\GoogleMapsStoreLocator\Model\Storelocator $page,
        &$error,
        array &$messages
    ) {
        if (!($this->dataProcessor->validate($pageData) && $this->dataProcessor->validateRequireEntry($pageData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithPageId($page, $error->getText());
            }
        }
    }

    /**
     * @param ModelStorelocator $page
     * @param $errorText
     * @return string
     */
    protected function getErrorWithPageId(ModelStorelocator $page, $errorText)
    {
        return '[Page ID: ' . $page->getId() . '] ' . $errorText;
    }

    /**
     * @param ModelStorelocator $page
     * @param array $extendedPageData
     * @param array $pageData
     * @return $this
     */
    public function setGoogleMapsStoreLocatorStorelocatorData(
        ModelStorelocator $page,
        array $extendedPageData,
        array $pageData
    ) {
        $page->setData(array_merge($page->getData(), $extendedPageData, $pageData));
        return $this;
    }
}
