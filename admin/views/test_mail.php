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
            settings_fields('mailazy_test_mail');
			?>
            <div class="mailazy_field">
                <label for="mailazy_test_to">
                <?php _e('To <span class="mailazy_red">*</span> :','mailazy');?>
                </label>
                <input type="text" id="mailazy_test_to" name="mailazy_test_mail[test_to]" placeholder="username@domain.com">
            </div>
            <hr />
            <div class="mailazy_field">
                <?php submit_button('Send Mail'); ?>
            </div>
        </form>
    </div>
</div>