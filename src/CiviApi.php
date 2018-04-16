<?php

namespace Leanwebstart\CiviApi3;

class CiviApi extends AbstractAPI
{

    protected $uri;
    protected $siteKey;
    protected $apiKey;  //Key attached to the Civi contact
    protected $httpHeaderUserAgent;
    protected $httpHeaderReferer;

    public function __construct()
    {

        // CIVICRM_SITE_param from civicrm.settings.php
        $this->siteKey = config('civi-api3.civi_site_key');

        // API param from contact.
        $this->apiKey = config('civi-api3.civi_user_key');

        // Civi integration path
        $integration = strtolower(config('civi-api3.civi_integration'));

        // Choose the right path. Default to Wordpress
        switch ($integration) {
            case "drupal":
                $path = config('civi-api3.civi_drupal_path');
                break;
            case "joomla":
                $path = config('civi-api3.civi_joomla_path');
                break;
            default:
                $path = config('civi-api3.civi_wordpress_path');
        }

        $this->uri = config('civi-api3.civi_host') . $path . '?json=1';

        // Being nice to firewalls that block requests that don't have those...
        $this->httpHeaderReferer = config('civi-api3.http_referer');
        $this->httpHeaderUserAgent = config('civi-api3.http_user_agent');

    }

    protected function apiCall($entity, $action, $params)
    {

       
        // That's ugly.. just want to control the order of things in the array...
        $params = array(
            "entity" => $entity,
            "action" => $action,
            "key" => $this->siteKey,
            "api_key" => $this->apiKey) + $params;
       
        $postQuery = $this->prepareQueryString($params);
        //dd($postQuery);
        $response = $this->sendPostRequest($postQuery);

        return json_decode($response);
    }

    protected function sendPostRequest($postQuery)
    {
        $ch = curl_init($this->uri);

        curl_setopt($ch, CURLOPT_USERAGENT, $this->httpHeaderUserAgent);
        curl_setopt($ch, CURLOPT_REFERER, $this->httpHeaderReferer);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    // We encode our own parameter string here because http_build_query() does not handle our
    // special case where we pass a 'chainedQuery' => null ...
    protected function prepareQueryString($pameterArray)
    {
        $xformedPostQuery = '';
        foreach ($pameterArray as $param => $val) {
            if ($val) {
                $xformedPostQuery .= urlencode($param) . '=' . urlencode($val) . '&';
            } else {
                // Value of null means we just want the $param in our query string, no '= $val'
                $xformedPostQuery .= urlencode($param) . '&';
            }
        }
        $xformedPostQuery = rtrim($xformedPostQuery, '&');

        return $xformedPostQuery;

    }
}
