<?php

/**
 * @version    CVS: 3.0.0
 * @package    Ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Ramblers\Component\Ra_tools\Administrator\Extension\Ra_toolsComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The Ra_tools service provider.
 *
 * @since  3.0.0
 */
return new class implements ServiceProviderInterface {

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   3.0.0
     */
    public function register(Container $container) {

        $container->registerServiceProvider(new CategoryFactory('\\Ramblers\\Component\\Ra_tools'));
        $container->registerServiceProvider(new MVCFactory('\\Ramblers\\Component\\Ra_tools'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Ramblers\\Component\\Ra_tools'));
        $container->registerServiceProvider(new RouterFactory('\\Ramblers\\Component\\Ra_tools'));

        $container->set(
                ComponentInterface::class,
                function (Container $container) {
                    $component = new Ra_toolsComponent($container->get(ComponentDispatcherFactoryInterface::class));

                    $component->setRegistry($container->get(Registry::class));
                    $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                    $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
                    $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                    return $component;
                }
        );
    }
};
