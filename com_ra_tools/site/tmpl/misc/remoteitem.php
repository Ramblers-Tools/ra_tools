<?php

/**
 * @version     3.3.15
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 04/02/24 CB created
 * 04/03/24 CB show remote website as a link
 * 05/03/24 Don't display heading from the menu
 * 17/08/25 CB use api_site
 * 14/09/25 CB set up maximum time of 5 minutes
 * 26/02/26 CB changed endpoint from /v1/content/articles to /v1/articles, improved error diagnostics
 */
// No direct access

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;

defined('_JEXEC') or die;

//echo '<h2>' . $this->params->get('page_title') . '</h2>';

$page_intro = $this->menu_params->get('page_intro');
$site_id = $this->menu_params->get('site_id', 0);
if ($site_id == 0) {
    Factory::getApplication()->enqueueMessage('Site id not specified ', 'error');
    return;
}
$id = $this->menu_params->get('id', 10);
$show_details = $this->menu_params->get('show_details', '');

$sql = 'SELECT url,token,colour FROM #__ra_api_sites ';
$sql .= 'WHERE id=' . $site_id;
$site = $this->toolsHelper->getItem($sql);

$website = rtrim($site->url, '/');
$token = $site->token;

if (!$page_intro == '') {
    echo $page_intro . '<br>';
}

// Try multiple API path formats for different Joomla versions
$endpoints_to_try = [
    '/api/index.php/v1/articles/' . $id,      // Joomla 4 standard
    '/api/index.php/v1/content/articles/' . $id, // Legacy format
    '/api/v1/articles/' . $id,                 // Joomla 5 possible format
];

$item = null;
$last_error_messages = [];
$url_used = '';

foreach ($endpoints_to_try as $endpoint) {
    $url_used = $website . $endpoint;
    $item = JsonHelper::getRemoteData($site_id, $endpoint);
    
    if ($item) {
        // Success, we found the article
        break;
    }
    // Collect messages from this attempt
    $last_error_messages = array_merge($last_error_messages, JsonHelper::getMessages());
}


// Check if we have an item
if (!$item) {
    echo '<div class="alert alert-warning"><strong>API Error:</strong> Article not found on any compatible API endpoint.</div>';
    
    foreach($last_error_messages as $message) {
        Factory::getApplication()->enqueueMessage($message, 'notice');
    }

    echo '<p><strong>Attempted URLs:</strong></p><ul>';
    foreach($endpoints_to_try as $endpoint) {
        echo '<li>' . htmlspecialchars($website . $endpoint) . '</li>';
    }
    echo '</ul>';

    echo '<p><strong>Troubleshooting:</strong></p>';
    echo '<ul>';
    echo '<li>Verify article ' . $id . ' exists and is published in the backend</li>';
    echo '<li>Confirm article access is set to "Public"</li>';
    echo '<li>Check category access is set to "Public"</li>';
    echo '<li>Verify your API token is valid and not expired</li>';
    echo '<li>Check with your hosting provider if the site was recently updated</li>';
    echo '<li>Verify the API component is enabled on the remote Joomla site</li>';
    echo '</ul>';

    return;
}

$created = $item->created;
$modified = $item->modified;
$title = $item->title;
$text = $item->text;
echo '<!-- start of code from ' . __FILE__ . '  -->' . PHP_EOL;
//
echo '<!-- start of JSON data  -->' . PHP_EOL;
echo'<div style="background: ' . $site->colour . '; padding-top: 10px; ">';

// Show which endpoint was used (for debugging syndication issues)
if (strpos($url_used, '/v1/content/articles/') !== false) {
    echo '<!-- Using legacy endpoint path: /v1/content/articles/ -->';
} else {
    echo '<!-- Using standard endpoint path: /v1/articles/ -->';
}

echo '<h2>' . $title . '</h2>';
echo $text . '<br>';

echo '<b>Created</b> ' . $created . '<br>';
if (!empty($modified)) {
    echo '<b>Modified</b> ' . $modified . '<br>';
}
echo'</div>';
if ($show_details == 'Y') {
    echo "<i>(Showing article $id from ";
    echo $this->toolsHelper->buildLink($website, $website, true);
    echo ')</i><br>';
}
