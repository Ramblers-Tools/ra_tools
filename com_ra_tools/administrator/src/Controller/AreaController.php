<?php

/**
 * @version    3.0.5
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 * 21/02/25 CB created
 * 22/02/25 CB
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

/**
 * Area controller class.
 *
 * @since  3.0.0
 */
class AreaController extends FormController {

    protected $back;
    protected $objHelper;
    protected $view_list = 'areas';

    public function __construct() {
        parent::__construct();
        //      $this->db = Factory::getContainer()->get('DatabaseDriver');
        $this->objHelper = new ToolsHelper;
        //      $this->objApp = Factory::getApplication();
        $this->back = 'administrator/index.php?option=com_ra_tools&view=area_list';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    public function showArea() {
        $code = Factory::getApplication()->input->getCmd('code', 'NS');
        $objHelper = new ToolsHelper;
        $sql = "SELECT * FROM #__ra_areas  ";
        $sql .= "WHERE code = '" . $code . "'";
//            echo $sql;
        $area = $objHelper->getItem($sql);
        ToolBarHelper::title($area->name);
        echo 'Code <b>' . $area->code . '</b><br>';
        echo 'Name <b>' . $area->name . '</b><br>';
        echo 'Details <b>' . $area->details . '</b><br>';
        echo 'Website <b>' . $area->website . '</b><br>';
        echo 'Head office site <b>' . $area->co_url . '</b><br>';
        echo 'Latitude <b>' . $area->latitude . '</b><br>';
        echo 'Longitude <b>' . $area->longitude . '</b><br>';
        //       echo 'Website <b>' . $area->website . '</b><br>';
        echo $objHelper->backButton($this->back);
    }

    public function showGroups() {
        $code = Factory::getApplication()->input->getCmd('area', '');
        $sql = 'SELECT name from #__ra_areas ';
        $sql .= 'WHERE code ="' . $code . '"';
        $area = $this->objHelper->getValue($sql);
        ToolBarHelper::title('Groups in Area ' . $area);
        $sql = 'SELECT * from #__ra_groups ';
        $sql .= 'WHERE code like"' . $code . '%"';
        $sql .= 'ORDER BY name ';

        $objTable = new ToolsTable;
        $objTable->add_header("Code,Name,Website,CO link,Location");
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->code);
            $objTable->add_item($row->name);
            if ($row->website == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($this->objHelper->buildLink($row->website, $row->website, true));
            }
            if ($row->co_url == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($this->objHelper->buildLink($row->co_url, $row->co_url, true));
            }
            $map_pin = $this->objHelper->showLocation($row->latitude, $row->longitude, 'O');
            $objTable->add_item($map_pin);
            $objTable->generate_line();
        }
        $objTable->generate_table();

        echo $this->objHelper->backButton($this->back);
    }

}
