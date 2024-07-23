<div class="wfp-settings-container">
    <h1>Weather Forecast Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('wfp_settings_group'); ?>
        <?php do_settings_sections('wfp_settings_group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Latitude</th>
                <td><input type="text" name="wfp_latitude" value="<?php echo esc_attr(get_option('wfp_latitude')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Longitude</th>
                <td><input type="text" name="wfp_longitude" value="<?php echo esc_attr(get_option('wfp_longitude')); ?>" /></td>
            </tr>
              <tr valign="top">
                <th scope="row">Location ID</th>
                <td><input type="text" name="wfp_location_id" value="<?php echo esc_attr(get_option('wfp_location_id')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Language</th>
                <td>
                    <select name="wfp_language">
                        <option value="en" <?php selected(get_option('wfp_language'), 'en'); ?>>English</option>
                      <!--   <option value="nb" <?php //selected(get_option('wfp_language'), 'nb'); ?>>Norwegian Bokmål</option>
                        <option value="nn" <?php //selected(get_option('wfp_language'), 'nn'); ?>>Norwegian Nynorsk</option>
                        <option value="sme" <?php //selected(get_option('wfp_language'), 'sme'); ?>>Northern Sami</option> -->
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Mode</th>
                <td>
                    <select name="wfp_mode">
                        <option value="light" <?php selected(get_option('wfp_mode'), 'light'); ?>>Light</option>
                        <option value="dark" <?php selected(get_option('wfp_mode'), 'dark'); ?>>Dark</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
