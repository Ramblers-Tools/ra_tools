<?php

/**
 *  @version    3.3.15
 * @package     com_ra_tools
 * @copyright   Copyleft (C) 2021
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk

 * 14/01/24 CB createLink and showConfig
 * 04/03/24 CB created isPresent
 * 25/08/25 CB help
 * 11/09/25 CB show Sitename in title
 * 13/04/26 CB separate template for Corporate MailMan
 */
// No direct access

namespace Ramblers\Component\Ra_tools\Administrator\View\Dashboard;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    protected $help_url;

    public function display($tpl = null): void {
        $layout = $this->getDashboardLayout();
        $this->help_url = $this->getHelpUrl($layout);
        $this->setLayout($layout);
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function getDashboardLayout(): string {
        if (!ComponentHelper::isEnabled('com_ra_mailman', true)) {
            return 'default';
        }

        $params = ComponentHelper::getParams('com_ra_mailman');

        return $params->get('full_version') === 'Y' ? 'default' : 'mailman';
    }

    protected function getHelpUrl(string $layout): string {
        switch ($layout) {
            case 'mailman':
                return 'https://docs.stokeandnewcastleramblers.org.uk/ramblers-components.html#corporate-mailman';

            default:
                return 'https://docs.stokeandnewcastleramblers.org.uk/ramblers-components.html';
        }
    }
    

    protected function addToolbar() {
        // Suppress menu side panel
        //       Factory::getApplication()->input->set('hidemainmenu', true);        $config = Factory::getConfig();

        ToolbarHelper::title('RA Dashboard ' . Factory::getConfig()->get('sitename'), "generic");
        ToolbarHelper::help('', false, $this->help_url);
    }

    public function createLink($component, $view, $caption) {
        $objHelper = new ToolsHelper;
        $target = '<li><a href="index.php?option=' . $component . '&amp;view=' . $view . '"';
        $target .= '" target="_self">' . $caption . ' (';

        switch ($view) {
            case 'area_list';
                $table = 'areas';
                break;
            case 'group_list';
                $table = 'groups';
                break;
            case 'mail_lsts':
                $table = 'mail_lists';
                break;
            case 'mailshots':
                $table = 'mail_shots';
                break;
            case 'subscriptions':
                $table = 'mail_subscriptions';
                break;
            default:
                $table = $view;
        }
        $sql = 'SELECT COUNT(*) FROM #__ra_' . $table;
        $count = $objHelper->getValue($sql);
        $target .= number_format($count);
        $target .= ')</a></li>' . PHP_EOL;
        return $target;
    }

    public function isPresent($extension, $type = 'component') {
        /*
          replacement for ComponentHelper::isEnabled, since that can give erroneous error
          Returns the extension id or False
         */
        $objHelper = new ToolsHelper;
        $sql = 'SELECT extension_id from #__extensions WHERE element="' . $extension . ' "';
        $sql .= 'AND type="' . $type . '" ';
        $sql .= 'AND enabled=1';
        $id = $objHelper->getValue($sql);
        if ($id) {
            return $id;
        } else {
            return false;
        }
    }

    public function showConfig($component) {
        $objHelper = new ToolsHelper;
        $target = '<li><a href="index.php?option=com_config&amp;view=component&amp;component=' . $component . '" target="_self">Configure ' . $component . ' (';
        $sql = 'SELECT s.version_id ';
        $sql .= 'FROM #__schemas AS s ';
        $sql .= 'INNER JOIN #__extensions as e ON e.extension_id = s.extension_id ';
        $sql .= 'WHERE e.element="' . $component . '"';
//        return $sql;
        $target .= $objHelper->getValue($sql);
        $target .= ')</a></li>' . PHP_EOL;
        return $target;
    }

}
