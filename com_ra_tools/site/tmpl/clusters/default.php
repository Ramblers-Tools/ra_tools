<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ra_tools
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

        echo '<h2>Clusters</h2>';
        $toolsHelper = new ToolsHelper;
         $target_contact = 'index.php?option=com_contact&view=contact&id=';
        $tot_count = 0;

        $objTable = new ToolsTable();
        $header = 'Code,Name,Areas,';
        $objTable->add_header("Code,Area,Contact,Areas,");
        $target = 'index.php?option=com_ra_tools&task=clusters.showAreas&Itemid=' . $this->menu_id . '&code=';
        $print = '&layout=print&tmpl=component&Itemid=' . $this->menu_id;

        foreach ($this->items as $row) {
            $objTable->add_item($row->code);
            $objTable->add_item($row->Cluster);
            //           $objTable->add_item($row->contact_id);
            if ($row->contact_id == '') {
                $objTable->add_item('');
            } else {
                if ($row->preferred_name == '') {
                    $objTable->add_item($row->name);
                } else {
                    $objTable->add_item($row->preferred_name . ' ' . $toolsHelper->imageButton('E', $target_contact . $row->contact_id, true));
                }
            }
            $area_count = $toolsHelper->getValue('SELECT COUNT(id) FROM #__ra_areas WHERE cluster ="' . $row->code . '"');
            $objTable->add_item($toolsHelper->buildLink($target . $row->code, $area_count));
            $objTable->add_item($toolsHelper->buildLink($target . $row->code . $print, 'Print'));
            $objTable->generate_line();
            $tot_count = $tot_count + $area_count;
        }
         $objTable->generate_table();
        echo $tot_count . ' Areas found<br> ';
