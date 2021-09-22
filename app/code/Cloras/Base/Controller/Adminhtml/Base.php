<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cloras\Base\Controller\Adminhtml;

/**
 * Adminhtml Cloras Base controller.
 *
 * @author                                         Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Base extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Cloras_Base::base';
}//end class
