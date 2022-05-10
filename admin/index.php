<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('mailazyAdmin')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mailazyAdmin {

        /**
         * Constructor
         */
        public function __construct() {
            if (is_admin()) {
                add_action('admin_init', array($this, 'register_settings'));
                add_action('admin_init', array($this, 'register_test_mail'));
            }
            add_action('admin_menu', array($this, 'admin_menu'));
            add_filter('plugin_action_links', array($this, 'setting_links'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'add_stylesheet_to_admin'));
        }

        /**
         * Save Plugin option on option table
         */
        public function register_settings() {
            register_setting('mailazy_option', 'mailazy_option', array($this, 'settings_validation'));
        }

        /**
         * test email after save setting
         */
        public function register_test_mail() {
            register_setting('mailazy_test_mail', 'mailazy_test_mail', array($this, 'test_mail_validation'));
        }

        /**
         * debug test email 
         * 
         * @global type $ts_mail_errors
         * @global type $phpmailer
         * @param type $result
         * @return type
         */
        function debug_wpmail($result = false) {

            if ($result)
                return;

            global $ts_mail_errors, $phpmailer;

            if (!isset($ts_mail_errors))
                $ts_mail_errors = array();

            if (isset($phpmailer))
                $ts_mail_errors[] = $phpmailer->ErrorInfo;

            return $ts_mail_errors;
        }

        /**
         * Send test email
         * @param type $input
         */
        public function test_mail_validation($input) {
            $message = __('There was a problem in configuration.');
            $type = 'error';
            $mailazy_option = get_option('mailazy_option');
            if (isset($mailazy_option['enable_type']) && isset($input['test_to']) && !empty($input['test_to'])) {
                $subject = 'Test email from ' . get_bloginfo('name') . ' via Mailazy Plugin';
                $mailBody = 'Hi test,' . "<br/><br/>";
                $mailBody .= wp_title() . ' Mailazy test email received. with Mail Send Type ' . $mailazy_option['enable_type'] . "<br/>";
                $res = wp_mail($input['test_to'], $subject, $mailBody);
                if ($mailazy_option['enable_type'] == 'api') {
                    $res = json_decode($res);
                    if (isset($res->error) && !empty($res->error)) {
                        $message = __("<b>" . $res->error . "</b>: " . $res->message, 'mailazy');
                    } else {
                        $message = __('Mail Sent!', 'mailazy');
                        $type = 'updated';
                    }
                } else {
                    $result = $this->debug_wpmail($res);
                    if (count($result) > 0) {
                        $message = '';
                        for ($i = 0; $i < count($result); $i++) {
                            $message .= __("<b>Error</b>: " . $result[$i], 'mailazy');
                        }
                    } else {
                        $message = __('Mail Sent!', 'mailazy');
                        $type = 'updated';
                    }
                }
            }
            add_settings_error('mailazy_test_mail_notice', 'mailazy_test_mail', $message, $type);
        }

        /**
         * Mailazy Validation
         */
        public function settings_validation($input) {
            $message = null;
            $type = null;
            if (!$input) {
                $input = array();
            }
            if (null != $input) {
                if (!isset($input['apikey']) || empty($input['apikey'])) {
                    $message = __('Mailazy Required APIkey.', 'mailazy');
                    $type = 'error';
                } elseif (!isset($input['apisecretkey']) || empty($input['apisecretkey'])) {
                    $message = __('Mailazy Required API Secret Key.', 'mailazy');
                    $type = 'error';
                } elseif (!isset($input['fromemail']) || empty($input['fromemail'])) {
                    $message = __('Mailazy Required From Email.', 'mailazy');
                    $type = 'error';
                } elseif (!is_email($input['fromemail'])) {
                    $message = __('From Email is not valid.', 'mailazy');
                    $type = 'error';
                } elseif (get_option('mailazy_option')) {
                    $message = __('Option updated!', 'mailazy');
                    $type = 'updated';
                } else {
                    $message = __('Option added!', 'mailazy');
                    $type = 'updated';
                }
            } else {
                $message = __('There was a problem.', 'mailazy');
                $type = 'error';
            }

            add_settings_error('mailazy_option_notice', 'mailazy_option', $message, $type);
            return $input;
        }

        /**
         * Create menu.
         */
        public function admin_menu() {
            add_menu_page('mailazy', 'Mailazy', 'manage_options', 'mailazy', array('mailazyAdmin', 'setting_page'), MAILAZY_ROOT_URL . 'admin/assets/images/favicon.png');
            $mailazy_option = get_option('mailazy_option');
            if (isset($mailazy_option['enable']) && $mailazy_option['enable'] == "1") {
                add_submenu_page('mailazy', 'Mailazy', 'Test Mail', 'manage_options', 'mailazy-test-mail', array('mailazyAdmin', 'test_mail_page'));
            }
        }

        /**
         * Add a settings link to the Plugins page,
         * so people can go straight from the plugin page to the settings page.
         */
        public function setting_links($links, $file) {
            static $thisPlugin = '';
            if (empty($thisPlugin)) {
                $thisPlugin = MAILAZY_ROOT_SETTING_LINK;
            }
            if ($file == $thisPlugin) {
                $settingsLink = '<a href="admin.php?page=mailazy">' . __('Settings', 'mailazy') . '</a>';

                array_unshift($links, $settingsLink);
            }
            return $links;
        }

        /**
         * Added Style and Script file on plguin Admin Page
         */
        public function add_stylesheet_to_admin() {
            wp_enqueue_style('mailazy-admin-style', MAILAZY_ROOT_URL . 'admin/assets/css/style.css', false, MAILAZY_PLUGIN_VERSION);
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */

        public static function setting_page() {
            require_once(MAILAZY_ROOT_DIR . "admin/views/settings.php");
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */

        public static function test_mail_page() {
            require_once(MAILAZY_ROOT_DIR . "admin/views/test_mail.php");
        }

    }

    new mailazyAdmin();
}
