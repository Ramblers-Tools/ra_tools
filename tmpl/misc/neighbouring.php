<?php

/**
 * @version     3.4.1
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie Bigley <webmaster@bigley.me.uk>
 * 20/02/21 Created
 * 15/07/22 CB correct building of link
 * 18/07/22 CB Neighbouring Areas
 * 19/07/22 CB Check that menu parameters are not null
 * 02/01/23 CB show group-code even if not record found in ra_groups
 * 05/01/23 CB  Don't show programme if group name not found
 * 31/08/23 CB use view programme to show walks
 * 22/12/24 CB ensure area code is always upper case
 * 16/04/25 CB optionally show Descriptions
 * 15/09/25 CB omit home group
 * 18/09/25 CB use icon for link to walks
 */
// No direct access
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

defined('_JEXEC') or die;
//if ($this->params->get('show_page_heading') == 1) {
echo '<h2>' . $this->params->get('page_title') . '</h2>';
//}
if (is_null($this->menu_params)) {
    echo 'Using default parameters<br>';
    $programme = '1';
    $display_type = 'G';
    $intro = '';
    $show_area = '0';
} else {
    //  Find any introduction for the page
    $intro = $this->menu_params->get('page_intro');
    //  See if details of group walks are required
    $programme = $this->menu_params->get('programme', '1');
    //  See if this is Neighbouring Groups or Neighbouring Areas (default to G)
    $display_type = $this->menu_params->get('display_type', 'G');
    //  See if details of Area website are required
    $show_area = $this->menu_params->get('show_area', '1');
    $show_desc = $this->menu_params->get('show_desc', '1');
}

if ($display_type == 'A') {
    $show_area = '0';
    $default_group = '';
    $group_list = $this->menu_params->get("area_list");  // 'CH,MR,SS,WK,WO';
} else {
    //  the list of Groups will be in format XXnn,XXnn,XXnn etc
    $default_group = $this->params->get('default_group');
    $group_list = $this->params->get("group_list");
}

$groups = explode(",", $group_list);

if ($intro != '') {
    echo $intro . "<br>";
}

if ($show_area == "1") {
    $objTable = new ToolsTable;
    $objTable->add_column("Area", "L");
    $objTable->add_column("Website", "L");
    if ($programme == '1') {
        $objTable->add_column("Walks", "L");
        $target = 'index.php?option=com_ra_tools&view=programme&group=';
    }
    $objTable->generate_header();
    // See if a website can be found for the home Area
    $area_code = strtoupper(substr($groups[0], 0, 2));
    $item = $this->toolsHelper->getItem("SELECT name, details, website, co_url from #__ra_areas WHERE code='" . $area_code . "'");
    if ($item == '') {
        $objTable->add_item($area_code);
        $objTable->add_item('');
    } else {
        if ($show_desc == '1') {
            $details = '<b>' . $item->name . '</b><br>' . $item->details;
        } else {
            $details = $item->name;
        }
        $objTable->add_item($details);
        if ($item->website == "") {
            $objTable->add_item($this->toolsHelper->buildLink($item->co_url, $item->co_url, True, ""));
        } else {
            $objTable->add_item($this->toolsHelper->buildLink($item->website, $item->website, True, ""));
        }
    }
    if ($programme == '1') {
        $objTable->add_item($this->toolsHelper->imageButton('W', $target . $area_code, true));
    }

    $objTable->generate_line();

    $objTable->generate_table();
}

$objTable = new ToolsTable;
$objTable->add_column("Group", "L");
$objTable->add_column("Website", "L");
if ($programme == '1') {
    $objTable->add_column("Walks", "L");
    $target = 'index.php?option=com_ra_tools&view=programme&group=';
}
$objTable->generate_header();

//echo "groups $group_list<br>";
if ($display_type == 'A') {
    $lookup_table = '#__ra_areas';
} else {
    $lookup_table = '#__ra_groups';
}
foreach ($groups as $group) {
    if ($group !== $default_group) {
        $sql = "SELECT code, name, details, website, co_url from $lookup_table WHERE code='" . $group . "'";
//    echo $sql . '<br>';
        $item = $this->toolsHelper->getItem($sql);
        if ($item == '') {
            if ($show_desc == '1') {
                $details = '<b>' . $group . '</b><br>' . $item->details;
            } else {
                $details = $group;
            }
            $objTable->add_item($details);
            $objTable->add_item('');
            if ($programme == '1') {
                $objTable->add_item('');
            }
        } else {
            if ($show_desc == '1') {
                $details = '<b>' . $item->name . '</b><br>' . $item->details;
            } else {
                $details = $item->name;
            }
            $objTable->add_item($details);
//        $objTable->add_item($item->name);
            //echo $item->name . ' ' . $item->code . '<br>';
            if ($item->website == "") {
                $detail = $this->toolsHelper->buildLink($item->co_url, $item->co_url, True) . '<br>';
            } else {
                $detail = $this->toolsHelper->buildLink($item->website, $item->website, True) . '<br>';
            }
            $objTable->add_item($detail);
            if ($programme == '1') {
                $detail = $this->toolsHelper->imageButton('W', $target . $item->code, true);
                $objTable->add_item($detail);
            }
        }
        $objTable->generate_line();
    }
}
$objTable->generate_table();
//die('abcd');