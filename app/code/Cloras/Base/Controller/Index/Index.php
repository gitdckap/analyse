<?php

namespace Cloras\Base\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    private $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
        parent::__construct($context);
    }

    public function execute()
    {
        return "Index Page";
    }
}//end class
