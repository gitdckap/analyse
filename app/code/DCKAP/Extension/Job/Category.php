<?php

namespace DCKAP\Extension\Job;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Output as OutputHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\ManagerInterface;
use DCKAP\Extension\Model\Payment as PaymentModel;

/**
 *  Category Operations
 *
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2019 Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
class Category extends \Akeneo\Connector\Job\Category
{
    protected $paymentModel;
    public function __construct(
        OutputHelper $outputHelper,
        ManagerInterface $eventManager,
        Authenticator $authenticator,
        TypeListInterface $cacheTypeList,
        Entities $entitiesHelper,
        StoreHelper $storeHelper,
        ConfigHelper $configHelper,
        CategoryModel $categoryModel,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PaymentModel $paymentmodel,
        array $data = []
    ) {
        parent::__construct($outputHelper, $eventManager, $authenticator, $cacheTypeList, $entitiesHelper,
                            $storeHelper, $configHelper, $categoryModel, $categoryUrlPathGenerator, $data);
        $this->paymentModel = $paymentmodel;
    }

    /**
     * Insert families in the temporary table
     *
     * @return void
     */
    public function insertData()
    {
        /** @var string|int $paginationSize */
        $paginationSize = $this->configHelper->getPanigationSize();
        /** @var ResourceCursorInterface $categories */
        $categories = $this->akeneoClient->getCategoryApi()->all($paginationSize);
        /** @var string $warning */
        $warning = '';

        /* add/update default category entry in akeneo_connector_entities table */
        $connection = $this->entitiesHelper->getConnection();
        $pimEntitiesTable = $this->entitiesHelper->getTable('akeneo_connector_entities');
//        $select = $connection->select()->from($pimEntitiesTable)->where('code = ?', 'default_category')
//                    ->where('import = ?', 'category');
//        $result = $connection->fetchAll($select);
        $result = $this->paymentModel->getCategoriesList($pimEntitiesTable);
        if ($result && !empty($result)) {
            foreach ($result as $res) {
                if ($res['entity_id'] != '2') {
                    $res['entity_id'] = '2';
                    $this->paymentModel->insertEntitiesById($pimEntitiesTable, $res);
                    //$connection->update($pimEntitiesTable, $res, ['id = ?' => (int)$res['id']]);
                }
            }
        } else {
            $data = [
                'import' => 'category',
                'code' => 'default_category',
                'entity_id' => '2'
            ];
            $this->paymentModel->insertEntities($pimEntitiesTable, $data);
        }

        /* add default category in temporary category table  */
        $rootCategory = [];
        $rootCategory['_links']['self']['href'] = '';
        $rootCategory['code'] = 'default_category';
        $rootCategory['parent'] = null;
        $rootCategory['labels']['en_US'] = 'Default Category';
        $this->entitiesHelper->insertDataFromApi($rootCategory, $this->getCode());
        /**
         * @var int $index
         * @var array $category
         */
        foreach ($categories as $index => $category) {
            /** @var string[] $lang */
            $lang = $this->storeHelper->getStores('lang');
            $warning = $this->checkLabelPerLocales($category, $lang, $warning);

            /* Make master category is the child of default category */
            if ($category['code'] == 'master') {
                $category['parent'] = 'default_category';
                $category['labels']['en_US'] = 'Shop By Category';
            }

            $this->entitiesHelper->insertDataFromApi($category, $this->getCode());
        }
        $index++;

        $this->setMessage(
            __('%1 line(s) found. %2', $index, $warning)
        );
    }

}

