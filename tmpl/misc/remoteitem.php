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

$website = $site->url;
$token = $site->token;

if (!$page_intro == '') {
    echo $page_intro . '<br>';
}
//      set up maximum time of 5 minutes
$max = 5 * 60;
set_time_limit($max);

// code fom https://slides.woluweb.be/api/api.html
$curl = curl_init();

// Try multiple API path formats for different Joomla versions
// Joomla 4.x uses: /api/index.php/v1/articles
// Joomla 5.x might use: /api/v1/articles
$endpoints_to_try = [
    $website . '/api/index.php/v1/articles/',      // Joomla 4 standard
    $website . '/api/index.php/v1/content/articles/', // Legacy format
    $website . '/api/v1/articles/',                 // Joomla 5 possible format
];

$response = null;
$httpCode = 0;
$url_used = '';

//
// HTTP request headers
$headers = [
    'Accept: application/vnd.api+json',
    'Content-Type: application/json',
    sprintf('X-Joomla-Token: %s', trim($token)),
];

foreach ($endpoints_to_try as $endpoint_url) {
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint_url . $id,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => 'utf-8',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
        ]
    );
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $url_used = $endpoint_url . $id;
    
    // If successful (200) or permission error (401/403), stop trying other paths
    if ($httpCode == 200 || $httpCode == 401 || $httpCode == 403) {
        break;
    }
    // For 404, continue to try the next endpoint path
}

if (curl_errno($curl)) {
    echo '<div class="alert alert-danger"><strong>CURL Error:</strong> ' . curl_error($curl) . '</div>';
    curl_close($curl);
    return;
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($curl_errno = curl_errno($curl)) {
    echo '<div class="alert alert-danger"><strong>CURL Error:</strong> ' . curl_error($curl) . '</div>';
    curl_close($curl);
    return;
}

// Check HTTP response code
if ($httpCode !== 200) {
    echo '<div class="alert alert-warning"><strong>HTTP ' . $httpCode . ':</strong> ';
    if ($httpCode === 404) {
        echo 'Article not found on any compatible API endpoint (both /v1/articles/ and /v1/content/articles/ returned 404).';
    } elseif ($httpCode === 401) {
        echo 'Unauthorized. Check your API token.';
    } elseif ($httpCode === 403) {
        echo 'Forbidden. You may not have permission to access this resource.';
    } else {
        echo 'API request failed.';
    }
    echo '</div>';
    echo '<p><strong>Attempted URL:</strong> ' . htmlspecialchars($url_used) . '</p>';
    
    // Try a diagnostic: test if the API is accessible at all by listing articles
    echo '<p><strong>Running diagnostics...</strong></p>';
    $diagnosticUrl = (strpos($url_used, '/v1/articles/') !== false) 
        ? str_replace('/' . $id, '', $url_used)
        : $website . '/api/index.php/v1/articles';
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $diagnosticUrl,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $diag_response = curl_exec($curl);
    $diag_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if ($diag_httpCode === 200) {
        echo '<div class="alert alert-info"><strong>API is accessible</strong> - The article listing endpoint works, suggesting the article ID or permissions issue</div>';
    } else {
        echo '<div class="alert alert-danger"><strong>API may not be accessible</strong> - Even the articles list endpoint returned HTTP ' . $diag_httpCode . '</div>';
        
        // Try one more diagnostic: test the API root (both Joomla 4 and 5 formats)
        echo '<p><strong>Testing API root endpoints...</strong></p>';
        $api_roots = [
            $website . '/api/index.php',        // Joomla 4 standard
            $website . '/api/v1',               // Joomla 5 possible format
            $website . '/api',                  // Joomla 5 alternative
        ];
        
        $api_found = false;
        foreach ($api_roots as $root_url) {
            curl_setopt_array($curl, [
                CURLOPT_URL => $root_url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => $headers,
            ]);
            $root_response = curl_exec($curl);
            $root_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if ($root_httpCode === 200 || $root_httpCode === 401) {
                echo '<div class="alert alert-info"><strong>API found at: ' . htmlspecialchars($root_url) . '</strong></div>';
                $api_found = true;
                break;
            }
        }
        
        if ($api_found) {
            echo '<p>The API is accessible. This could mean:</p><ul>';
            echo '<li>The article might not exist at that ID</li>';
            echo '<li>Your token may not have read permissions for articles</li>';
            echo '<li>The article might be restricted to specific access levels</li>';
            echo '</ul>';
        } else {
            echo '<div class="alert alert-danger"><strong>API root not found at standard locations</strong></div>';
            echo '<p>Possible causes:</p><ul>';
            echo '<li>Joomla 5 may use a completely different API structure</li>';
            echo '<li>The API component/plugin is disabled</li>';
            echo '<li>There may be server/firewall blocking API access</li>';
            echo '</ul>';
        }
    }
    
    echo '<p><strong>Troubleshooting:</strong></p>';
    echo '<ul>';
    echo '<li>Verify article ' . $id . ' exists and is published in the backend</li>';
    echo '<li>Confirm article access is set to "Public"</li>';
    echo '<li>Check category access is set to "Public"</li>';
    echo '<li>Verify your API token is valid and not expired</li>';
    echo '<li>Check with your hosting provider if the site was recently updated</li>';
    echo '<li>Verify the API component is enabled on the remote Joomla site</li>';
    echo '</ul>';
    
    if ($httpCode === 404 && !empty($response)) {
        echo '<p><strong>Response from specific article request:</strong></p><pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
    }
    curl_close($curl);
    return;
}

$details = json_decode($response, true);

if ($details === null) {
    echo '<div class="alert alert-danger"><strong>JSON Decode Error:</strong> Response is not valid JSON</div>';
    echo '<p><strong>Response:</strong></p><pre>' . htmlspecialchars($response) . '</pre>';
    return;
}

if (!is_null($details["errors"] ?? null)) {
    echo '<div class="alert alert-danger"><strong>API Errors:</strong></div>';
    var_dump($details["errors"]);
    Factory::getApplication()->enqueueMessage('API Errors received', 'error');
    return;
}

if (!isset($details["data"]) || empty($details["data"])) {
    echo '<div class="alert alert-warning"><strong>No data returned</strong> from article ' . $id . '</div>';
    echo '<p><strong>Response:</strong></p><pre>' . htmlspecialchars($response) . '</pre>';
    return;
}

$data = $details["data"];
$attributes = $data["attributes"];

$created = $attributes['created'];
$modified = $attributes['modified'];
$title = $attributes['title'];
$text = $attributes['text'];
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
if (!$modified == '') {
    echo '<b>Modified</b> ' . $modified . '<br>';
}
echo'</div>';
if ($show_details == 'Y') {
    echo "<i>(Showing article $id from ";
    echo $this->toolsHelper->buildLink($website, $website, true);
    echo ')</i><br>';
}
