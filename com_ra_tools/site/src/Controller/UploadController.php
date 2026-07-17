<?php

/**
 * @version    3.4.2
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 13/09/24 Created by component-generator
 * 14/09/24 CB return to input form
 * 18/09/24 CB delete function remove, change function cancel to return to index.php
 * 19/09/24 CB renamed from Uploadform
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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Upload class.
 *
 * @since  1.0.4
 */
class UploadController extends FormController {

    /**
     * Method to abort current operation
     *
     * @return void
     *
     * @throws Exception
     */
    public function cancel($key = NULL) {
        $url = 'index.php';
        $this->setRedirect($url);
    }

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return  void
     *
     * @since   1.0.4
     *
     * @throws  Exception
     */
    public function edit($key = NULL, $urlVar = NULL) {
        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $this->app->getUserState('com_ra_tools.edit.upload.id');
        $editId = $this->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $this->app->setUserState('com_ra_tools.edit.upload.id', $editId);

        // Get the model.
        $model = $this->getModel('Upload', 'Site');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=upload&layout=edit', false));
    }

    /**
     * Method to save data.
     *
     * @return  void
     *
     * @throws  Exception
     * @since   1.0.4
     */
    public function save($key = NULL, $urlVar = NULL) {
        // Check for request forgeries.
        $this->checkToken();

        // Initialise variables.
        $model = $this->getModel('Upload', 'Site');

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

        $menu_id = $this->app->getUserState('com_ra_tools.upload_menu', '0');
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
            $this->app->setUserState('com_ra_tools.edit.upload.data', $jform);

            // Redirect back to the edit screen.

            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=upload&Itemid=' . $menu_id, false));

            $this->redirect();
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $this->app->setUserState('com_ra_tools.edit.upload.data', $data);

            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=upload&layout=edit&Itemid=' . $menu_id, false));
            $this->redirect();
        }


        // Clear the profile id from the session.
        $this->app->setUserState('com_ra_tools.edit.upload.id', null);

        // Redirect to the input screen.

        $this->setRedirect(route::_('index.php?option=com_ra_tools&view=upload' . '&Itemid=' . $menu_id, false));

        // Flush the data from the session.
        $this->app->setUserState('com_ra_tools.edit.upload.data', null);

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $data);
    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = array()) {

    }

    function unlink() {
        // Ensure user has permission
        $canDo = ToolsHelper::getActions('com_ra_tools');
        if ($canDo->get('core.delete')) {
            $app = Factory::getApplication();

            $file = $app->input->getCmd('file');
            $menu_id = $app->input->getCmd('menu_id', '0');
            // The folder name cannot be passed via the input stack, since directory separators are filtered out
            $target_folder = Factory::getApplication()->getUserState('com_ra_tools.target_folder', 'images/com_ra_tools');
            $base = 'images/' . $target_folder . '/';
            if ($file != '') {
                $filename = $base . $file;
                if (is_dir($filename)) {
                    Factory::getApplication()->enqueueMessage($file . ' is a directory', 'warning');
                } else {
                    if (file_exists($filename)) {
                        unlink($filename);
                        Factory::getApplication()->enqueueMessage($file . ' deleted', 'info');
                    } else {
                        Factory::getApplication()->enqueueMessage('Could not find ' . $filename, 'info');
                    }
                }
            }
        }
        $target = 'index.php?option=com_ra_tools&view=upload&Itemid=' . $menu_id;
        $this->setRedirect(route::_($target, false));
    }

}
