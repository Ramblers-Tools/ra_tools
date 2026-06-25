<?php
/**
 * @version    3.4.2
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 13/09/24 Created by component-generator
 * 16/09/24 Replace MailmanHelper with ToolsHelper, add code to display files
 * 18/09/24 CB delete tab, use literals
 * 23/09/24 CB show filenames in table
 * 29/09/24 CB force names into ascending order,replace JPATH_SITE by JPATH_ROOT
 * 08/10/24 CB correct access permissions, change label on button
 * 23/10/24 CB correct lookup of canSave
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_ra_tools', JPATH_ROOT);

$objHelper = new ToolsHelper;

$target_folder = $this->params->get('target_folder', '');
$working_folder = $objHelper->addSlash(JPATH_ROOT . '/images/' . $target_folder);
$intro = $this->params->get('intro', '');
$list_files = $this->params->get('list_files', '1');
$num_columns = $this->params->get('num_columns', '2');
$mime_types = $this->params->get('mime_types', '');
$sort = $this->params->get('sort');

$target_delete = 'index.php?option=com_ra_tools&task=upload.unlink&menu_id=' . $this->menu_id;
$target_delete .= '&file=';

if (!$this->canDo->get('core.create')) {
    echo '<h3>Sorry, you are not authorised to upload files</h3>';
    return 0;
} else {
    echo '<h3>File upload</h3>';
    if ($intro != '') {
        echo $intro . '<br>';
    }
    echo 'Target folder is <b>' . $target_folder . '</b>, permitted types are <b>' . $mime_types . '</b><br>';
}
?>

<div class="upload-edit front-end-edit">


    <form id="form-upload"
          action="<?php echo Route::_('index.php?option=com_ra_tools&task=upload.save'); ?>"
          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

        <input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

        <?php echo $this->form->getInput('modified_by'); ?>
        <?php echo $this->form->renderField('file_name'); ?>

        <?php if (!empty($this->item->file_name)) : ?>
            <?php $file_nameFiles = array(); ?>
            <?php foreach ((array) $this->item->file_name as $fileSingle) : ?>
                <?php if (!is_array($fileSingle)) : ?>
                    <a href="<?php echo Route::_(Uri::root() . '/images/com_ra_tools' . DIRECTORY_SEPARATOR . $fileSingle, false); ?>"><?php echo $fileSingle; ?></a> |
                    <?php $file_nameFiles[] = $fileSingle; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="jform[file_name_hidden]" id="jform_file_name_hidden" value="<?php echo implode(',', $file_nameFiles); ?>" />
        <?php endif; ?>

        <div class="control-group">
            <div class="controls">

                <?php if ($this->canDo->get('core.create')): ?>
                    <button type="submit" class="validate btn btn-primary">
                        <span class="fas fa-check" aria-hidden="true"></span>
                        <?php echo 'Upload'; ?>
                    </button>
                <?php endif; ?>
                <a class="btn btn-danger"
                   href="<?php echo Route::_('index.php?option=com_ra_tools&task=upload.cancel'); ?>"
                   title="<?php echo Text::_('JCANCEL'); ?>">
                    <span class="fas fa-times" aria-hidden="true"></span>
                    <?php echo Text::_('JCANCEL'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="option" value="com_ra_tools"/>
        <input type="hidden" name="task"
               value="upload.save"/>
               <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<?php
if ($list_files != '1') {
    return true;
}
$base = JPATH_BASE . '/images/' . $this->target_folder . '/';
$names = array();
if (!file_exists($working_folder)) {
    $text = "Folder does not exist: " . $working_folder . ". Unable to list contents";
// Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

echo '<h4>Files in ' . $target_folder;
if ($sort == 'DESC') {
    echo ' (descending)';
}
echo '</h4>';
if ($handle = opendir($working_folder)) {
    while (false !== ($entry = readdir($handle))) {
//            echo $working_folder . $entry . '<br>';
        if ($entry != "." && $entry != "..") {
            if (is_dir($working_folder . $entry)) {

            } else {
                $names[] = $entry;
            }
        }
    }
    closedir($handle);
}

if ($names) {
// Remove trailing slash
    $base = substr(uri::base(), 0, -1) . '/images/' . $target_folder . '/';

    // Sorting into ascending order does not seem to work - hence work-around
    natcasesort($names);
    $names = array_reverse($names);
    if ($sort != 'DESC') {
        $names = array_reverse($names);
    }
    echo count($names) . ' files' . '<br>';

    $average_count = intdiv(count($names), $num_columns);
    if ($average_count == (count($names) / $num_columns)) {
// all columns have the same number of items
        $max_rows = $average_count;
    } else {
        $max_rows = $average_count + 1;
    }
//echo 'max=' . $max_rows . ', av= ' . $average_count . ', int ' . (count($names) / $num_columns) . '<br>';
    $header = '';
    for ($col = 0; ( $col + 2) <= $num_columns; $col++) {
        $header .= ',';
    }
    $max_pointer = count($names);
//    echo 'cols=' . $num_columns . '<br>';
    $objTable = new ToolsTable();
    $objTable->add_header($header);

    for ($row = 0; ($row + 1) <= $max_rows; $row++) {
        for ($col = 0; ( $col + 1) <= $num_columns; $col++) {
            $i = ($col * $max_rows) + $row;
            if ($i < $max_pointer) {
                $value = $names[$i];
                //$objTable->add_item('i=' . $i . ' ' . $value);
                $name = $value;
                $details = $objHelper->buildLink($base . $value, $value, true);
                if ($this->canDo->get('core.delete')) {
//                  echo 'delete' . $target_delete;
                    $details .= $objHelper->buildLink($target_delete . $value, '<i class="icon-trash" ></i>');
                }
            } else {
                $details = '';
            }
            $objTable->add_item($details);
        }
        $objTable->generate_line(); //echo '</td>';
    }
    $objTable->generate_table();
} else {
    echo 'No files in ' . $target_folder;
    return 0;
}
?>