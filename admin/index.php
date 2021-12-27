<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('mailazy_Admin')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mailazy_Admin
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            if (is_admin()) {
                add_action('admin_init', array($this, 'register_mailazy_plugin_settings'));
            }
            add_action('admin_menu', array($this, 'create_mailazy_menu'));
            add_filter('plugin_action_links', array($this, 'mailazy_setting_links'), 10, 2);
            add_action('mailazy_reset_admin_action', array($this, 'reset_settings_action'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'add_stylesheet_to_admin'));
        }
        /**
         * Save Plugin option on option table
         */
        public function register_mailazy_plugin_settings()
        {
            register_setting('mailazy_option', 'mailazy_option', array($this,'mailazy_settings_validation'));
        }
        /**
         * Mailazy Validation
         */
        public function mailazy_settings_validation($input)
        {
            $message = null;
            $type = null;
            if (!$input) {
                $input = array();
            }
            if (null != $input) {
                if (!isset($input['apikey']) || empty($input['apikey'])) {
                    $message = __('Mailazy Required APIkey.');
                    $type = 'error';
                } elseif (!isset($input['apisecretkey']) || empty($input['apisecretkey'])) {
                    $message = __('Mailazy Required API Secret Key.');
                    $type = 'error';
                } elseif (!isset($input['fromemail']) || empty($input['fromemail'])) {
                    $message = __('Mailazy Required From Email.');
                    $type = 'error';
                } elseif (get_option('mailazy_option')) {
                    $message = __('Option updated!');
                    $type = 'updated';
                } else {
                    $message = __('Option added!');
                    $type = 'updated';
                }
            } else {
                $message = __('There was a problem.');
                $type = 'error';
            }
            
            add_settings_error('mailazy_option_notice', 'mailazy_option', $message, $type);
            return $input;
        }
        /**
         *
         * @param type $option
         * @param type $settings
         */
        public static function reset_settings_action($option, $settings)
        {
            if (current_user_can('manage_options')) {
                update_option($option, $settings);
            }
        }
        /**
         * Create menu.
         */
        public function create_mailazy_menu()
        {
            add_menu_page('mailazy', 'Mailazy', 'manage_options', 'mailazy', array('mailazy_Admin', 'options_page'), MAILAZY_ROOT_URL . 'admin/assets/images/favicon.ico');
        }
        /**
         * Add a settings link to the Plugins page,
         * so people can go straight from the plugin page to the settings page.
         */
        public function mailazy_setting_links($links, $file)
        {
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
        public function add_stylesheet_to_admin()
        {
            wp_enqueue_style('mailazy-admin-style', MAILAZY_ROOT_URL . 'admin/assets/css/style.css', false, MAILAZY_PLUGIN_VERSION);
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */
        public static function options_page()
        {
            require_once(MAILAZY_ROOT_DIR . "admin/views/settings.php");
        }
    }
    new mailazy_Admin();
}
