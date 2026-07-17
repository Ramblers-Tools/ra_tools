<?php
/**
 * @version    3.4.6
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 08/12/25 CB include hidden field attached_file
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

//return;
//die;
// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_ra_tools', JPATH_SITE);

echo '<h2>' . $this->caption . '</h2>';
?>
<div class="email-edit front-end-edit">
    <form id="form-email"
          action="<?php echo Route::_('index.php?option=com_ra_tools&task=emailform.save'); ?>"
          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

        <input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

        <?php
        echo $this->form->renderField('addressee_name');
        echo $this->form->renderField('sender_name');
        echo $this->form->renderField('sender_email');
        echo $this->form->renderField('title');
        echo $this->form->renderField('body');
        echo $this->form->renderField('attachments');
        echo $this->form->renderField('attached_file');
        ?>
        <?php if (!empty($this->item->attachments)) : ?>
            <?php $attachmentsFiles = array(); ?>
            <?php foreach ((array) $this->item->attachments as $fileSingle) : ?>
                <?php if (!is_array($fileSingle)) : ?>
                    <a href="<?php echo Route::_(Uri::root() . 'com_ra_tools/emails/' . $fileSingle, false); ?>"><?php echo $fileSingle; ?></a> |
                    <?php $attachmentsFiles[] = $fileSingle; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="jform[attachments_hidden]" id="jform_attachments_hidden" value="<?php echo implode(',', $attachmentsFiles); ?>" />
            <?php
        endif;
        echo $this->form->renderField('addressee_email');
        echo $this->form->renderField('sub_system');
        echo $this->form->renderField('record_type');
        echo $this->form->renderField('ref');
        ?>
        <?php if (!empty($this->item->attachments)) : ?>
            <?php $attachmentFiles = array(); ?>
            <?php foreach ((array) $this->item->attachments as $fileSingle) : ?>
                <?php if (!is_array($fileSingle)) : ?>
                    <a href="<?php echo Route::_(Uri::root() . 'images/com_ra_tools' . DIRECTORY_SEPARATOR . $fileSingle, false); ?>"><?php echo $fileSingle; ?></a> |
                    <?php $attachmentFiles[] = $fileSingle; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="jform[attachment_hidden]" id="jform_attachment_hidden" value="<?php echo implode(',', $attachmentFiles); ?>" />
        <?php endif; ?>

        <div class="control-group">
            <div class="controls">

                <?php if ($this->canSave): ?>
                    <button type="submit" class="validate btn btn-primary">
                        <span class="fas fa-check" aria-hidden="true"></span>
                        <?php echo Text::_('JSUBMIT'); ?>
                    </button>
                <?php endif; ?>
                <a class="btn btn-danger"
                   href="<?php echo Route::_('index.php?option=com_ra_tools&task=emailform.cancel'); ?>"
                   title="<?php echo Text::_('JCANCEL'); ?>">
                    <span class="fas fa-times" aria-hidden="true"></span>
                    <?php echo Text::_('JCANCEL'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="option" value="com_ra_tools"/>
        <input type="hidden" name="task"
               value="emailform.save"/>
               <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

