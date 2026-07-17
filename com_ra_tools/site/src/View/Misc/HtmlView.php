<?php

/**
 * @version     3.5.1
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 17/07/23 CB remove diagnostics
 * 08/01/24 CB canDo
 * 05/02/24 CB prepare document
 * 17/09/24 CB set up $this-CanDo
 * 16/12/24 CB use getIdentity, not getUser
 * 23/12/24 CB reverted to getUser
 * 02/01/25 CB allow expansion of sub-folders
 * 19/01/26 CB Changes to implement new radius selection
 */

namespace Ramblers\Component\Ra_tools\Site\View\Misc;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    public $canDo;
    public $app;
    protected $params;
    protected $menu_id;
    protected $menu_params;
    protected $objHelper;
    protected $state;
    protected $title;
    protected $user;
    protected$toolsHelper;
    // for folderlist
    protected $root; // default, root directory before sub-folders
    protected $level;
    protected $working_folder;

    public function display($tpl = null) {
        $this->app = Factory::getApplication();
        //$this->user = Factory::getApplication()->loadIdentity();
        $this->user = Factory::getUser();
        $this->menu_id = $this->app->input->getInt('Itemid', '0');
        $this->level = $this->app->input->getCmd('level', '1');
        if (0) { // JDEBUG) {
            echo 'level: ';
            var_dump($this->level);
            echo '<br>';
        }
        $this->params = $this->get('State')->get('params');
//        $this->params = $this->app->getParams();
//        var_dump($this->params);
//        echo '<br>end of params from app<br>';
        $menu = $this->app->getMenu()->getActive();
        if (is_null($menu)) {
            echo 'Menu params are null<br>';
        } else {
            $this->menu_params = $menu->getParams();
        }
        $target_folder = $this->menu_params->get('target_folder', '');
        $this->root = ToolsHelper::addSlash('images/' . $target_folder);
        if ($this->level == '1') {
            $this->working_folder = $this->root;
//            $this->resetGlobals();
        } else {
            $this->working_folder = $this->app->getUserState('com_ra_tools.docs' . $this->level);
        }
        $this->toolsHelper = new ToolsHelper;
        $this->canDo = $this->toolsHelper->getActions('com_ra_tools');

        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');
        $this->prepareDocument();
        return parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return void
     *
     * @throws Exception
     */
    protected function prepareDocument() {
        $menus = $this->app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_RA_EVENTS_DEFAULT_PAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ($this->app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
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

    function resetGlobals() {
        for ($i = 1; $i < 3; $i++) {
            for ($j = 1; $j < 11; $j++) {
                $context = 'com_ra_tools.docs' . $i . '_' . $j;
                echo 'resetting ' . $context . '<br>';
                $this->app->setUserState($context, null);
            }
        }
    }

}

