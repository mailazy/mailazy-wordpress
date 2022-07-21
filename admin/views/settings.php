<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}
?>
<div id="mailazy_admin">
    <div class="mailazy_logo">
        <img src="<?php echo MAILAZY_ROOT_URL . 'admin/assets/images/logo.svg' ?>" alt="Mailazy" title="Mailazy">
    </div>
    <br />
    <?php
    settings_errors();
    ?><br />
    <div class="mailazy_config">
        <form method="post" action="options.php">
            <?php
            $mailazy_option = get_option('mailazy_option');
            settings_fields('mailazy_option');
            ?>
            <div class="mailazy_field">
                <label for="mailazy_enable">
                    <?php _e('Enable <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="checkbox" id="mailazy_enable" name="mailazy_option[enable]" value="1" <?php echo (isset($mailazy_option['enable']) && $mailazy_option['enable'] == "1") ? "checked='checked'" : ""; ?>>
            </div>
            <div class="mailazy_field">
                <label for="mailazy_enable_type">
                    <?php _e('Send Type <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <select id="mailazy_enable_type" name="mailazy_option[enable_type]">
                    <option value="api" <?php echo (isset($mailazy_option['enable_type']) && $mailazy_option['enable_type'] == 'api' ? " selected='selected'" : ""); ?>><?php _e('Email API', 'mailazy'); ?></option>
                    <option value="smtp" <?php echo (isset($mailazy_option['enable_type']) && $mailazy_option['enable_type'] == 'smtp' ? " selected='selected'" : ""); ?>><?php _e('SMTP', 'mailazy'); ?></option>
                </select>
            </div>

            <div class="mailazy_field">
                <label for="mailazy_host">
                    <?php _e('Host <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="text" id="mailazy_host" name="mailazy_option[host]" value="<?php echo (isset($mailazy_option['host']) && !empty($mailazy_option['host'])) ? esc_attr($mailazy_option['host']) : "smtp.mailazy.com"; ?>" placeholder="smtp.mailazy.com">
            </div>
            <div class="mailazy_field">
                <label for="mailazy_port">
                    <?php _e('Port <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="text" id="mailazy_port" name="mailazy_option[port]" value="<?php echo (isset($mailazy_option['port']) && !empty($mailazy_option['port'])) ? esc_attr($mailazy_option['port']) : "587"; ?>" placeholder="587">
            </div>
            <div class="mailazy_field" id="mailazy_secure">
                <label for="mailazy_secure">
                    <?php _e('Encription <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="radio" id="mailazy_secure_none" name="mailazy_option[secure]" value="none" <?php echo (isset($mailazy_option['secure']) && $mailazy_option['secure'] == "none") ? "checked='checked'" : ""; ?>>None
                <input type="radio" id="mailazy_secure_tls" name="mailazy_option[secure]" value="tls" <?php echo (!isset($mailazy_option['secure']) || $mailazy_option['secure'] != "none" || $mailazy_option['secure'] != "ssl") ? "checked='checked'" : ""; ?>>TLS
                <input type="radio" id="mailazy_secure_ssl" name="mailazy_option[secure]" value="ssl" <?php echo (isset($mailazy_option['secure']) && $mailazy_option['secure'] == "ssl") ? "checked='checked'" : ""; ?>>SSL
            </div>
            <div class="mailazy_field">
                <label for="mailazy_auth">
                    <?php _e('Authentication <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="checkbox" id="mailazy_auth" name="mailazy_option[auth]" value="1" <?php echo (isset($mailazy_option['auth']) && $mailazy_option['auth'] == "1") ? "checked='checked'" : ""; ?>>
            </div>
            <div class="mailazy_field mailazy_allow_auth">
                <label for="mailazy_apikey">
                    <?php _e('Username <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="text" id="mailazy_apikey" name="mailazy_option[apikey]" value="<?php echo isset($mailazy_option['apikey']) ? esc_attr($mailazy_option['apikey']) : ""; ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
                <div class="mailazy_message"><?php _e('Go to <a href="https://app.mailazy.com" target="_blank">app.mailazy.com</a> -> Access Keys section to get your API key.', 'mailazy'); ?></div>
            </div>
            <div class="mailazy_field mailazy_allow_auth">
                <label for="mailazy_apisecretkey">
                    <?php _e('Password <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="password" id="mailazy_apisecretkey" name="mailazy_option[apisecretkey]" value="<?php echo isset($mailazy_option['apisecretkey']) ? esc_attr($mailazy_option['apisecretkey']) : ""; ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
                <div class="mailazy_message"><?php _e('Go to <a href="https://app.mailazy.com" target="_blank">app.mailazy.com</a> -> Access Keys section to get your API secret.', 'mailazy'); ?></div>
            </div>
            <div class="mailazy_field">
                <label for="mailazy_fromemail">
                    <?php _e('From Email <span class="mailazy_red">*</span> :', 'mailazy'); ?>
                </label>
                <input type="email" id="mailazy_fromemail" name="mailazy_option[fromemail]" value="<?php echo isset($mailazy_option['fromemail']) ? esc_attr($mailazy_option['fromemail']) : ""; ?>" placeholder="From Email">
                <div class="mailazy_message"><?php _e('Your verified domain on Mailazy is example.com then your email should be (anyname)@example.com', 'mailazy'); ?></div>
            </div>
            <hr>
            <div class="mailazy_field">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
</div>
<script>
    (function() {
        if (document.getElementById('mailazy_fromemail').value == '') {
            document.getElementById('mailazy_fromemail').value = 'username@' + window.location.hostname;
        }
        if (document.getElementById('mailazy_enable_type').value == "smtp") {
            smtpView()
        } else {
            emailAPIView()
        }
        document.getElementById('mailazy_enable_type').addEventListener("change", function(e) {
            if (e.target.value == "smtp") {
                smtpView()
            } else {
                emailAPIView()
            }
        })
        document.getElementById('mailazy_auth').addEventListener("change", function(e) {
            if (e.target.checked) {
                manageAuthAllow('block')
            } else {
                manageAuthAllow('none')
            }
        })
        function manageAuthAllow(action){
            var authAllow = document.getElementsByClassName('mailazy_allow_auth');
                for (let i = 0; i < authAllow.length; i++) {
                    authAllow[i].style.display = action;
                }
        }
        function smtpView() {
            document.getElementById('mailazy_host').parentElement.style.display = "block";
            document.getElementById('mailazy_port').parentElement.style.display = "block";
            document.getElementById('mailazy_secure').style.display = "block";
            document.getElementById('mailazy_auth').parentElement.style.display = "block";
            if (document.getElementById('mailazy_auth').checked) {
                manageAuthAllow('block');
            } else {
                manageAuthAllow("none");
            }
        }

        function emailAPIView() {
            document.getElementById('mailazy_host').parentElement.style.display = "none";
            document.getElementById('mailazy_port').parentElement.style.display = "none";
            document.getElementById('mailazy_secure').style.display = "none";
            document.getElementById('mailazy_auth').parentElement.style.display = "none";
            manageAuthAllow('block');
        }
    })();
</script>