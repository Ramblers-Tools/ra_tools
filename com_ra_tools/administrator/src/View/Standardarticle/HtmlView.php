<?php
/**
 * @version    3.7.4
 * @package    com_ra_tools
 * @author     GitHub Copilot
 * @copyright  2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Ramblers\Component\Ra_tools\Administrator\View\Standardarticle;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;

/**
 * View to display a single standard article.
 *
 * @since  3.7.4
 */
class HtmlView extends BaseHtmlView
{
    protected $app;
    protected $item;
    protected $toolsHelper;
    protected $site;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches for the template in the layout directory
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->toolsHelper = new ToolsHelper;
        $this->app = Factory::getApplication();
        $id  = $this->app->input->getInt('id');

        $params = ComponentHelper::getParams('com_ra_tools');
        $site_id = $params->get('site_id');

        if (empty($site_id) || empty($id)) {
            $this->app->enqueueMessage('Site ID or Article ID not specified.', 'error');
            return;
        }

        $this->toolsHelper = new ToolsHelper;
        $sql = 'SELECT * FROM #__ra_api_sites WHERE id=' . (int) $site_id;
        $this->site = $this->toolsHelper->getItem($sql);

        if (!$this->site) {
            $this->app->enqueueMessage('API Site not found.', 'error');
            return;
        }

        $jsonHelper = new JsonHelper();
        $endpoint = '/api/index.php/v1/content/articles/' . $id;
        $item = $jsonHelper->getRemoteData($site_id, $endpoint);

        if (!$item) {
            $messages = $jsonHelper->getMessages();
            foreach ($messages as $message) {
                $this->app->enqueueMessage($message, 'error');
            }
            $this->app->enqueueMessage('Error fetching remote article', 'error');
            return;
        }

        $this->item = $item;

        $this->document->setTitle('Standard Article');

        parent::display($tpl);
    }
}
