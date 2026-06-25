<?php

/**
 * @version    3.2.1
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 27/04/24 CB Add access view
 * 16/12/24 CB use getIdentity, not getUser
 * 08/04/25 CB CurrentUserInterface
 * 03/05/25 CB use toolsHelper->showAccess
 */

namespace Ramblers\Component\Ra_tools\Site\View\Profile;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use \Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView implements CurrentUserInterface {

    protected $user;
    protected $form;
    protected $item;

    public function display($tpl = null) {
        $layout = Factory::getApplication()->input->getCmd('layout', '');
        //       die("Layout $layout<br>");
        $this->user = $this->getCurrentUser();
        //       die('user ' . $this->user->id);
        //       if ($layout == 'register') {
        if ($this->user->id == 0) {
            //return Error::raiseWarning(404, "Please login to gain access to this function");
//            throw new \Exception('Please login to gain access to this function', 404);
            echo '<h4>Please login to gain access to this function</h4>';
            return false;
        }
        //       }
        //       $this->item = $this->get('Item');
//        $this->form = $this->get('Form');
//        $this->canDo = ContentHelper::getActions('com_ra_tools');
        return parent::display($tpl);
    }

}
