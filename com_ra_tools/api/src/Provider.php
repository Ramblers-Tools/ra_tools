<?php
/**
 * @package     com_ra_tools
 * @subpackage  api
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * API service provider for ra-tools
 */

namespace Ramblers\Component\Ra_tools\Api;

use Joomla\CMS\Service\Provider\ServiceProviderInterface;
use Joomla\CMS\Service\Container;

class Provider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set('Ra_toolsApiRouter', function ($container) {
            return new Router();
        });
    }
}
