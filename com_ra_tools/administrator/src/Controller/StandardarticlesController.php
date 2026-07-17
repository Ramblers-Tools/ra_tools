<?php
/**
 * @version    3.7.4
 * @package    com_ra_tools
 * @author     GitHub Copilot
 * @copyright  2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Ramblers\Component\Ra_tools\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;

/**
 * Standardarticles Controller
 *
 * @since  3.7.4
 */
class StandardarticlesController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'standardarticles';

    protected $db;

    public function cancel($key = null, $urlVar = null) {
        $this->setRedirect('index.php?option=com_ra_tools&view=dashboard');
    }

    public function refresh()
    {

        $input = $this->app->input;
        $remote_id    = $input->getInt('remote_id');
        $local_id = $input->getInt('local_id');
        $params = ComponentHelper::getParams('com_ra_tools');
        $api_site_id = $params->get('site_id');
    // Get the remote data for the article
    
        $endpoint = $params->get('api_endpoint');
        $endpoint .= '/api/index.php/v1/content/articles/'  . $remote_id;   
        $jsonHelper = new JsonHelper();
        $item = $jsonHelper->getRemoteData($api_site_id, $endpoint );

        if (!$item) {
            $this->app->enqueueMessage('Failed to retrieve remote article.', 'error');
            $messages = $jsonHelper->getMessages();
            foreach($messages as $message){
                $this->app->enqueueMessage($message, 'error');
            }
            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=standardarticles', false));
            return;
        }

        $data = [
            'id' => 0, // Let the save method determine if it's an insert or update
            'title' => $item->title,
            'articletext' => $item->text,
            'catid' => $params->get('local_category_id'),
            'state' => 1, // Published
            'language' => '*',
        ];
        // Get the Articles  table
        $table = \Joomla\CMS\Table\Table::getInstance('Content', 'JTable', ['dbo' => $this->db]);

        if ($local_id > 0) {
            $table->load($local_id);

        }
        $table->bind($data);
        if (!$table->check()) {
            $this->app->enqueueMessage('Error checking article data: ' . $table->getError(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=standardarticles', false));
            return;
        }
        if ($table->store()) {
            $this->app->enqueueMessage('Article saved successfully.');
        } else {
            $this->app->enqueueMessage('Error saving article: ' . $table->getError(), 'error');
        }
        $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=standardarticles', false));
    }
}
