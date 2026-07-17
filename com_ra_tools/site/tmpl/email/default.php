<?php

/**
 * @version    3.2.3
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
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$toolsHelper = new ToolsHelper;

echo '<div class="item_fields">';

if ($this->params->get('show_page_heading')) {
    echo '<div class="page-header">';
    echo '<h1>' . $this->escape($this->params->get('page_heading')) . '</h1>';
    echo '</div>';
}
$objTable = new ToolsTable();
$objTable->add_header("Field,value");

$objTable->add_item('Date sent');
$objTable->add_item(HTMLHelper::_('date', $this->item->date_sent, 'H:i D d/m/y'));
$objTable->generate_line();

$objTable->add_item('Sub system');
$objTable->add_item($this->item->sub_system);
$objTable->generate_line();

$objTable->add_item('Record type');
$objTable->add_item($this->item->record_type);
$objTable->generate_line();

$objTable->add_item('Reference');
$objTable->add_item($this->item->ref);
$objTable->generate_line();

$objTable->add_item('Addressee');
$objTable->add_item($this->item->addressee);
$objTable->generate_line();

$objTable->add_item('Title');
$objTable->add_item($this->item->title);
$objTable->generate_line();

$objTable->add_item('Body');
$objTable->add_item($this->item->body);
$objTable->generate_line();

$objTable->add_item('Attachments');
$attachments = '';
foreach ((array) $this->item->attachments as $singleFile) {
    if (!is_array($singleFile)) {
        $uploadPath = 'com_ra_tools/emails' . DIRECTORY_SEPARATOR . $singleFile;
        $attachments .= '<a href="' . Route::_(Uri::root() . $uploadPath, false) . '" target="_blank">' . $singleFile . '</a> ';
    }
}
$objTable->add_item($attachments);
$objTable->generate_line();

$objTable->add_item('Sender');
$objTable->add_item($this->item->sender_name . ', ' . $this->item->sender_email);
$objTable->generate_line();

$objTable->generate_table();
echo '<div>';

$target = 'index.php?option=com_ra_tools&view=emails';
$menu = Factory::getApplication()->getMenu()->getActive;
$target .= '&Itemid=' . $menu->id;
echo $toolsHelper->backButton($target);

