<?php
/**
 * Copyright © DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DCKAP\Catalog\Block\Category;

use Magento\Framework\App\Filesystem\DirectoryList;

class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $configInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    protected $catalogHelper;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\View\ConfigInterface $configInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $configInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Helper\Data $catalogHelper,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->imageHelper = $imageHelper;
        $this->configInterface = $configInterface;
        $this->storeManager = $storeManager;
        $this->imageFactory = $imageFactory;
        $this->categoryRepository = $categoryRepository;
        $this->_filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->layerResolver = $layerResolver;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @return string
     */
    public function getCategoryPlaceHolderImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @return mixed
     */
    public function getSubCategories()
    {
        $category = $this->layerResolver->get()->getCurrentCategory();
        return $category->getChildrenCategories();
    }

    /**
     * @return array
     */
    public function getSubcategoryImageAttributes()
    {
        return $this->configInterface->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE,
            'sub_category'
        );
    }

    /**
     * @param $categoryId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryImage($categoryId)
    {
        $categoryIdElements = explode('-', $categoryId);
        $category = $this->categoryRepository->get(end($categoryIdElements));
        $categoryImage = $category->getImage();

        return $categoryImage;
    }

    /**
     * @param $imageName
     * @param $width
     * @param $height
     * @return bool|string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getResizeImage($imageName, $width, $height)
    {
        /* Real path of image from directory */
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog/category/' . $imageName);

        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        // echo $realPath; exit;
        /* Target directory path where our resized image will be save */
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width . 'x' . $height);

        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        // echo $pathTargetDir; exit;
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();

        // print_r($image); exit;
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width . 'x' . $height . '/' . $imageName;
        }
        return false;
    }
}
