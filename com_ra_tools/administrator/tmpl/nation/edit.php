<?php
/**
 * @version    CVS: 3.0.0
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
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
    method="post" enctype="multipart/form-data" name="adminForm" id="nation-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'nation')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'nation', Text::_('COM_RA_TOOLS_TAB_NATION', true)); ?>
    <div class="row-fluid">
        <div class="col-md-12 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_RA_TOOLS_FIELDSET_NATION'); ?></legend>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('code'); ?>
                <?php echo $this->form->renderField('name'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
