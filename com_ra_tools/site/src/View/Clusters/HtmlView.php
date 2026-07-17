<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ra_tools
 */

namespace Ramblers\Component\Ra_tools\Site\View\Clusters;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $app;
    protected $items;
    protected $menu_id;

    public function display($tpl = null)
    {   
        $this->app = Factory::getApplication();
        $this->menu_id = $this->app->input->getInt('Itemid', '');

        $this->items      = $this->get('Items');

        parent::display($tpl);
    }
}
