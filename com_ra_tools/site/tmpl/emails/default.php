<?php
/**
 * @version    3.3.4
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 31/07/25 CB addresse_name
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_ra_tools') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'emailform.xml');
$canEdit = $user->authorise('core.edit', 'com_ra_tools') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'emailform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ra_tools');
$canChange = $user->authorise('core.edit.state', 'com_ra_tools');
$canDelete = $user->authorise('core.delete', 'com_ra_tools');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ra_tools.list');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif; ?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">
          <?php
          if (!empty($this->filterForm)) {
              echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
          }
          ?>
    <div class="table-responsive">
        <table class="table table-striped" id="emailList">
            <thead>
                <tr>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Date sent', 'a.date_sent', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Attach', 'a.attachments', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Sender', 'a.sender_name', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Addressee', 'a.addressee_name', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Sub system', 'a.sub_system', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Type', 'a.record_type', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Reference', 'a.ref', $listDirn, $listOrder); ?>
                    </th>

                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                        <div class="pagination">
                            <?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php $canEdit = $user->authorise('core.edit', 'com_ra_tools'); ?>
                    <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_ra_tools')): ?>
                        <?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
                    <?php endif; ?>

                    <tr class="row<?php echo $i % 2; ?>">

                        <td>
                            <?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_tools.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
                            <?php if ($canCheckin && $item->checked_out > 0) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_ra_tools&task=email.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'email.', false); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo Route::_('index.php?option=com_ra_tools&view=email&id=' . (int) $item->id); ?>">
                                <?php echo $this->escape($item->id); ?></a>
                        </td>

                        <?php
                        echo '<td>' . HTMLHelper::_('date', $item->date_sent, 'H:i D d/m/y') . '</td>';
                        echo '<td>' . $item->title . '</td>';

                        echo '<td>';
                        if (!empty($item->attachments)) {
                            $attachmentsArr = (array) explode(',', $item->attachments);
                            foreach ($attachmentsArr as $singleFile) {
                                if (!is_array($singleFile)) {
                                    $uploadPath = 'com_ra_tools/emails' . DIRECTORY_SEPARATOR . $singleFile;
                                    echo '<a href="' . Route::_(Uri::root() . $uploadPath, false) . '" target="_blank" title="See the attachments">' . $singleFile . '</a> ';
                                }
                            }
                        } else {
                            echo $item->attachments;
                        }
                        echo '</td>';

                        echo '<td>' . $item->sender_name . '<br>' . $item->sender_email . '</td>';
                        echo '<td>' . $item->addressee_name . '<br>' . $item->addressee_email . '</td>';
                        echo '<td>' . $item->sub_system . '</td>';
                        echo '<td>' . $item->record_type . '</td>';
                        echo '<td>' . $item->ref . '</td>';
                        ?>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
