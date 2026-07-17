<?php
/**
 * @version    3.5.5
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2026 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Ramblers\Component\Ra_tools\Api\Extension\Ra_toolsComponent;

return new class implements ServiceProviderInterface
{
  public function register(Container $container)
  {
    $container->registerServiceProvider(new MVCFactory('\\Ramblers\\Component\\Ra_tools\\Api'));
    $container->registerServiceProvider(new ComponentDispatcherFactory('\\Ramblers\\Component\\Ra_tools\\Api'));

    $container->set(
      ComponentInterface::class,
      function (Container $container)
      {
        $component = new Ra_toolsComponent($container->get(ComponentDispatcherFactoryInterface::class));
        $component->setMVCFactory($container->get(MVCFactoryInterface::class));

        return $component;
      }
    );
  }
};
