<?php
if (!defined('ABSPATH')) {
    exit();
}
require_once(MAILAZY_ROOT_DIR."sdk/mailazyAPI.php");
/**
 * OverWrite mailazyWPClient Class with mailazyAPI
 */
class mailazyWPClient extends mailazyAPI
{	
    /**
     * OverWrite Request function of mailazy API request
     */
    public function request($endPointPath, $args = array()){
        $output = array();
        $request = wp_remote_request($this->getApiurl() . $endPointPath, $args);
        $output['response'] = wp_remote_retrieve_body($request);
        $output['status_code'] = $request["response"]["code"];
        return $output;
    }
}