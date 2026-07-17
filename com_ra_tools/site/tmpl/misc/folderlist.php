<?php

/**
 * @version     5.1.4
 * @package     com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 23/09/24 CB use recursive folderlist instead of folder / sub folder
 * 24/09/24 CB prepare for deleting of files (need SystemController)
 * 29/09/24 CB replace JPATH_SITE by JPATH_ROOT
 * 21/10/24 prepend images/ to folder name
 * 02/11/24 CB correct check for directory
 * 08/11/24 CB show folder name in italics
 * 23/12/24 CB conditional display of folder name
 * 02/01/25 CB allow expansion of sub-folders
 * 11/02/25 CB validation of target folder
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

echo '<h2>' . $this->params->get('page_title') . '</h2>';

$app = Factory::getApplication();
$sort = $this->menu_params->get('sort', 'ASC');
$intro = $this->menu_params->get('page_intro', '');
//$target_folder = $this->menu_params->get('target_folder', '');
$expand = $this->menu_params->get('expand', 'Y');
$num_columns = $this->params->get('num_columns', '2');
$show_folder = $this->menu_params->get('show_folder', 'None');

//$this->working_folder = 'images/' . $target_folder;
if (JDEBUG) {
    echo "root $this->root<br>";
    echo "level $this->level<br>";
    echo "expand $expand<br>";
    echo "target folder is $target_folder<br>";
    echo "working folder is $this->working_folder<br>";
}
if ($target_folder == '-1') {
    Factory::getApplication()->enqueueMessage('No folder has been selected', 'error');
    return;
}
if (!file_exists($this->working_folder)) {
    $text = 'Folder ' . $this->working_folder . ' does not exist';
    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

$folders = array();
$files = array();
$fileTypes = array(".pdf", ".doc", ".docx", ".odt", ".zip", "png");
$canDelete = $this->canDo->get('core.delete');
$target_delete = 'index.php?option=com_ra_tools&task=upload.unlink&menu_id=' . $this->menu_id;
$target_delete .= '&file=';

$self = 'index.php?option=com_ra_tools&view=misc&layout=folderlist&Itemid=' . $this->menu_id;
$self .= '&level=';
$sub_folders = 1;
$len = strlen($this->level);
$depth = (int) (strlen($this->level) / 2) + 1;

if ($expand == 'Y') {
// first seven chacters are /images/
    $heading = substr($this->working_folder, 7);
    if (substr($this->working_folder, -1) == '/') {
        $heading = substr($heading, 0, strlen($heading) - 1);
    }
    echo "<h4>Folder: $heading</h4>";
}

if ($depth == 2) {
    $back = $self . '1';
} else {
//   echo "level is $this->level, depth is $depth<br>";
    $string_length = ( 2 * $depth ) - 3;
    $back = $self . substr($this->level, 0, $string_length);
}

//if (JDEBUG AND ($depth > 1)) {
//    echo 'working folder is ' . $this->working_folder . '<br>Root is ';
//    var_dump($this->root);
//    echo '<br>';
//}
if ($depth > 2) {
    $upper = $this->root . '/';
// First create the link to the original top-level menu entry
    $target = $self . '1';
//
    /*
     * originally, planned to create "breadcrumbs", with a link to each higher level
      $folder_list = explode('/', $this->working_folder);
      $heading = $this->toolsHelper->buildLink($target, $folder_list[1]) . ' > ';
      $count = count($folder_list);
      for ($i = 2; $i < $count; $i++) {
      $upper .= $folder_list[$i];
      $len = strlen($this->level);

      $upper_level = substr($this->level, 0, $len - 1);
      echo "length of $this->level is $len, upper = $upper_level<br>";
      $heading .= $folder_list[$i];
      echo $i . ' ' . $folder_list[$i] . ', link=' . $link . '<br>';
      }

      echo "$heading<br>";
     */
}
if (!$intro == '') {
    echo $intro . '<br>';
}

if ($handle = opendir($this->working_folder)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (is_dir($this->working_folder . '/' . $entry)) {

                if ($expand == 'N') {
//                   echo 'Folder ' . $this->working_folder . $entry . '<br>';
                    $folders[] = '<i>' . $entry . '</i>';
                } else {
                    $level = $this->level . '_' . $sub_folders;
                    $sub_folders++;
                    $link = $self . $level;
                    if (substr($this->working_folder, -1) == '/') {
                        $target = $this->working_folder . $entry;
                    } else {
                        $target = $this->working_folder . '/' . $entry;
                    }
                    $folders[] = 'Sub-folder ' . $this->toolsHelper->buildLink($link, $entry);
                    $this->app->setUserState('com_ra_tools.docs' . $level, $target);
                }
            } else {
                $files[] = $entry;
            }
        }
    }
    closedir($handle);
}
echo "<ul>";
if (count($folders) > 0) {
    natcasesort($folders);

    if ($sort == 'DESC') {
        $folders = array_reverse($folders);
    }
}

if ($files) {
    natcasesort($files);

    if ($sort == 'DESC') {
        $files = array_reverse($files);
    }
} else {
    echo 'No files in <b>' . $this->working_folder . $entry . '</b><br>';
}

$names = array_merge($folders, $files);
/* * *************************************************************************
 * This common code is shared between
 *   com_ra_tools/site/tmpl/upload/default.php AND
 *   com_ra_tools/site/tmpl/misc/folderlist.php
 * it displays the array of $names in the required number of columns,
 * and assumes the $names[] and $num_columns have been defined
 */
$average_count = intdiv(count($names), $num_columns);
if ($average_count == (count($names) / $num_columns)) {
// all columns have the same number of items
    $max_rows = $average_count;
} else {
    $max_rows = $average_count + 1;
}
//echo 'max=' . $max_rows . ', av= ' . $average_count . ', int ' . (count($names) / $num_columns) . '<br>';
$header = '';
for ($col = 0;
        ( $col + 2) <= $num_columns;
        $col++) {
    $header .= ',';
}
$max_pointer = count($names);
//    echo 'cols=' . $num_columns . '<br>';
$objTable = new ToolsTable();
$objTable->add_header($header);

for ($row = 0;
        ($row + 1) <= $max_rows;
        $row++) {
    for ($col = 0;
            ( $col + 1) <= $num_columns;
            $col++) {
        $i = ($col * $max_rows) + $row;
        if ($i < $max_pointer) {
            $value = $names[$i];
            if (substr($value, 0, 3) == 'Sub') {
                $details = $value;
            } else {
                $name = $value;
                if (substr($this->working_folder, -1) == '/') {
                    $target = $this->working_folder . $name;
                } else {
                    $target = $this->working_folder . '/' . $name;
                }
                $details = $this->toolsHelper->buildLink($target, $value, true);
                if ($this->canDo->get('core.delete')) {
//                  echo 'delete' . $target_delete;
                    $details .= $this->toolsHelper->buildLink($target_delete . $value, '<i class="icon-trash" ></i>');
                }
            }
        } else {
            $details = '';
        }
        $objTable->add_item($details);
    }
    $objTable->generate_line(); //echo '</td>';
}
$objTable->generate_table();
/* * ***********************************************************************
 * end of common code
 */

if (count($folders) > 0) {
    echo count($folders) . ' folders ';
}
if (count($files) > 0) {
    if (count($folders) > 0) {
        echo 'and ';
    }
    echo count($files) . ' files';
}

if (($show_folder == 'Public') OR
        (($show_folder == 'Users') AND ($this->user->id > 0))) {
    echo ' in <b>' . $this->working_folder . $entry . '</b>';
}
echo '<br>';

if ($depth > 2) {
    echo $this->toolsHelper->buildButton($self . '1', 'Home', false, 'grey');
}
if ($depth > 1) {
    echo $this->toolsHelper->backButton($back);
}


