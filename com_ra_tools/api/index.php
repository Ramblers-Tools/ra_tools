<?php
/**
 * @package     com_ra_tools
 * @subpackage  api
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * API entry point for ra-tools
 */

declare(strict_types=1);

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

// Bootstrap the Joomla API application
$app = Factory::getApplication();

// You can add API-specific bootstrap logic here if needed

// This file can be used as the main entry point for custom API logic
