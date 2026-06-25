<?php

/**
 * @version    3.4.2
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 13/09/24 Created by component-generator
 * 14/09/24 CB take MIME types and folder from menu parameters
 * 24/09/24 CB comment out deletion of old files
 */

namespace Ramblers\Component\Ra_tools\Site\Table;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Filesystem\File;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table as Table;
use \Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use \Joomla\Database\DatabaseDriver;
use \Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Upload table
 *
 * @since 1.0.4
 */
class UploadTable extends Table implements VersionableTableInterface, TaggableTableInterface {

    use TaggableTableTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;
    protected $target_folder;

    /**
     * Constructor
     *
     * @param   JDatabase  &$db  A database connector object
     */
    public function __construct(DatabaseDriver $db) {
        // target_folder is set up by the View from the menu parameters
        $target_folder = Factory::getApplication()->getUserState('com_ra_tools.target_folder', 'images/com_ra_tools');
        $this->target_folder = ToolsHelper::addSlash(JPATH_ROOT . '/images/' . $target_folder);

        $this->typeAlias = 'com_ra_tools.upload';
        // we don't actually access the database, but have to supply a valid table name
        parent::__construct('#__ra_areas', 'id', $db);
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   1.0.4
     */
    public function getTypeAlias() {
        return $this->typeAlias;
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param   array  $array   Named array
     * @param   mixed  $ignore  Optional array or list of parameters to ignore
     *
     * @return  boolean  True on success.
     *
     * @see     Table:bind
     * @since   1.0.4
     * @throws  \InvalidArgumentException
     */
    public function bind($array, $ignore = '') {
        $input = Factory::getApplication()->input;

        // Support for multi file field: file_name
        if (!empty($array['file_name'])) {
            if (is_array($array['file_name'])) {
                $array['file_name'] = implode(',', $array['file_name']);
            } elseif (strpos($array['file_name'], ',') != false) {
                $array['file_name'] = explode(',', $array['file_name']);
            }
        } else {
            $array['file_name'] = '';
        }

        return parent::bind($array, $ignore);
    }

    /**
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.0.4
     */
    public function store($updateNulls = true) {
        // Validation of file name/size/type will have been carried out in model/validate
        $app = Factory::getApplication();
        $files = $app->input->files->get('jform', array(), 'raw');
        $array = $app->input->get('jform', array(), 'ARRAY');

        $this->file_name = "";

        foreach ($files['file_name'] as $singleFile) {
            if (JDEBUG) {
                $app->enqueueMessage('checking ' . $singleFile['name'], 'info');
            }
            jimport('joomla.filesystem.file');

            // Check if the server found any error.
            $fileError = $singleFile['error'];
            $message = '';

            if ($fileError > 0 && $fileError != 4) {
                switch ($fileError) {
                    case 1:
                        $message = Text::_('File size exceeds allowed by the server');
                        break;
                    case 2:
                        $message = Text::_('File size exceeds allowed by the html form');
                        break;
                    case 3:
                        $message = Text::_('Partial upload error');
                        break;
                }

                if ($message != '') {
                    $app->enqueueMessage($message, 'warning');

                    return false;
                }
            } elseif ($fileError == 4) {
                if (isset($array['file_name'])) {
                    $this->file_name = $array['file_name'];
                }
            } else {
                // Replace any special characters in the filename
                jimport('joomla.filesystem.file');
                $filename = File::stripExt($singleFile['name']);
                $extension = File::getExt($singleFile['name']);
                $working_filename = preg_replace("/[^A-Za-z0-9]/i", "-", $filename);
                if ($working_filename != $filename) {
                    $app->enqueueMessage($filename . ' contained illegal characters', 'warning');
                }
                $working_filename = $filename . '.' . $extension;
                $uploadPath = $this->target_folder . $working_filename;
//                $app->enqueueMessage('Table: copying ' . $singleFile['name'] . ' to ' . $uploadPath, 'info');
                if (file_exists($uploadPath)) {
                    $action = ' overwritten';
                } else {
                    $action = ' uploaded';
                }

                $fileTemp = $singleFile['tmp_name'];

                if (File::upload($fileTemp, $uploadPath)) {
                    if (JDEBUG) {
                        $app->enqueueMessage('Table: copying ' . $singleFile['name'] . ' to ' . $this->target_folder . $working_filename . $exists, 'info');
                    } else {
                        $app->enqueueMessage('File ' . $working_filename . $action, 'info');
                    }
                } else {
                    $app->enqueueMessage('Error moving file', 'warning');
                    return false;
                }

                $this->file_name .= (!empty($this->file_name)) ? "," : "";
                $this->file_name .= $filename;
            }
        }


        return true;
    }

    /**
     * Overloaded check function
     *
     * @return bool
     */
    public function checkkk() {
        $app = Factory::getApplication();
        $files = $app->input->files->get('jform', array(), 'raw');
        $array = $app->input->get('jform', array(), 'ARRAY');

        if (empty($files['file_name'][0])) {
            echo "files ['file_name'][0] is empty<br>";
            $app->enqueueMessage("files [' file_name'][0] is empty", 'error');
            return false;
            $temp = $files;
            $files = array();
            $files['file_name'][] = $temp['file_name'];
        }
        if (1) {
            $app->enqueueMessage('Please select a filee', 'error');
            return false;
        }
        if ($files['file_name'][0]['size'] > 0) {
            $this->file_name = "";

            foreach ($files['file_name'] as $singleFile) {
                if (JDEBUG) {
                    $app->enqueueMessage('checking ' . $singleFile['name'], 'info');
                }
                jimport('joomla.filesystem.file');

                // Check if the server found any error.
                $fileError = $singleFile['error'];
                $message = '';

                if ($fileError > 0 && $fileError != 4) {
                    switch ($fileError) {
                        case 1:
                            $message = Text::_('File size exceeds allowed by the server');
                            break;
                        case 2:
                            $message = Text::_('File size exceeds allowed by the html form');
                            break;
                        case 3:
                            $message = Text::_('Partial upload error');
                            break;
                    }

                    if ($message != '') {
                        $app->enqueueMessage($message, 'warning');

                        return false;
                    }
                } elseif ($fileError == 4) {
                    if (isset($array['file_name'])) {
                        $this->file_name = $array['file_name'];
                    }
                } else {
                    // Replace any special characters in the filename
                    jimport('joomla.filesystem.file');
                    $filename = File::stripExt($singleFile['name']);
                    $extension = File::getExt($singleFile['name']);
                    $filename = preg_replace("/[^A-Za-z0-9]/i", "-", $filename);
                    $filename = $filename . '.' . $extension;
                    $uploadPath = $this->target_folder . $filename;
                    if (JDEBUG) {
                        $app->enqueueMessage('Table: copying ' . $singleFile['name'] . ' to ' . $this->target_folder . $filename, 'info');
                    } else {
                        $app->enqueueMessage('File ' . $filename . ' uploaded', 'info');
                    }
                    $fileTemp = $singleFile['tmp_name'];

                    if (!File::exists($uploadPath)) {
                        if (!File::upload($fileTemp, $uploadPath)) {
                            $app->enqueueMessage('Error moving file', 'warning');
                            return false;
                        }
                    }

                    $this->file_name .= (!empty($this->file_name)) ? "," : "";
                    $this->file_name .= $filename;
                }
            }
        } else {
            $app->enqueueMessage('PPlease select a file' . $files['file_name'][0]['size'], 'error');
            return false;
        }

        return true;
    }

}
