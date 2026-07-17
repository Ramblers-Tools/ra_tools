<?php

/**
 * @version    2.1.0
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2025 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 20/07/25 CB use CurrentUserInterface
 */

namespace Ramblers\Component\Ra_tools\Administrator\View\Profile;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\User\CurrentUserInterface;

class HtmlView extends BaseHtmlView implements CurrentUserInterface {

    protected $form;
    protected $item;
    protected $user;

    public function display($tpl = null) {
        //       $this->form = $this->get('Form');
        //       $this->item = $this->get('Item');
        $this->user = $this->getCurrentUser();

        // required record is specified by $this->item->id

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar() {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolbarHelper::title($isNew ? 'New Profile' : 'Edit Profile', 'address foo');

        ToolbarHelper::apply('profile.apply');
        ToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
    }

}
