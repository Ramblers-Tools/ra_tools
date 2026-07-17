<?php

/**
 * @version     3.5.3
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 05/06/22 CB remove diagnostic display
 * 01/07/22 CB changes for new version of Ramblers Library
 * 18/02/23 CB printed programmes
 * 27/03/23 CB updated for Joomla 4
 * 20/11/23 CB use table-responsive for navigation table
 * 04/12/23 CB remove diagnostic display
 * 22/01/24 CB use LookaheadWeeks
 * 13/04/24 CB correct spelling of responsive
 * 13/10/25 CB Don't allow restrict by number
 * 19/01/26 CB Changes to implement new radius selection
 * 20/01/26 CB show radius distance as miles
 * 22/01/26 CB show extra_filter
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Uri\Uri;

// Load the component params
//echo "Displaying $this->display_type for $this->group, day=$this->day, intro=$this->intro<br>";
if ($this->intro != '') {
    echo $this->intro . "<br>";
}
// Generate the seven entries at the top of the page, as a table with a single row
// The current day is shown in bold, others as buttons
if ($this->dayswitcher == '1') {
    //echo '<table style="margin-right: auto; margin-left: auto;">';
    echo '<div class="table-responsive">' . PHP_EOL;
    echo '<table>';
    echo "<tr>";
    $week = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    for ($i = 0;
            $i < 7;
            $i++) {
        echo "<td>";
        $weekday = $week[$i];
        $target = 'index.php?option=com_ra_tools&view=programme_day&day=' . $weekday . '&Itemid=' . $this->menu_id;

    //    $link = URI::base() . $target;
        $link = $target;
        if ($this->day == $weekday) {
            echo '<b>' . $this->day . '<b>';
        } else {
            if ($i < 5) {
                $colour = 'p7474';
            } else {
                $colour = 'p0555';
            }
            echo $this->toolsHelper->buildLink($link, $weekday, False, "link-button button-" . $colour);
        }
        echo "</td>";
    }
    echo "</tr>";
    echo "</table>";
    echo '</div>' . PHP_EOL;    // table-responsive
}

// Handle filter type - group or radius
if ($this->filter_type == 'group') {
    $options = new RJsonwalksFeedoptions($this->group);
} else {
    // filter_type = radius
    $item = $this->toolsHelper->getItem('SELECT latitude, longitude from #__ra_groups where code="' . $this->group . '"');
    $options = new RJsonwalksFeedoptions();
    $options->addWalksManagerGroupsInArea($item->latitude,$item->longitude,$this->radius);
}
$objFeed = new RJsonwalksFeed($options);

if ($this->show_cancelled == '0') {
    $objFeed->filterCancelled();
}

if ($this->restrict_walks == "2") {
    $datefrom = new DateTime();
    $weeks = (int) number_format($this->lookahead_weeks, 2);
//  DateInterval is described in https://www.php.net/manual/en/class.dateinterval.php
    $period = new DateInterval('P' . $weeks . 'W');
    $dateto = new DateTime();
    $dateto->add($period);
    $objFeed->filterDateRange($datefrom, $dateto);
} else {
    if ($this->limit > 0) {
        $objFeed->limitNumberWalks($this->limit);
    }
}
$objFeed->filterDayofweek(array($this->day));
/*
  if (!$days == "0") {
  $datefrom = new DateTime(); // set date to today
  $dateto = new DateTime();   // set date to today
  date_add($dateto, date_interval_create_from_date_string($days . "days"));
  $objFeed->filterDateRange($datefrom, $dateto);
  }
 */

switch ($this->display_type) {
    case 'simple':
        $display = new RJsonwalksStdFulldetails();
        break;
    case "map":
        $display = new RJsonwalksLeafletMapmarker();
        break;
    case "list":
        $display = new RJsonwalksStdDisplay();
        $tabOrder = ['List'];
        $display->setTabOrder($tabOrder);
        break;
    case "tabs":
        $display = new RJsonwalksStdDisplay();
        break;
    default:
        $display = new RJsonwalksStdFulldetails();
}

if ($intro != '') {
    echo $intro . "<br>";
}
if ($group_type == "list") {
    $display->displayGroup = true;
}

$display->displayGradesIcon = false;
$display->emailDisplayFormat = 2;      // don't show actual email addresses

$objFeed->Display($display);           // display walks information
if (($this->show_criteria == '2')
        OR (($this->show_criteria == '1') AND ($this->user->id > 0))) {

    if ($this->filter_type == 'radius') {
        echo "Within " . $this->radius . " miles of " . $this->group;
        // Link to view the circle map
        $target = 'index.php?option=com_ra_tools&view=misc&layout=circle&group=' . $this->group . '&radius=' . $this->radius;
        echo $this->toolsHelper->imageButton('I',$target,true);    
    } else {        
        echo "Group=" . $this->group;
    }
    echo ", day=" . $this->day;
    if ($this->restrict_walks == "2") {
        if ($this->lookahead_weeks > "0") {
            echo ', Dates from ' . date_format($datefrom, 'd/m/Y') . ' to ' . date_format($dateto, 'd/m/Y');
        }
    } else {
        if ($this->limit > 0) {
            echo ', Limit=' . $this->limit;
        }
    }
//    if (JDEBUG) {
//        echo "display_type $this->display_type<br>";
//        echo "group $this->group";
//        echo ", restrict_walks $this->restrict_walks";
//        echo ", limit $this->limit";
//        echo ", lookahead_weeks $this->lookahead_weeks";
//        echo ", show_cancelled $this->show_cancelled<br>";
//    }
}

