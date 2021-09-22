<?php

namespace Cloras\DDI\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    private $context;

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }//end __construct()

    public function execute()
    {
    }
}//end class
