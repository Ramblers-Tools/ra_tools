<?php

/**
 *  @version    3.3.12
 * @package     com_ra_tools
 * @copyright   Copyleft (C) 2021
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk

 * 06/03/24 CB Created
 * 19/03/24 CB added ra_faults and com_ra_paths
 * 29/04/24 CB added mod_ra_paths, removed com_ra_faults
 * 18/08/26 CB added plg_ra_events and plg_ra_mailman
 * 21/08/26 CB added plg_ra_eventscli and plg_ramblerwalks
 * 01/09/25 CB correct names for plugins
 * 03/09/25 CB use ToolsHelper
 */
// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

// No direct access
defined('_JEXEC') or die;

$toolsHelper = new ToolsHelper;

echo '<h2>' . $this->params->get('page_title') . '</h2>';
echo $toolsHelper->showExtensions();
if (ComponentHelper::isEnabled('com_ra_mailman', true)) {

}
echo '<br>';
