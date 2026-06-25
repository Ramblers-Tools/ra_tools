<?php

/**
 * @version     5.1.1
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 16/10/23 CB Created
 * 18/10/23 CB include Richard Sharpe
 * 23/11/23 CB correct use of AssetManager
 * 29/09/24 CB replace JPATH_SITE by JPATH_ROOT
 * 18/12/24 CB set up Nations
 * 14/03/26 CB get Clusters usingJsonHelper
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;

class ClustersController extends FormController {

    protected $objApp;
    protected $objHelper;

    public function __construct() {
        parent::__construct();
        $this->objHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        // Import CSS
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    function checkColumn($table, $column, $mode, $details) {
//  $mode = A: add the field
//  $mode = U: update the field
        $objHelper = new ToolsHelper;
        $config = Factory::getConfig();
        $database = $config->get('db');
        $this->dbPrefix = $config->get('dbprefix');

        $table_name = $this->dbPrefix . $table;
        $sql = 'SELECT COUNT(COLUMN_NAME) ';
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $this->dbPrefix . $table . "' ";
        $sql .= "AND COLUMN_NAME='" . $column . "'";
//    echo "$sql<br>";

        $count = $objHelper->getValue($sql);
        echo 'Seeking ' . $table_name . '/' . $column . ', count=' . $count . "<br>";
        if (($mode == 'A') AND ($count == 1)) {
            return true;
        }
        if (($mode == 'U') AND ($count == 0)) {
            echo 'Field ' . $column . ' not found in ' . $table_name . '<br>';
            return false;
        }

        $sql = 'ALTER TABLE ' . $table_name . ' ' . $details;
        echo "$sql<br>";
        $response = $objHelper->executeCommand($sql);
        if ($response) {
            echo 'Success';
        } else {
            echo 'Failure';
        }
        echo ' for ' . $table_name . '<br>';
        return $count;
    }

    function checkSchema() {
        $details = '( `code` VARCHAR(3) NOT NULL DEFAULT "",'
                . '`name` VARCHAR(20) NOT NULL,'
                . '`contact_id` INT NULL,'
                . '  PRIMARY KEY (`code`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $details2 = 'INSERT INTO  `#__ra_clusters` (code,name) values ';
        $details2 .= "('ME','Midlands and East'),";
        $details2 .= "('N','North and North West'),";
        $details2 .= "('SE','South East'),";
        $details2 .= "('SSW','South and South West')";
        $this->checkTable('ra_clusters', $details, '');
        $this->checkColumn('ra_areas', 'cluster', 'A', "ADD cluster VARCHAR(3) NOT NULL DEFAULT '' AFTER co_url; ");
        $this->checkColumn('ra_areas', 'chair_id', 'A', "ADD chair_id INT NOT NULL DEFAULT '0' AFTER cluster; ");
        // $details2
    }

    function checkTable($table, $details, $details2 = '') {
        $objHelper = new ToolsHelper;

        $config = Factory::getConfig();
        $database = $config->get('db');
        $dbPrefix = $config->get('dbprefix');

        $table_name = $dbPrefix . $table;
        $sql = 'SELECT COUNT(COLUMN_NAME) ';
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $table_name . "' ";
        echo "$sql<br>";

        $count = $objHelper->getValue($sql);
        echo 'Seeking ' . $table_name . ', count=' . $count . "<br>";
        if ($count > 0) {
            return $count;
        }
        $sql = 'CREATE TABLE ' . $table_name . ' ' . $details;
        echo "$sql<br>";
        //       return;
        $response = $objHelper->executeCommand($sql);
        if ($response) {
            echo 'Table created OK';
        } else {
            echo 'Failure';
            return false;
        }
        if ($details2 != '') {
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $details2;
            $response = $objHelper->executeCommand($sql);
            if ($response) {
                echo 'Table altered OK';
            } else {
                echo 'Failure';
                return false;
            }
        }
    }

    public function docs_duff() {
        $tool = JPATH_LIBRARIES . '/ramblers/directory/list.php';
        //       echo "$tool<br>";
        if (file_exists($tool)) {
            die($tool . ' exists');
            require_once($tool);
            $dir = new RDirectoryList(array(".pdf", ".doc", ".docx", ".odt", ".zip", "png"));

            $dir->listItems("images/com_ra_tools/packs");
        }
        echo "No file found for $tool<br>";
        die(__FILE__);
    }

    public function docs() {
        echo '<h2>Cluster packs</h2>';
        $objHelper = new ToolsHelper;
        $folder = '/images/com_ra_tools/packs';
        $fileTypes = array(".pdf", ".doc", ".docx", ".odt", ".zip", "png");
        $this->names = array();
        if (!file_exists(JPATH_ROOT . $folder)) {
            $text = "Folder does not exist: " . JPATH_ROOT . $folder . ". Unable to list contents";

            // Add a message to the message queue
            Factory::getApplication()->enqueueMessage($text, 'error');
            echo "<b>Not able to list contents of folder<b>";
            return;
        }

        if ($handle = opendir(JPATH_ROOT . $folder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($entry)) {

                    } else {
                        $names[] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        if ($names) {
//            echo count($names) . ' files in ' . $folder . '<br>';
            // Remove trailing slash
            $base = substr(uri::base(), 0, -1) . $folder;
//            echo 'Base = ' . $base . '<br>';
        } else {
            echo 'No files in ' . $folder;
            return 0;
        }
//        var_dump($names);
        natcasesort($names);

        //       if ($sort == self::DESC) {
        //           $this->names = array_reverse($this->names);
        //       }
        echo "<ul>";
        foreach ($names as $value) {
//            echo 'value ' . $value . '<br>';
//            echo 'file ' . $base . '/' . $value . '<br>';
            echo '<li>' . $objHelper->buildLink($base . '/' . $value, $value, true) . "</li>\n";
        }
        echo "</ul>";
    }

    public function email($email = 'charlie@bigley.me.uk') {
        $url = 'https://dns.google/resolve?type=MX&name=';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $email);
        curl_setopt($ch, CURLOPT_HEADER, false); // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // allow xx seconds for timeout
//            curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_wf"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . "\n");
            print('Http return: ' . $httpCode . "\n");
            echo 'Access failed';

            return;
        }

        $temp = json_decode($data);
        var_dump($temp);
        $summary = $temp->summary;
    }

    public function init() {
        echo '<h3>Updating Areas and Nations</h3>';
        $objHelper = new ToolsHelper;
        $count = $objHelper->getValue('SELECT COUNT(code) FROM #__ra_clusters');
        if ($count == 0) {
            $details2 = 'INSERT INTO  `#__ra_clusters` (code,name) values ';
            $details2 .= "('ME','Midlands and East'),";
            $details2 .= "('N','North and North West'),";
            $details2 .= "('SE','South East'),";
            $details2 .= "('SSW','South and South West')";
            $objHelper->executeCommand($details2);
//$sql = "ALTER TABLE #__ra_areas ADD cluster VARCHAR(3) NOT NULL DEFAULT '' AFTER co_url;";
//$objHelper->executeCommand($sql);
//$sql = "ALTER TABLE #__ra_areas ADD chair_id INT NULL AFTER cluster;";
//        $objHelper->executeCommand($sql);
// $sql = 'UPDATE `#__ra_areas` SET cluster = "" WHERE code in ("BF","CH","DE",

            $sql = 'UPDATE `#__ra_areas` SET cluster = "ME" WHERE code in ("LI","NP","NR","NE","SS","NS","WO")';
            $objHelper->executeCommand($sql);
            $sql = 'UPDATE `#__ra_areas` SET cluster = "SE" WHERE code in ("BU","CB","ES","WX","KT","IL","IW","NO","OX","SK","SR","SX")';
            $objHelper->executeCommand($sql);
            $sql = 'UPDATE `#__ra_areas` SET cluster = "SSW" WHERE code in ("AV","BK","CL","DN","DT","GR","IW","OX","SO","WE")';
            $objHelper->executeCommand($sql);
            $sql = 'UPDATE `#__ra_areas` SET cluster = "N" WHERE code in ("ER","MR","LD","LL","ML","MC","LN","NY","NN","SD","WR")'; //,"")';
            $objHelper->executeCommand($sql);

            $sql = 'UPDATE `#__ra_areas` SET cluster = "SC" WHERE nation_id=2';
            $objHelper->executeCommand($sql);
            $sql = 'UPDATE `#__ra_areas` SET cluster = "WA" WHERE nation_id=3';
            $objHelper->executeCommand($sql);
        } else {
            echo $count . ' Clusters present<br>';
        }
        $count = $objHelper->getValue('SELECT COUNT(code) FROM #__ra_nations');
        if ($count == 0) {
            $details2 = 'INSERT INTO  `#__ra_nations` (code,name) values ';
            $details2 .= "('EN','England'),";
            $details2 .= "('SC','Scotland'),";
            $details2 .= "('WA','Wales')";
            $objHelper->executeCommand($details2);
        } else {
            echo $count . ' Nations present<br>';
        }
        $target = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($target);
    }

    public function show() {
        $menu_id = Factory::getApplication()->input->getInt('menu_id', '0');
        echo '<h2>Clusters</h2>';
        $objHelper = new ToolsHelper;
        /*
          $details2 = 'INSERT INTO  `#__ra_clusters` (code,name) values ';
          //$details2 .= "('ME','Midlande and East')";
          $details2 .= "('N','North and North West'),";
          $details2 .= "('SE','South East'),";
          $details2 .= "('SW','South and South West')";
          $objHelper->executeCmd($details2);
         */
        $sql = 'SELECT c.code, c.name as `Cluster`, c.contact_id, con.name ';
        $sql .= 'from #__ra_clusters AS c ';
        $sql .= 'LEFT JOIN #__contact_details AS con ON con.id = c.contact_id ';
        $sql .= 'ORDER BY code ';
        $target_contact = 'index.php?option=com_contact&view=contact&id=';
        $tot_count = 0;

        $objTable = new ToolsTable();
        $header = 'Code,Name,Areas,';
        $objTable->add_header("Code,Area,Contact,Areas,");
        $target = 'index.php?option=com_ra_tools&task=clusters.showAreas&Itemid=' . $menu_id . '&code=';
        $print = '&layout=print&tmpl=component&Itemid=' . $menu_id;

        $rows = $objHelper->getRows($sql);

        foreach ($rows as $row) {
            $objTable->add_item($row->code);
            $objTable->add_item($row->Cluster);
            //           $objTable->add_item($row->contact_id);
            if ($row->contact_id == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($row->name . ' ' . $objHelper->imageButton('E', $target_contact . $row->contact_id, true));
            }
            $area_count = $objHelper->getValue('SELECT COUNT(id) FROM #__ra_areas WHERE cluster ="' . $row->code . '"');
            $objTable->add_item($objHelper->buildLink($target . $row->code, $area_count));
            $objTable->add_item($objHelper->buildLink($target . $row->code . $print, 'Print'));
            $objTable->generate_line();
            $tot_count = $tot_count + $area_count;
        }

        $sql = 'SELECT COUNT(id) FROM `#__ra_areas` WHERE cluster =""';
        $nk = $objHelper->getValue($sql);
        if ($nk > 0) {
            $objTable->add_item('NK');
            $objTable->add_item('Not known');
            $objTable->add_item('');
            $objTable->add_item($objHelper->buildLink($target . 'nk', 'List Areas'));
            $objTable->add_item($objHelper->buildLink($target . 'nk' . $print, 'Print'));
            $objTable->generate_line();
        }

        $objTable->generate_table();
        echo $tot_count . ' Areas found<br> ';

        $sql = 'SELECT id,name FROM `#__contact_details` WHERE con_position ="Scotland"';
        $item = $objHelper->getItem($sql);
        if ($item->id > 0) {
            echo 'Contact for Scotland ( ' . $item->name . ') ';
            echo $objHelper->imageButton('E', $target_contact . $id, true) . '<br>';
        }

        $sql = 'SELECT id,name FROM `#__contact_details` WHERE con_position ="Wales"';
        $item = $objHelper->getItem($sql);
        if ($item->id > 0) {
            echo 'Contact for Wales ( ' . $item->name . ') ';
            echo $objHelper->imageButton('E', $target_contact . $id, true) . '<br>';
        }

        $sql = 'SELECT id FROM `#__contact_details` WHERE name ="Richard Sharp"';
        $id = $objHelper->getValue($sql);
        if ($id > 0) {
            echo 'Manager of Regional Engagement Team (Richard Sharp) ';
            echo $objHelper->imageButton('E', $target_contact . $id, true) . '<br>';
        }
    }

    public function showAreas() {
        $code = Factory::getApplication()->input->getCmd('code', '');
        $menu_id = Factory::getApplication()->input->getInt('menu_id', '0');
        $sql = 'SELECT name from #__ra_clusters ';
        $sql .= 'WHERE code ="' . $code . '"';
        $cluster = $this->objHelper->getValue($sql);

        echo '<h2>Areas in cluster ' . $cluster . '</h2>';
        $sql = 'SELECT * from #__ra_areas ';
        $sql .= 'WHERE cluster ="' . $code . '"';
        $sql .= 'ORDER BY name ';

        $objTable = new ToolsTable;
        $objTable->add_header("Code,Name,Website,CO link,Location");
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->code);
            $objTable->add_item($row->name);
            if ($row->website == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($this->objHelper->buildLink($row->website, $row->website, true));
            }
            if ($row->co_url == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($this->objHelper->buildLink($row->co_url, $row->co_url, true));
            }
            $map_pin = $this->objHelper->showLocation($row->latitude, $row->longitude, 'O');
            $objTable->add_item($map_pin);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = 'index.php?option=com_ra_tools&task=clusters.show&Itemid=' . $menu_id;
        echo $this->objHelper->backButton($back);
    }

    public function showRemote($site_id=2){

//         if (JDEBUG) {
//            $message = 'Site id ' . $site_id . ', ';
//            $message .= 'Seeking clusters from site id ' . $site_id;
//            $this->messages[] = $message;
//        }
        $jsonHelper = new JsonHelper;
        $data = $jsonHelper->getRemoteData($site_id,'/api/index.php/v1/ra_tools/clusters');
        if ($data == false) {
            for ($i = 0, $count = count($jsonHelper->messages); $i < $count; $i++) {
                echo $jsonHelper->messages[$i] . '<br>';
            }
        }
  //      $endpoint = '/api/index.php/v1/ra_tools/clusters';
  //      $data = $jsonHelper->fetchApiData($site_id, $endpoint, 1);
        var_dump($data);        
    }

    public function update() {
        $app = Factory::getApplication();
        $code = $app->input->getWord('code', '');
        $contact = $app->input->getInt('contact', '0');
        if ($contact == 0) {
            echo '<b>Error</b> Contact is zero<br>';
        } else {
            if ($code == '') {
                echo '<b>Error</b> Code is blank<br>';
            } else {
                echo '<h3>Updating cluster ' . $code . '</h3>';
                $objHelper = new ToolsHelper;
                $sql = 'UPDATE `#__ra_clusters` SET contact_id=' . $contact . ' WHERE code="' . $code . '"';
                echo "$sql<br>";
                $objHelper->executeCommand($sql);
            }
        }
        $target = 'index.php?option=com_ra_tools&task=clusters.show';
        echo $this->objHelper->backButton($target);
    }

}
