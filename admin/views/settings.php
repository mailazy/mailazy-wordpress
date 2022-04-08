<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}
?>
<div id="mailazy_admin">
    <div class="mailazy_logo">
        <img src="<?php echo MAILAZY_ROOT_URL . 'admin/assets/images/logo.svg'?>" alt="Mailazy" title="Mailazy">
    </div>
    <br/>
    <?php
    settings_errors();
    ?><br/>
    <div class="mailazy_config">
        <form method="post" action="options.php"> 
            <?php
            $mailazy_option = get_option('mailazy_option');
            settings_fields('mailazy_option');
			?>
            <div class="mailazy_field">
                <label for="mailazy_enable">
                <?php _e('Enable <span class="mailazy_red">*</span> :','mailazy');?>
                </label>
                <input type="checkbox" id="mailazy_enable" name="mailazy_option[enable]" value="1" <?php echo (isset($mailazy_option['enable']) && $mailazy_option['enable'] == "1") ? "checked='checked'" : "" ;?>>
            </div>
			<div class="mailazy_field">
                <label for="mailazy_apikey">
                <?php _e('APIkey <span class="mailazy_red">*</span> :','mailazy');?>
                </label>
                <input type="text" id="mailazy_apikey" name="mailazy_option[apikey]" value="<?php echo isset($mailazy_option['apikey'])?esc_attr($mailazy_option['apikey']):"";?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
                <div class="mailazy_message">Go to the <a href="https://app.mailazy.com/access-keys" target="_blank">API Details</a> screen from your Website Dashboard to findyour API Key.</div>
            </div>
			<div class="mailazy_field">
                <label for="mailazy_apisecretkey">
                <?php _e('API Secret key <span class="mailazy_red">*</span> :','mailazy');?>
                </label>
                <input type="password" id="mailazy_apisecretkey" name="mailazy_option[apisecretkey]" value="<?php echo isset($mailazy_option['apisecretkey'])?esc_attr($mailazy_option['apisecretkey']):"";?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
                <div class="mailazy_message">Go to the <a href="https://app.mailazy.com/access-keys" target="_blank">API Details</a> screen from your Website Dashboard to findyour API Secret Key.</div>
            </div>
			<div class="mailazy_field">
                <label for="mailazy_fromemail">
                <?php _e('From Email <span class="mailazy_red">*</span> :','mailazy');?>
                </label>
                <input type="text" id="mailazy_fromemail" name="mailazy_option[fromemail]" value="<?php echo isset($mailazy_option['fromemail'])?esc_attr($mailazy_option['fromemail']):"";?>" placeholder="From Email">
                <div class="mailazy_message">From Email! We recommend using the same email that is configured on mailazy as sender mail. Example: info@your_domain.com</div>
            </div>
            <hr>
            <div class="mailazy_field">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
</div>
<script>
(function(){
	if(document.getElementById('mailazy_fromemail').value == ''){
		document.getElementById('mailazy_fromemail').value = 'info@'+window.location.hostname;
	}
})();
</script>