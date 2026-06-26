<?php

/**
 * @version     3.6.0
 * @package     com_ra_tools
 * @copyright   Copyleft (C) 2021
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk

 * 13/04/26 CB created
 */
// No direct access
use Ramblers\Component\Ra_mailman\Site\Helpers\Mailhelper   ;

\defined('_JEXEC') or die;

$mailHelper = new MailHelper;
// logic for building the menu is embedded in the MailMan project
// so it can change without a new version of Ra_tools
echo $mailHelper->buildMenu();
