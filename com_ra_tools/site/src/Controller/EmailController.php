<?php

/**
 * @version    3.2.3
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;

/**
 * Email class.
 *
 * @since  1.6.0
 */
class EmailController extends BaseController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @throws  Exception
     */
    public function edit() {
        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $this->app->getUserState('com_ra_tools.edit.email.id');
        $editId = $this->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $this->app->setUserState('com_ra_tools.edit.email.id', $editId);

        // Get the model.
        $model = $this->getModel('Email', 'Site');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=emailform&layout=edit', false));
    }

    /**
     * Method to save data
     *
     * @return    void
     *
     * @throws  Exception
     * @since   2.0
     */
    public function publish() {
        // Checking if the user can remove object
        $user = $this->app->getIdentity();

        if ($user->authorise('core.edit', 'com_ra_tools') || $user->authorise('core.edit.state', 'com_ra_tools')) {
            $model = $this->getModel('Email', 'Site');

            // Get the user data.
            $id = $this->input->getInt('id');
            $state = $this->input->getInt('state');

            // Attempt to save the data.
            $return = $model->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $this->app->setUserState('com_ra_tools.edit.email.id', null);

            // Flush the data from the session.
            $this->app->setUserState('com_ra_tools.edit.email.data', null);

            // Redirect to the list screen.
            $this->setMessage(Text::_('COM_RA_TOOLS_ITEM_SAVED_SUCCESSFULLY'));
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();

            if (!$item) {
                // If there isn't any menu item active, redirect to list view
                $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=emails', false));
            } else {
                $this->setRedirect(Route::_('index.php?Itemid=' . $item->id, false));
            }
        } else {
            throw new \Exception(500);
        }
    }

    /**
     * Check in record
     *
     * @return  boolean  True on success
     *
     * @since   2.0
     */
    public function checkin() {
        // Check for request forgeries.
        $this->checkToken('GET');

        $id = $this->input->getInt('id', 0);
        $model = $this->getModel();
        $item = $model->getItem($id);

        // Checking if the user can remove object
        $user = $this->app->getIdentity();

        if ($user->authorise('core.manage', 'com_ra_tools') || $item->checked_out == $user->id) {

            $return = $model->checkin($id);

            if ($return === false) {
                // Checkin failed.
                $message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=email' . '&id=' . $id, false), $message, 'error');
                return false;
            } else {
                // Checkin succeeded.
                $message = Text::_('COM_RA_TOOLS_CHECKEDIN_SUCCESSFULLY');
                $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=email' . '&id=' . $id, false), $message);
                return true;
            }
        } else {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Remove data
     *
     * @return void
     *
     * @throws Exception
     */
    public function remove() {
        // Checking if the user can remove object
        $user = $this->app->getIdentity();

        if ($user->authorise('core.delete', 'com_ra_tools')) {
            $model = $this->getModel('Email', 'Site');

            // Get the user data.
            $id = $this->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $model->delete($id);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return) {
                    $model->checkin($return);
                }

                $this->app->setUserState('com_ra_tools.edit.email.id', null);
                $this->app->setUserState('com_ra_tools.edit.email.data', null);

                $this->app->enqueueMessage(Text::_('COM_RA_TOOLS_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $this->app->redirect(Route::_('index.php?option=com_ra_tools&view=emails', false));
            }

            // Redirect to the list screen.
            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(Route::_($item->link, false));
        } else {
            throw new \Exception(500);
        }
    }

    public function test() {
        $objHelper = new ToolsHelper;
        $to = 'hyperbigley@gmail.com';
        $reply_to = 'rodeheath.ypcc@gmail.com';
        $title = 'Test email';
        $body = 'Test email send from ' . __FILE__;
        $files = '1000008358.jpg,20180729-4.jpg';
        $attach_array = explode(',', $files);
        foreach ($attach_array as $file) {
            $working_file = JPATH_ROOT . '/images/com_ra_tools/' . $file;
            if (file_exists($working_file)) {
                $attachments[] = $working_file;
            }
        }
        $result = $objHelper->sendEmail($to, $reply_to, $title, $body, $attachments);

        if ($result) {
            echo 'Sent message to ' . $to;
            Factory::getApplication()->enqueueMessage('Sent message to ' . $to, 'info');
        } else {
            echo 'Unable to send message to ' . $to;
            Factory::getApplication()->enqueueMessage('Unable to send message to ' . $to, 'error');
        }
        //       $this->setRedirect(Route::_('index.php', false));
        //       $this->redirect();
    }

}
