<?php

/**
 * @version    3.2.3
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 26/07/25 CB showByRef
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

/**
 * Emails class.
 *
 * @since  2.0
 */
class EmailsController extends FormController {

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional
     * @param   array   $config  Configuration array for model. Optional
     *
     * @return  object	The model
     *
     * @since   2.0
     */
    public function getModel($name = 'Emails', $prefix = 'Site', $config = array()) {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function showByRef() {
        $ref = $this->input->getInt('ref', 0);
        $toolsHelper = new ToolsHelper;
        $sql = 'SELECT e.id, e.sub_system, e.record_type, e.date_sent, e.title, ';
        $sql .= 'e.body, e.sender_name, e.sender_email, e.addressee_name, e.addressee_email ';
        $sql .= 'FROM #__ra_emails AS e ';
        $sql .= 'WHERE e.ref=' . $ref;
        $sql .= ' ORDER BY sub_system ASC ,e.record_type ASC, e.date_sent DESC';
        $objTable = new ToolsTable;
        $objTable->add_header("Sub system,Type,Date,Title,Sender,Addressee,id");
        $rows = $toolsHelper->getRows($sql);
        if ((count($rows) > 0)) {
            echo '<h2>Emails from or to you</h2>';
            $target = 'index.php?option=com_ra_tools&task=emails.showEmail';
            foreach ($rows as $row) {

                $objTable->add_item($row->sub_system);
                $objTable->add_item($row->record_type);
                $objTable->add_item($row->date_sent);
                $objTable->add_item($row->title);
                $objTable->add_item($row->sender_name);
                $objTable->add_item($row->addressee_name);
                $objTable->add_item($row->id);
                $objTable->generate_line();
            }
            $objTable->generate_table();
            if (count($rows) > 1) {
                echo count($rows) . ' Emails<br>';
            }
        }
    }

}
