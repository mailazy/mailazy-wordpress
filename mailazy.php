<?php

/**
 * Plugin Name: WP SMTP and Email API with Mailazy 
 * Plugin URI: https://mailazy.com/
 * Description: Mailazy provides a secure and delightful experience to your customer with Email API.
 * Version: 2.1
 * Author: Mailazy Team
 * Author URI: https://mailazy.com
 * License: GPL2+
 */
if (!defined('ABSPATH')) {
     exit();
}
// If this file is called directly, abort.
define('MAILAZY_ROOT_DIR', plugin_dir_path(__FILE__));
define('MAILAZY_ROOT_URL', plugin_dir_url(__FILE__));
define('MAILAZY_PLUGIN_VERSION', '2.1');
define('MAILAZY_ROOT_SETTING_LINK', plugin_basename(__FILE__));

if (!class_exists('mailazy')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mailazy {

        /**
         * Constructor
         */
        public function __construct() {
            $this->define_constants();
            $mailazy_option = get_option('mailazy_option');
            if (isset($mailazy_option['enable']) && $mailazy_option['enable'] == "1") {
                if (isset($mailazy_option['enable_type']) && $mailazy_option['enable_type'] == 'smtp') {
                    // Load PHPMailer class, so we can subclass it.
                    global $phpmailer;
                    // (Re)create it, if it's gone missing
                    if (!( $phpmailer instanceof PHPMailer )) {
                        global $wp_version;
                        if ($wp_version < '5.5') {
                            require_once(ABSPATH . WPINC . '/class-phpmailer.php');
                            require_once(ABSPATH . WPINC . '/class-smtp.php');
                            $phpmailer = new PHPMailer();
                        } else {
                            require_once(ABSPATH . WPINC . '/PHPMailer/PHPMailer.php');
                            require_once(ABSPATH . WPINC . '/PHPMailer/SMTP.php');
                            require_once(ABSPATH . WPINC . '/PHPMailer/Exception.php');
                            $phpmailer = new PHPMailer\PHPMailer\PHPMailer();
                        }
                    }
                    add_action('phpmailer_init', array($this, 'smtp'), 10, 1);
                } else if (!function_exists('wp_mail')) {

                    /**
                     * Sends an email, similar to PHP's mail function.
                     *
                     * A true return value does not automatically mean that the user received the
                     * email successfully. It just only means that the method used was able to
                     * process the request without any errors.
                     *
                     * The default content type is `text/plain` which does not allow using HTML.
                     * However, you can set the content type of the email by using the
                     * {@see 'wp_mail_content_type'} filter.
                     *
                     * The default charset is based on the charset used on the blog. The charset can
                     * be set using the {@see 'wp_mail_charset'} filter.
                     *
                     * @since 1.2.1
                     * @since 5.5.0 is_email() is used for email validation,
                     *              instead of emailService's default validator.
                     *
                     * @global emailService\emailService\emailService $emailService
                     *
                     * @param string|string[] $to          Array or comma-separated list of email addresses to send message.
                     * @param string          $subject     Email subject.
                     * @param string          $message     Message contents.
                     * @param string|string[] $headers     Optional. Additional headers.
                     * @param string|string[] $attachments Optional. Paths to files to attach.
                     * @return bool Whether the email was sent successfully.
                     */
                    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
                        $mailazy_option = get_option('mailazy_option');
                        // Compact the input, apply the filters, and extract them back out.
                        /**
                         * Filters the wp_mail() arguments.
                         *
                         * @since 2.2.0
                         *
                         * @param array $args {
                         *     Array of the `wp_mail()` arguments.
                         *
                         *     @type string|string[] $to          Array or comma-separated list of email addresses to send message.
                         *     @type string          $subject     Email subject.
                         *     @type string          $message     Message contents.
                         *     @type string|string[] $headers     Additional headers.
                         *     @type string|string[] $attachments Paths to files to attach.
                         * }
                         */
                        $atts = apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments'));

                        /**
                         * Filters whether to preempt sending an email.
                         *
                         * Returning a non-null value will short-circuit {@see wp_mail()}, returning
                         * that value instead. A boolean return value should be used to indicate whether
                         * the email was successfully sent.
                         *
                         * @since 5.7.0
                         *
                         * @param null|bool $return Short-circuit return value.
                         * @param array     $atts {
                         *     Array of the `wp_mail()` arguments.
                         *
                         *     @type string|string[] $to          Array or comma-separated list of email addresses to send message.
                         *     @type string          $subject     Email subject.
                         *     @type string          $message     Message contents.
                         *     @type string|string[] $headers     Additional headers.
                         *     @type string|string[] $attachments Paths to files to attach.
                         * }
                         */
                        $pre_wp_mail = apply_filters('pre_wp_mail', null, $atts);

                        if (null !== $pre_wp_mail) {
                            return $pre_wp_mail;
                        }

                        if (isset($atts['to'])) {
                            $to = $atts['to'];
                        }

                        if (!is_array($to)) {
                            $to = explode(',', $to);
                        }

                        if (isset($atts['subject'])) {
                            $subject = $atts['subject'];
                        }

                        if (isset($atts['message'])) {
                            $message = $atts['message'];
                        }

                        if (isset($atts['headers'])) {
                            $headers = $atts['headers'];
                        }

                        if (isset($atts['attachments'])) {
                            $attachments = $atts['attachments'];
                        }

                        if (!is_array($attachments)) {
                            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
                        }
                        global $emailService;

                        // (Re)create it, if it's gone missing.
                        if (!( $emailService instanceof mailazyWPClient )) {
                            require_once(MAILAZY_ROOT_DIR . "mailazyWPClient.php");
                            $emailService = new mailazyWPClient();
                            $emailService->setApikey($mailazy_option['apikey']);
                            $emailService->setApisecret($mailazy_option['apisecretkey']);
                        }

                        // Headers.
                        $cc = array();
                        $bcc = array();
                        $reply_to = array();

                        if (empty($headers)) {
                            $headers = array();
                        } else {
                            if (!is_array($headers)) {
                                // Explode the headers out, so this function can take
                                // both string headers and an array of headers.
                                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
                            } else {
                                $tempheaders = $headers;
                            }
                            $headers = array();

                            // If it's actually got contents.
                            if (!empty($tempheaders)) {
                                // Iterate through the raw headers.
                                foreach ((array) $tempheaders as $header) {
                                    if (strpos($header, ':') === false) {
                                        if (false !== stripos($header, 'boundary=')) {
                                            $parts = preg_split('/boundary=/i', trim($header));
                                            $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
                                        }
                                        continue;
                                    }
                                    // Explode them out.
                                    list( $name, $content ) = explode(':', trim($header), 2);

                                    // Cleanup crew.
                                    $name = trim($name);
                                    $content = trim($content);

                                    switch (strtolower($name)) {
                                        // Mainly for legacy -- process a "From:" header if it's there.
                                        case 'content-type':
                                            if (strpos($content, ';') !== false) {
                                                list( $type, $charset_content ) = explode(';', $content);
                                                $content_type = trim($type);
                                                if (false !== stripos($charset_content, 'charset=')) {
                                                    $charset = trim(str_replace(array('charset=', '"'), '', $charset_content));
                                                } elseif (false !== stripos($charset_content, 'boundary=')) {
                                                    $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset_content));
                                                    $charset = '';
                                                }

                                                // Avoid setting an empty $content_type.
                                            } elseif ('' !== trim($content)) {
                                                $content_type = trim($content);
                                            }
                                            break;
                                        case 'cc':
                                            $cc = array_merge((array) $cc, explode(',', $content));
                                            break;
                                        case 'bcc':
                                            $bcc = array_merge((array) $bcc, explode(',', $content));
                                            break;
                                        case 'reply-to':
                                            $reply_to = array_merge((array) $reply_to, explode(',', $content));
                                            break;
                                        default:
                                            // Add it to our grand headers array.
                                            $headers[trim($name)] = trim($content);
                                            break;
                                    }
                                }
                            }
                        }

                        try {
                            $emailService->setFrom($mailazy_option['fromemail']);
                        } catch (Exception $e) {
                            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
                            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

                            /** This filter is documented in wp-includes/pluggable.php */
                            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $e->getMessage(), $mail_error_data));

                            return false;
                        }

                        // Set mail's subject and body.
                        $emailService->setSubject($subject);
                        $emailService->setBody($message);

                        // Set destination addresses, using appropriate methods for handling addresses.
                        $address_headers = compact('to', 'cc', 'bcc', 'reply_to');

                        foreach ($address_headers as $address_header => $addresses) {
                            if (empty($addresses)) {
                                continue;
                            }

                            foreach ((array) $addresses as $address) {
                                try {
                                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                                    $recipient_name = '';

                                    if (preg_match('/(.*)<(.+)>/', $address, $matches)) {
                                        if (count($matches) == 3) {
                                            $recipient_name = $matches[1];
                                            $address = $matches[2];
                                        }
                                    }

                                    switch ($address_header) {
                                        case 'to':
                                            $emailService->addAddress($address, $recipient_name);
                                            break;
                                        case 'cc':
                                            $emailService->addCC($address, $recipient_name);
                                            break;
                                        case 'bcc':
                                            $emailService->addBCC($address, $recipient_name);
                                            break;
                                        case 'reply_to':
                                            $emailService->addReplyTo($address, $recipient_name);
                                            break;
                                    }
                                } catch (Exception $e) {
                                    continue;
                                }
                            }
                        }

                        // Set Content-Type and charset.
                        // If we don't have a content-type from the input headers.
                        if (!isset($content_type)) {
                            $content_type = 'text/plain';
                        }

                        /**
                         * Filters the wp_mail() content type.
                         *
                         * @since 2.3.0
                         *
                         * @param string $content_type Default wp_mail() content type.
                         */
                        $content_type = apply_filters('wp_mail_content_type', $content_type);

                        $emailService->ContentType = $content_type;

                        // Set whether it's plaintext, depending on $content_type.
                        if ('text/html' === $content_type) {
                            $emailService->isHTML(true);
                        }

                        // If we don't have a charset from the input headers.
                        if (!isset($charset)) {
                            $charset = get_bloginfo('charset');
                        }

                        /**
                         * Filters the default wp_mail() charset.
                         *
                         * @since 2.3.0
                         *
                         * @param string $charset Default email charset.
                         */
                        $emailService->CharSet = apply_filters('wp_mail_charset', $charset);

                        if (!empty($attachments)) {
                            foreach ($attachments as $attachment) {
                                try {
                                    $emailService->addAttachment($attachment);
                                } catch (Exception $e) {
                                    continue;
                                }
                            }
                        }

                        /**
                         * Fires after emailService is initialized.
                         *
                         * @since 2.2.0
                         *
                         * @param emailService $emailService The emailService instance (passed by reference).
                         */
                        do_action_ref_array('phpmailer_init', array(&$emailService));

                        // Send!
                        try {
                            return $emailService->send();
                        } catch (Exception $e) {

                            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
                            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

                            /**
                             * Fires after a Exception is caught.
                             *
                             * @since 4.4.0
                             *
                             * @param WP_Error $error A WP_Error object with the Exception message, and an array
                             *                        containing the mail recipient, subject, message, headers, and attachments.
                             */
                            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $e->getMessage(), $mail_error_data));

                            return false;
                        }
                    }

                }
            }
        }

        /**
         * smtp functionality with wordpress
         * 
         * @param type $phpmailer
         * @return type
         */
        public function smtp($phpmailer) {
            $mailazy_option = get_option('mailazy_option');
            if (empty($mailazy_option["apikey"]) || empty($mailazy_option["apisecretkey"]) || !is_email($mailazy_option['fromemail'])) {
                return;
            }
            $phpmailer->isSMTP();
            $phpmailer->Host = 'smtp.mailazy.com';
            $phpmailer->SMTPAuth = true;
            $phpmailer->SMTPSecure = 'tls';
            $phpmailer->Port = 587;
            $phpmailer->Username = $mailazy_option["apikey"];
            $phpmailer->Password = $mailazy_option["apisecretkey"];
            $phpmailer->From = $mailazy_option["fromemail"];
            $phpmailer->isHTML(true);
        }

        /**
         * Define constants needed across the plug-in.
         */
        public function define_constants() {
            require_once(MAILAZY_ROOT_DIR . "admin/index.php");
        }

        /**
         * Post Data validation
         */
        public static function data_validation($key, $post) {
            return isset($post[$key]) && !empty($post[$key]) ? sanitize_text_field(esc_html(wp_kses(trim($post[$key])))) : false;
        }

    }

    new mailazy();
}
