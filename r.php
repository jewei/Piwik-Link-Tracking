<?php

/**
 * Outbound Link Redirection & Piwik Tracking.
 *
 * This is a dirty single file solution for tracking outbound links.
 * Clicking on any link with that points to this file, as following example,
 * (e.g. http://example.com/r.php?url=www.google.com) will performe 2 actions:
 * - Send tracking information to Piwik server.
 * - Redirect to the URI specified in the query string.
 */

/**
 * Id site to be tracked.
 */
define('CONST_SITE_ID', 1);

/**
 * 32 chars token_auth string.
 */
define('CONST_TOKEN_AUTH', '9dfed29f99e6f45079d5f50d17c66edc');

/**
 * The base URL for the Piwik server.
 */
define('CONST_API_URL', 'http://piwik.dev/piwik.php');

/**
 * The PHP Piwik library.
 * https://github.com/piwik/piwik-php-tracker
 */
define('CONST_PIWIK_LIB_PATH', __DIR__.'/PiwikTracker.php');

/**
 * Type of the action.
 */
define('CONST_ACTION_TYPE', 'link');

/**
 * The outbound link base URL.
 */
define('CONST_URL_QUERY_FIELD_NAME', 'url');

/**
 * Custom variables.
 */
define('CONST_CUSTOM_VARS', 'projectid,pseudonym');

/**
 * Error message if link not exists.
 */
define('CONST_LINK_ERROR', 'LINK ERROR.');

/**
 * Default HTTP scheme.
 */
define('CONST_DEFAULT_HTTP_SCHEME', 'http://');

/**
 * Extract $_GET and $_SERVER global variables.
 */
if (empty($_GET[CONST_URL_QUERY_FIELD_NAME])) {
    exit(CONST_LINK_ERROR);
}

$outbound_url = $_GET[CONST_URL_QUERY_FIELD_NAME];
unset($_GET[CONST_URL_QUERY_FIELD_NAME]);
$custom_vars = array();

// Add missing HTTP scheme.
if (!preg_match('/^https?:\/\//', $outbound_url)) {
    $outbound_url = CONST_DEFAULT_HTTP_SCHEME . $outbound_url;
}

if (!empty($_GET)) {
    $outbound_url .= '?' . http_build_query($_GET, '', '&');
    $custom_vars = get_custom_vars(explode(',', CONST_CUSTOM_VARS));
}

$http_referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

/**
 * Calling Piwik API.
 */
require CONST_PIWIK_LIB_PATH;

$tracker = new PiwikTracker(CONST_SITE_ID, CONST_API_URL);
$tracker->setTokenAuth(CONST_TOKEN_AUTH);
$tracker->setUrl($http_referer);
$tracker->setIp(get_ip());
$tracker->setUserAgent($_SERVER['HTTP_USER_AGENT']);

if (!empty($custom_vars)) {
    $i = 0;
    foreach ($custom_vars as $key => $value) {
        ++$i;
        $tracker->setCustomVariable($i, $key, $value);
    }
}

$tracker->doTrackAction($outbound_url, CONST_ACTION_TYPE);

/**
 * Redirect and exit script.
 */
header('Location: ' . $outbound_url, true, 302);
exit();

function get_ip()
{
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = $ips[0];
    } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function get_custom_vars($names)
{
    $keys = array_keys($_GET);
    $vars = array();
    if (!empty($names)) {
        foreach ($names as $name) {
            if (in_array($name, $keys)) {
                $vars[$name] = $_GET[$name];
            }
        }
    }
    return $vars;
}
