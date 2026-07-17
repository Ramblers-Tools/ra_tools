<?php

/**
 * @version    3.3.2
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 11/07/25 CB show caption
 */

namespace Ramblers\Component\Ra_tools\Site\View\Emailform;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\User\CurrentUserInterface;

/**
 * View class for a list of Ra_tools.
 *
 * @since  2.0
 */
class HtmlView extends BaseHtmlView implements CurrentUserInterface {

    protected $caption;
    protected $state;
    protected $item;
    protected $form;
    protected $menu_id;
    protected $params;
    protected $canSave;
    protected $user;

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @throws Exception
     */
    public function display($tpl = null) {
        $app = Factory::getApplication();
        $this->user = $this->getCurrentUser();
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->params = $app->getParams('com_ra_tools');
        $this->canSave = $this->get('CanSave');
        $this->form = $this->get('Form');
        //       echo 'View after form';
        $this->menu_id = $app->input->getInt('Itemid', '0');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors));
        }

        $this->caption = $app->getUserState('com_ra_tools.email.caption');

        $this->_prepareDocument();
//        die('View before display');
        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return void
     *
     * @throws Exception
     */
    protected function _prepareDocument() {
        $app = Factory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_RA_TOOLS_DEFAULT_PAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

}
