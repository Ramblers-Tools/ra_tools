<?php

/**
 * @version    3.4.1
 * @package    com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2024 Charlie Bigley
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 * 06/06/23 CB correct links to reports
 * 17/07/23 CB only count walks if Walks Follow has been installed
 * 03/08/23 CB remove diagnostics
 * 03/09/23 CB show location
 * 16/10/23 CB Clusters, Chair email
 * 08/01/24 CB table responsive
 * 10/03/24 CB correction for showing location
 * 21/02/25 CB showArea, delete percentage widths, only show one link to CO
 * 16/04/25 CB show link to organisation feed
 * 18/09/25 CB don't show location
 * 10/10/25 CB correct nation & cluster
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers.css', 'com_ta_tools/css/ramblers.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$toolsHelper = new ToolsHelper;
$jsonHelper = new JsonHelper;

// See if RA Walks has been installed
$com_ra_walks = ComponentHelper::isEnabled('com_ra_walks', true);
$target_reports = 'index.php?option=com_ra_walks&view=reports_area&callback=area_list&area=';

// get the current invokation parameters so that after drilldown, the
// subordinate programs can return to the same state
$current_uri = Uri::getInstance()->toString();
$callback_key = 'com_ra_walks.callback_matrix';
Factory::getApplication()->setUserState($callback_key, $current_uri);

echo '<form action="';
echo Route::_('index.php?option=com_ra_tools&view=area_list');
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
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped" id="mail_lstList">';
// Start actual table of contents
    echo '<thead>';

    echo '<tr>';
    echo '<th scope="col" style="width:1%; min-width:85px" class="text-center">';
    echo HTMLHelper::_('searchtools.sort', 'Code', 'a.code', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" ';
    echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" ';
    echo HTMLHelper::_('searchtools.sort', 'Nation', 'n.name', $listDirn, $listOrder) . '</th>';
    echo '<th scope="col"  class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'Cluster', 'a.cluster', $listDirn, $listOrder) . '</th>';

//    echo '<th></th>';
    echo '<th scope="col" ';
    echo HTMLHelper::_('searchtools.sort', 'Website', 'a.website', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col"  class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'CO link', 'a.co_url', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:5%" class="text-center">';
    echo 'Groups' . '</th>';
    echo '</th>' . PHP_EOL;
    echo '</tr>';
    echo '</thead>' . PHP_EOL;

    $target = 'index.php?option=com_ra_tools&task=area.showArea&code=';
    foreach ($this->items as $i => $item) {
        $group_count = $toolsHelper->getValue('SELECT COUNT(id) FROM #__ra_groups WHERE code LIKE "' . $item->code . '%"');
        echo "<tr>";
        echo "<td>" . $item->code . "</td>";
        echo "<td>" . $toolsHelper->buildLink($target . $item->code, $item->name, False, "");
        echo '</td>';
        echo "<td>" . $item->nation . "</td>";
        echo "<td>" . $item->cluster . "</td>";
        echo '<td>';
        if ($item->website !== "") {
            echo $toolsHelper->buildLink($item->website, $item->website, True, "");
        }
        echo '</td>';

        echo '<td>';
        if (substr($item->website, 8, 19) !== 'www.ramblers.org.uk') {
            if ($item->co_url !== "") {
                echo $toolsHelper->buildLink($item->co_url, $item->co_url, True, "");
            }
        }
        echo '</td>';

        echo '<td class="">';
        echo '<a href="index.php?option=com_ra_tools&view=area&code=' . $item->code . '">';
        echo $group_count . '</a>';
        echo '</td>';

        echo "</tr>";
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td>";
    $target = $current_uri . '&layout=print&tmpl=component';
    echo $toolsHelper->buildLink($target, 'Print');
    echo "</td>";
    echo "<td>";
    // load the pagination.
    echo $this->pagination->getListFooter();
    echo "</td>";

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
echo '<input type="hidden" name="task" value="">';
//echo '<input type="hidden" name="boxchecked" value="0">';
echo HTMLHelper::_('form.token');
echo '</div><!-- row -->' . PHP_EOL;
echo '</div><!-- col-md-12 -->' . PHP_EOL;
echo '</div><!-- j-main-container -->' . PHP_EOL;
echo '</form>';

//
if ($this->nation != '') {
    $nation_id = Factory::getApplication()->input->getCmd('nation', '0');
    $sql = 'SELECT COUNT(*) FROM #__ra_areas WHERE nation_id=' . $nation_id;
    $count = $toolsHelper->getValue($sql);
    echo 'Number of Areas for ' . $this->nation . '=' . $count;
} else {
    if ($this->cluster != '') {
        $sql = 'SELECT COUNT(*) FROM #__ra_areas WHERE cluster="' . $this->cluster . '"';
        $count = $toolsHelper->getValue($sql);
        echo 'Number of Areas for Cluster ' . $this->cluster . '=' . $count;
    }
}
