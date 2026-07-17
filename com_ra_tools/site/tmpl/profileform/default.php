<?php
/**
 * @version    CVS: 3.4.4
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

echo __file__ . $this->item->id;
var_dump($this->item);
$canEdit = Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_ra_tools');

if (!$canEdit && Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_ra_tools')) {
    $canEdit = Factory::getApplication()->getIdentity()->id == $this->item->created_by;
}
?>

<div class="item_fields">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
    <?php endif; ?>
    <table class="table">


        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_STATE'); ?></th>
            <td>
                <i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_GROUPS_TO_FOLLOW'); ?></th>
            <td><?php echo $this->item->real_name; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_NOTIFY_EMAIL'); ?></th>
            <td><?php echo $this->item->email; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_PREFERRED_NAME'); ?></th>
            <td><?php echo $this->item->preferred_name; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_RADIUS'); ?></th>
            <td><?php echo $this->item->radius; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_HOME_GROUP'); ?></th>
            <td><?php echo $this->item->home_group; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_EMAIL'); ?></th>
            <td><?php echo $this->item->email; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_RA_TOOLS_FORM_LBL_PROFILE_REAL_NAME'); ?></th>
            <td><?php echo $this->item->real_name; ?></td>
        </tr>

    </table>

</div>

<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_tools.' . $this->item->id) || $this->item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
<?php if ($canEdit && $this->item->checked_out == 0): ?>

    <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_ra_tools&task=profile.edit&id=' . $this->item->id); ?>"><?php echo Text::_("COM_RA_TOOLS_EDIT_ITEM"); ?></a>
<?php elseif ($canCheckin && $this->item->checked_out > 0) : ?>
    <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_ra_tools&task=profile.checkin&id=' . $this->item->id . '&' . Session::getFormToken() . '=1'); ?>"><?php echo Text::_("JLIB_HTML_CHECKIN"); ?></a>

<?php endif; ?>

<?php if (Factory::getApplication()->getIdentity()->authorise('core.delete', 'com_ra_tools.profile.' . $this->item->id)) : ?>

    <a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
        <?php echo Text::_("COM_RA_TOOLS_DELETE_ITEM"); ?>
    </a>

    <?php
    echo HTMLHelper::_(
            'bootstrap.renderModal',
            'deleteModal',
            array(
                'title' => Text::_('COM_RA_TOOLS_DELETE_ITEM'),
                'height' => '50%',
                'width' => '20%',
                'modalWidth' => '50',
                'bodyHeight' => '100',
                'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_ra_tools&task=profile.remove&id=' . $this->item->id, false, 2) . '" class="btn btn-danger">' . Text::_('COM_RA_TOOLS_DELETE_ITEM') . '</a>'
            ),
            Text::sprintf('COM_RA_TOOLS_DELETE_CONFIRM', $this->item->id)
    );
    ?>

<?php endif; ?>

