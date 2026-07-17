<?php

/**
 * @version    CVS: 3.0.0
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Ra_tools master display controller.
 *
 * @since  3.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	protected $default_view = 'dashboard';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  BaseController|boolean  This object to support chaining.
	 *
	 * @since   3.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
