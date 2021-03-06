<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Model\Authz;

use Magento\Authz\Model\UserLocatorInterface;
use Magento\Authz\Model\UserIdentifier;
use Magento\Webapi\Controller\Request as Request;
use Magento\Integration\Model\Integration\Factory as IntegrationFactory;

/**
 * Web API user locator.
 */
class UserLocator implements UserLocatorInterface
{
    /** @var Request */
    protected $_request;

    /** @var IntegrationFactory */
    protected $_integrationFactory;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param IntegrationFactory $integrationFactory
     */
    public function __construct(Request $request, IntegrationFactory $integrationFactory)
    {
        $this->_request = $request;
        $this->_integrationFactory = $integrationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        $consumerId = $this->_request->getConsumerId();
        $integration = $this->_integrationFactory->create()->loadByConsumerId($consumerId);
        return $integration->getId() ? (int)$integration->getId() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserIdentifier::USER_TYPE_INTEGRATION;
    }
}
