<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_ra_tools
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<form action="<?php echo Route::_('index.php?option=com_ra_tools&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="form-horizontal">
        <?php echo $this->form->renderFieldset('basic'); ?>
    </div>
    <div>
        <button type="submit" class="btn btn-primary validate">
            <?php echo Text::_('JSAVE'); ?>
        </button>
        <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_ra_tools&view=clusters'); ?>">
            <?php echo Text::_('JCANCEL'); ?>
        </a>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
