<?php

/**
 * @version    3.2.1
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Shows access permissions for the current user
 * 01/01/24 CB stub for possible new option - see bespoke article "Show Access" with embedded PHP code
 * 27/05/24 CB implemented display for all possible components
 * 09/02/25 CB use getUser to chek that user is logged in
 * 07/04/25 CB sort by the name to list of registered groups
 * 08/04/25 CB after view uses CurrentUser
 * 03/05/25 CB use toolsHelper->showAccess
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');

$toolsHelper = new ToolsHelper;

if ($this->user->id == 0) {
    throw new \Exception('Access not permitted', 401);
}
$toolsHelper->showAccess($this->user->id);
echo $toolsHelper->backButton($this->back);

