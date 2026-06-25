<?php

/**
 * @version    3.5.3
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 11/07/25 CB include copy of message to sender of email
 * 21/07/25 CB change logic for showing / hiding form fields
 * 30/07/25 CB send emails one at a time (not using bcc)
 * 01/10/25 CB change confirmation message
 * 01/11/25 CB add emails for event attendees - BookingHelper + lookupBooking
 * 09/02/26 CB correct emails to event attendees - only one booking_info
 */

namespace Ramblers\Component\Ra_tools\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\Helper\TagsHelper;
use Ramblers\Component\Ra_events\Site\Helpers\BookingHelper;
use Ramblers\Component\Ra_events\Site\Helpers\EventsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ra_tools model.
 *
 * @since  2.0
 */
class EmailformModel extends FormModel {

    private $item = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @throws  Exception
     */
    protected function populateState() {
        $app = Factory::getApplication('com_ra_tools');

// Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_ra_tools.edit.email.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_ra_tools.edit.email.id', $id);
        }

        $this->setState('email.id', $id);

// Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('email.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Method to get an ojbect.
     *
     * @param   integer $id The id of the object to get.
     *
     * @return  Object|boolean Object on success, false on failure.
     *
     * @throws  Exception
     */
    public function getItem($id = null) {
        if ($this->item === null) {
            $this->item = false;

            if (empty($id)) {
                $id = $this->getState('email.id');
            }

// Get a level row instance.
            $table = $this->getTable();
            $properties = $table->getProperties();
            $this->item = ArrayHelper::toObject($properties, CMSObject::class);

            if ($table !== false && $table->load($id) && !empty($table->id)) {
                $user = Factory::getApplication()->getIdentity();
                $id = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_ra_tools') || $user->authorise('core.create', 'com_ra_tools');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_ra_tools')) {
                    $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                    throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
                }

// Check published state.
                if ($published = $this->getState('filter.published')) {
                    if (isset($table->state) && $table->state != $published) {
                        return $this->item;
                    }
                }

// Convert the Table to a clean CMSObject.
                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, CMSObject::class);
            }
        }

        return $this->item;
    }

    /**
     * Method to get the table
     *
     * @param   string $type   Name of the Table class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array  $config Optional configuration array for Table object
     *
     * @return  Table|boolean Table if found, boolean false on failure
     */
    public function getTable($type = 'Email', $prefix = 'Administrator', $config = array()) {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get an item by alias
     *
     * @param   string $alias Alias string
     *
     * @return int Element id
     */
    public function getItemIdByAlias($alias) {
        $table = $this->getTable();
        $properties = $table->getProperties();

        if (!in_array('alias', $properties)) {
            return null;
        }

        $table->load(array('alias' => $alias));
        $id = $table->id;

        return $id;
    }

    /**
     * Method to check in an item.
     *
     * @param   integer $id The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   2.0
     */
    public function checkin($id = null) {
// Get the id.
        $id = (!empty($id)) ? $id : (int) $this->getState('email.id');

        if ($id) {
// Initialise the table
            $table = $this->getTable();

// Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to check out an item for editing.
     *
     * @param   integer $id The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   2.0
     */
    public function checkout($id = null) {
// Get the user id.
        $id = (!empty($id)) ? $id : (int) $this->getState('email.id');

        if ($id) {
// Initialise the table
            $table = $this->getTable();

// Get the current user object.
            $user = Factory::getApplication()->getIdentity();

// Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  Form    A Form object on success, false on failure
     *
     * @since   2.0
     */
    public function getForm($data = array(), $loadData = true) {
// Get the form.
        $form = $this->loadForm('com_ra_tools.email', 'emailform', array(
            'control' => 'jform',
            'load_data' => $loadData
                )
        );

        $sub_system = Factory::getApplication()->getUserState('com_ra_tools.email.sub_system');
        $record_type = Factory::getApplication()->getUserState('com_ra_tools.email.record_type');
        $ref = Factory::getApplication()->getUserState('com_ra_tools.email.ref');
        $addressee_name = Factory::getApplication()->getUserState('com_ra_tools.email.addressee_name');
        $addressee_email = Factory::getApplication()->getUserState('com_ra_tools.email.addressee_email');
        $sender_name = Factory::getApplication()->getUserState('com_ra_tools.email.sender_name');
        $sender_email = Factory::getApplication()->getUserState('com_ra_tools.email.sender_email');
        if (empty($form)) {
            return false;
        }
// Set value of the form from input
//       $form->setValue('sub_system', $sub_system); DOES NOT WORK
        $form->setFieldAttribute('sub_system', 'default', $sub_system);
        $form->setFieldAttribute('record_type', 'default', $record_type);
        $form->setFieldAttribute('ref', 'default', $ref);
        $form->setFieldAttribute('sender_name', 'default', $sender_name);
        $form->setFieldAttribute('sender_email', 'default', $sender_email);
//        var_dump($addressee_email);
//        die;
        $form->setFieldAttribute('addressee_email', 'default', $addressee_email);
        if ($sub_system == 'RA Events') {
            if ($record_type == '1') {
                $form->setFieldAttribute('addressee_name', 'default', $addressee_name);
                $form->setFieldAttribute('sender_name', 'readonly', false);
                $form->setFieldAttribute('sender_email', 'readonly', false);
            } else {
                $form->setFieldAttribute('sender_email', 'type', 'hidden');
                $members = explode(',', $addressee_email);
                $value = count($members) . ' members';
                $form->setFieldAttribute('addressee_name', 'default', $value);
            }
        } elseif ($sub_system == 'RA Tools') {
            $form->setFieldAttribute('addressee_name', 'default', $addressee_name);
            $form->setFieldAttribute('sender_name', 'readonly', false);
            $form->setFieldAttribute('sender_email', 'readonly', false);
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     * @since   2.0
     */
    protected function loadFormData() {
        $data = Factory::getApplication()->getUserState('com_ra_tools.edit.email.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        if ($data) {


            return $data;
        }

        return array();
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data The form data
     *
     * @return  bool
     *
     * @throws  Exception
     * @since   2.0
     */
    public function save($data) {
        $app = Factory::getApplication();
        $id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('email.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = $app->getIdentity();
//      No need to check access, emails can be sent without logging in

        $table = $this->getTable();

        if (!empty($id)) {
            $table->load($id);
        }
        try {
            if ($table->save($data) === true) {
                $new_email_id = $table->id;
            } else {
                $app->enqueueMessage($table->getError(), 'error');
                return false;
            }
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
//      If attachments were present, they will have been saved to the table as a string
        $attachment_files = array();

//      Prepend the website base and the directory name
        if ($table->attachments !== '') {
//          There may be multiple attachments - convert to an array
            $attachments = explode(',', $table->attachments);
            foreach ($attachments as $attachment) {
                $file_name = JPATH_ROOT . '/images/com_ra_tools/emails/' . $attachment;
                if (file_exists($file_name)) {
                    $attachment_files[] = $file_name;
                } else {
                    $message = 'Unable to find ' . $file_name;
                    Factory::getApplication()->enqueueMessage($message, 'error');
                    return false;
                }
            }
            //           var_dump($attachment_files);
            //           echo '<br>';
            //           var_dump($table->attachments);
            //           die('attach ' . $table->attachments);
        }
        $toolsHelper = new ToolsHelper;

//      There may be multiple addressees - convert to an array
        $addressee = explode(',', $data['addressee_email']);
        if ($data['record_type'] == 1) {
//      Sending email to a Committee Member
//      Send copy to author by adding another entry to the array
            $addressee[] = $data['sender_email'];
        }

        $reply_to = $data['sender_email'];
        $subject = $data['title'];

        if ($data['sub_system'] == 'RA Events') {
            $eventsHelper = new EventsHelper;
            $emailHeader = $eventsHelper->emailHeader($data['ref'], $data['record_type']);
            $body = $emailHeader . $data['body'];
            $bookingHelper = new BookingHelper;
        } else {
            $body = $data['body'];
        }

        //       if (count($to) == 1) {
        foreach ($addressee as $to) {
            if (($data['sub_system'] == 'RA Events') AND ($data['record_type'] == 3)) {
                // Add a block with details of the booking
                $booking_info = $bookingHelper->lookupBooking($data['ref'], $to);
            } else {
                $booking_info = '';
            }
            $toolsHelper->sendEmail($to, $reply_to, $subject, $body . $booking_info, $attachment_files);
            if (JDEBUG) {
                $app->enqueueMessage('Email ' . $subject . ' sent to ' . $to, 'comment');
            }
            //          $addressee_email = Factory::getApplication()->getUserState('com_ra_tools.email.addressee_email');
            //          foreach ($to as $singleUser) {
            //              $toolsHelper->sendEmail($singleUser, $reply_to, $subject, $body, $attachments);
            //
            //          }
        }
        $app->enqueueMessage('Your email on the subject "' . $subject . '" has been sent to ' . $data['addressee_name'] . ', (with a copy to you)', 'comment');
    }

    /**
     * Method to delete data
     *
     * @param   int $pk Item primary key
     *
     * @return  int  The id of the deleted item
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function delete($id) {
        $user = Factory::getApplication()->getIdentity();

        if (empty($id)) {
            $id = (int) $this->getState('email.id');
        }

        if ($id == 0 || $this->getItem($id) == null) {
            throw new \Exception(Text::_('COM_RA_TOOLS_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_ra_tools') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->delete($id) !== true) {
            throw new \Exception(Text::_('JERROR_FAILED'), 501);
        }

        return $id;
    }

    /**
     * Check if data can be saved
     *
     * @return bool
     */
    public function getCanSave() {
        $table = $this->getTable();

        return $table !== false;
    }

}
