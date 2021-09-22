<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Helper\Strategy;

use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;

/**
 * Class RegExAllNegationMatcher
 *
 * @package BitExpert\ForceCustomerLogin\Helper\Strategy
 */
class RegExAllNegationMatcher extends RegExAllMatcher implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function isMatch($url, WhitelistEntry $rule)
    {
        return !parent::isMatch($url, $rule);
    }
}
