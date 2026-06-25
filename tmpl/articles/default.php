<?php

/**
 * @version    5.0.3
 * @component  com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 29/02/24 CB Created
 * 02/09/24 CB show modified_by
 * 06/09/24 CB include created_by_alias
 * 07/09/24 CB show page intro
 * 09/09/24 CB Show total number of Articles
 * 10/09/24 CB Show Year
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseFactory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers.css', 'com_ta_tools/css/ramblers.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$objHelper = new ToolsHelper;
$target = 'index.php?option=com_ra_tools&task=article.showArticle&Itemid=' . $this->menu_id . '&id=';
//$target =  $objHelper->addSlash($this->params->get('remote_url')) . 'index.php?option=com_content&view=article&id=';
if ($this->intro != '') {
    echo $this->intro . "<br>";
}
// Count number of Articles
// Find selection criteria set up by the model
$criteria = Factory::getApplication()->getUserState('com_ra_tools.articles_criteria');

$sql = 'SELECT COUNT(a.id) FROM #__content AS a ';
$sql .= 'LEFT JOIN #__users AS created ON created.id = a.created_by ';
$sql .= 'LEFT JOIN #__users AS updated ON updated.id = a.modified_by ';
$sql .= $criteria;
if ($this->params->get('site') == 'remote') {
    $config = array();
    $config['driver'] = 'mysqli';                   // Database driver name
    $config['host'] = $this->params->get('server');       // Database host name
    $config['database'] = $this->params->get('database'); // Database name
    $config['user'] = $this->params->get('user');         // User for database authentication
    $config['password'] = $this->params->get('password'); // Password for database authentication
    $config['prefix'] = $this->params->get('prefix');     // Database prefix
// external database connection
    $dbFactory = new DatabaseFactory();
    $dbDriver = $dbFactory->getDriver('mysqli', $config);
    $query = $dbDriver->getQuery(true);
    $dbDriver->setQuery($sql);
    $count = $dbDriver->loadResult();
} else {
    $count = $objHelper->getValue($sql);
}
// See if a particular year has been specified
$pos = strpos($criteria, 'a.created >');
if ($pos == 0) {
    $year = '';
} else {
    $year = substr($criteria, $pos + 13, 4);
}
echo '<form action="';
echo Route::_('index.php?option=com_ra_tools&view=articles&Itemid=' . $this->menu_id);
echo '" method="post" name="adminForm" id="adminForm">';
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div id="j-main-container" class="j-main-container">';
echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
if (empty($this->items)) {
    echo '<div class="alert alert-info">';
    echo '<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only">' . Text::_('INFO') . '</span>';
    echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
    echo '</div>';
} else {
    echo '<table class="table" id="ra_areasList">';
// Start actual table of contents
    echo '<thead>';

    echo '<tr>';
    echo '<th scope="col" style="width:10%" class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'Created', 'a.created', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Created by', 'created.name', $listDirn, $listOrder) . '</th>';
    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Author', 'a.created_by_alias', $listDirn, $listOrder) . '</th>';
    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Modified', 'a.modified', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Modified by', 'updated.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:1%; min-width:85px" class="text-center">';
    echo HTMLHelper::_('searchtools.sort', 'ID', 'a.id', $listDirn, $listOrder) . '</th>';

    echo '</tr>';
    echo '</thead>' . PHP_EOL;

    foreach ($this->items as $i => $item) {
        echo '<tr>';
        echo '<td>' . HTMLHelper::_('date', $item->created, 'd M Y H:i') . '</td>';

        echo '<td>';
        echo $objHelper->buildLink($target . $item->id, $item->title, false);
        echo '</td>';

        echo '<td>' . $item->created_by . '</td>';
        echo '<td>' . $item->created_by_alias . '</td>';
        echo '<td>' . HTMLHelper::_('date', $item->modified, 'd M Y H:i') . '</td>';

        if ($item->username != $item->modified_by) {
            echo '<td>' . $item->modified_by . '</td>';
        } else {
            echo '<td></td>';
        }
        echo '<td>' . $item->id . '</td>';
        echo '</tr>';
    }

    echo '<tr>';
    echo '<td>';
    $target = Uri::getInstance()->toString() . '&layout=print&tmpl=component';
    echo $objHelper->buildLink($target, 'Print');
    echo '</td>';
    echo '<td>';
    // load the pagination.
    echo $this->pagination->getListFooter();
    echo '</td>';

    echo '</tbody>';
    echo '</table>';
}
echo '<input type="hidden" name="task" value="">';
echo HTMLHelper::_('form.token');
echo '</div><!-- row -->' . PHP_EOL;
echo '</div><!-- col-md-12 -->' . PHP_EOL;
echo '</div><!-- j-main-container -->' . PHP_EOL;
echo '</form>';
if ($year != '') {
    echo 'Year=' . $year . ', ';
}
echo ' Total number of Articles=' . $count;

