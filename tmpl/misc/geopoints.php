<?php

/**
 * Shows the contents of a file with details of geographical points
 *
 *
 * @version     5.1.1
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 13/12/23 CB created
 * 24/09/24 CB use recursive folderlist
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

// No direct access
defined('_JEXEC') or die;

echo '<h2>' . $this->params->get('page_title') . '</h2>';

//  Find any introduction for the page
$intro = $this->menu_params->get('page_intro');
//  Find any page footer
$page_footer = $this->menu_params->get('page_footer', '1');
// Get file locations
$target_folder = $this->params->get('target_folder', '');
$working_folder = ToolsHelper::addSlash(JPATH_ROOT . '/images/' . $target_folder);
$file = $this->menu_params->get('file', '');

$show_file = $this->menu_params->get('show_file', 'N');
$download = $this->menu_params->get('download', 'N');

if (!$intro == '') {
    echo $intro . '<br>';
}


if (!file_exists($working_folder)) {
    $text = "Folder ' . $working_folder . ' does not exist, unable to list contents";

    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

$target = $working_folder . $file;
if (!file_exists($target)) {
    $text = 'File ' . $target . ' does not exist';

    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

$list = new RLeafletCsvList($target);
$list->display();

if ($show_file == 'Y') {
    echo 'File is ' . $target;
}
if ($download == 'Y') {
    echo $this->toolsHelper->buildLink($target, 'Download', False, 'link-button button-p0186');
}
//echo 'user ' . $this->user->id;
//if ($this->user->id > 0) {
//    if ($this->canDo->get('core.edit' == true)) {
//      echo $this->toolsHelper->buildLink($target, 'Download', False, 'link-button button-p0186');
//    }
//}
echo '<br>';
if ($page_footer != '') {
    echo $page_footer . "<br>";
}

