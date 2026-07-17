<?php
/**
 * @version    3.5.5
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2026 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The Clusters API controller
 *
 * @since  1.0.0
 */
class ClustersController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $contentType = 'clusters';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $default_view = 'clusters';

    /**
     * Authorizes the request (override for temporary public access)
     *
     * @return bool
     * @since 1.0.0
     */
    protected function authorizeRequest()
    {
        // TEMPORARY: Allow public API access for testing
        // TODO: Remove this bypass once token authentication is working
        return true;
    }
}
