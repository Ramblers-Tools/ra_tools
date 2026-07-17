<?php
/**
 * @package     com_ra_tools
 * @subpackage  api
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * API router for ra-tools
 */

namespace Ramblers\Component\Ra_tools\Api;

use Joomla\CMS\Router\ApiRouter;

class Router extends ApiRouter
{
    public function __construct($app = null)
    {
        parent::__construct($app);
        // Register the clusters endpoint
        $this->registerController('clusters', __NAMESPACE__ . '\Controller\ClustersController');
    }
}
