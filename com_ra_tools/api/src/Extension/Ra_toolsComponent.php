<?php
/**
 * @version    3.5.5
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2026 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Api\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class Ra_toolsComponent extends MVCComponent implements BootableExtensionInterface
{
  public function boot(ContainerInterface $container)
  {
    // No API-specific bootstrapping required.
  }
}
