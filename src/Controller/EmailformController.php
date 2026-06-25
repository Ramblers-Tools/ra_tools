<?php

/**
 * @version    3.3.2
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Email class.
 *
 * @since  2.0
 */
class EmailformController extends FormController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @throws  Exception
     */
    public function edit($key = NULL, $urlVar = NULL) {
        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $this->app->getUserState('com_ra_tools.edit.email.id');
        $editId = $this->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $this->app->setUserState('com_ra_tools.edit.email.id', $editId);

        // Get the model.
        $model = $this->getModel('Emailform', 'Site');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=emailform&layout=edit', false));
    }

    /**
     * Method to save data.
     *
     * @return  void
     *
     * @throws  Exception
     * @since   2.0
     */
    public function save($key = NULL, $urlVar = NULL) {
        // Check for request forgeries.
        $this->checkToken();

        // Initialise variables.
        $model = $this->getModel('Emailform', 'Site');

        // Get the user data.
        $data = $this->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new \Exception($model->getError(), 500);
        }

        // Send an object which can be modified through the plugin event
        $objData = (object) $data;
        $this->app->triggerEvent(
                'onContentNormaliseRequestData',
                array($this->option . '.' . $this->context, $objData, $form)
        );

        $data = (array) $objData;

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $this->app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $jform = $this->input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $this->app->setUserState('com_ra_tools.edit.email.data', $jform);

            // Redirect back to the edit screen.
            $id = (int) $this->app->getUserState('com_ra_tools.edit.email.id');
            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=emailform&layout=edit&id=' . $id, false));

            $this->redirect();
        }
        // Attempt to save the data - this will also dispatch the message(s)
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $this->app->setUserState('com_ra_tools.edit.email.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $this->app->getUserState('com_ra_tools.edit.email.id');
            $this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=emailform&layout=edit&id=' . $id, false));
            $this->redirect();
        }

        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the id from the session.
        $this->app->setUserState('com_ra_tools.edit.email.id', null);

        // Redirect to the list screen.
        if (!empty($return)) {
            $this->setMessage(Text::_('COM_RA_TOOLS_ITEM_SAVED_SUCCESSFULLY'));
        }

        // $url = (empty($item->link) ? 'index.php?option=com_ra_tools&view=emails' : $item->link);
        $url = Factory::getApplication()->getUserState('com_ra_tools.email.callback');
        $this->setRedirect(Route::_($url, false));
        $this->setMessage('controller/save ' . $url);
        $this->setMessage('Thank you, your email has been sent');
//        $this->setRedirect(Route::_($url, false));
        // Flush the data from the session.
        $this->app->setUserState('com_ra_tools.edit.email.data', null);
        $this->app->setUserState('com_ra_tools.email.sub_system', null);
        $this->app->setUserState('com_ra_tools.email.record_type', null);
        $this->app->setUserState('com_ra_tools.email.ref', null);
        $this->app->setUserState('com_ra_tools.email.caption', null);
        $this->app->setUserState('com_ra_tools.email.sender_input', null);
        $this->app->setUserState('com_ra_tools.email.sender_name', null);
        $this->app->setUserState('com_ra_tools.email.addressee_name', null);
        $this->app->setUserState('com_ra_tools.email.addressee_email', null);
        $this->app->setUserState('com_ra_tools.email.callback', null);

        $this->redirect($url, false);
    }

    /**
     * Method to abort current operation
     *
     * @return void
     *
     * @throws Exception
     */
    public function cancel($key = NULL) {
        // Get the current edit id.
        $editId = (int) $this->app->getUserState('com_ra_tools.edit.email.id');

        // Get the model.
        $model = $this->getModel('Emailform', 'Site');

        // Check in the item
        if ($editId) {
            $model->checkin($editId);
        }
        $callback = Factory::getApplication()->getUserState('com_ra_tools.email.callback');
        $this->setRedirect(Route::_($callback, false));
//        $menu = Factory::getApplication()->getMenu();
//        $item = $menu->getActive();
//        die('controller ' . $callback . ', menu=' . $item->id);
//        $url = (empty($item->link) ? $callback : $item->link);
//        $this->setRedirect(Route::_($url, false));
    }

}
