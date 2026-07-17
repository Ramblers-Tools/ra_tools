<?php
/**
 * @version    2.1.0
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 15/09/25 CB add field title
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
    action="<?php echo Route::_('index.php?option=com_ra_tools&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" enctype="multipart/form-data" name="adminForm" id="apisite-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'option')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'option', Text::_('Details', true)); ?>
    <div class="row-fluid">
        <div class="col-md-12 form-horizontal">
            <fieldset class="adminform">
                <?php
                echo $this->form->renderField('sub_system');
                echo $this->form->renderField('title');
                echo $this->form->renderField('url');
                echo $this->form->renderField('token');
                echo $this->form->renderField('colour');
                ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php
    if ($this->item->id > 0) {
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'event5', 'Publishing');
        echo '<div class="row-fluid">';
        echo '<div class="span10 form-horizontal">';
        echo '<fieldset class="adminform">';
        echo $this->form->renderField('state');
        echo $this->form->renderField('created');
        echo $this->form->renderField('created_by');
        echo $this->form->renderField('modified');
        echo $this->form->renderField('modified_by');
        echo $this->form->renderField('id');
        echo $this->form->renderField('event_type_id');
        echo '</fieldset>';
        echo '</div>';
        echo '</div>';
        echo HTMLHelper::_('uitab.endTab');
    }
    ?>
    <input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>