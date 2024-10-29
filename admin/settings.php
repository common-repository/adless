<?php

function adless_action_links ( $actions ) {
    $mylinks = array(
       '<a href="' . admin_url( 'options-general.php?page=adless' ) . '">Settings</a>',
       '<a href="https://adless.net/get-started?utm_source=wordpress&medium=integration" target="_blank">Support</a>',
    );
    $actions = array_merge( $actions, $mylinks );
    return $actions;
 }

class AdlessSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));

        add_filter('plugin_action_links_adless/adless.php', 'adless_action_links');
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Adless Settings',
            'Adless',
            'manage_options',
            'adless',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('adless_options');
?>
        <div class="wrap">
            <h1>Adless Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('adless_options');
                do_settings_sections('adless-setting-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'adless_options', // Option group
            'adless_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Mandatory Configuration', // Title
            array($this, 'print_section_info'), // Callback
            'adless-setting-admin' // Page
        );

        add_settings_field(
            'earnerID', // ID
            'Earner ID', // Title 
            array($this, 'earnerID_callback'), // Callback
            'adless-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_section(
            'default_services_section_id', // ID
            'Default Services Configuration', // Title
            array($this, 'print_default_services_section_info'), // Callback
            'adless-setting-admin' // Page
        );

        add_settings_field(
            'services', // ID
            'Default Services', // Title 
            array($this, 'services_callback'), // Callback
            'adless-setting-admin', // Page
            'default_services_section_id' // Section           
        );

        add_settings_section(
            'paywall_section_id', // ID
            'Paywall Configuration', // Title
            array($this, 'print_paywall_section_info'), // Callback
            'adless-setting-admin' // Page
        );

        add_settings_field(
            'paywallMessageField', // ID
            'Paywall Message', // Title 
            array($this, 'paywall_message_callback'), // Callback
            'adless-setting-admin', // Page
            'paywall_section_id' // Section           
        );


        add_settings_section(
            'advanced_section_id', // ID
            'Advanced Configuration', // Title
            array($this, 'print_advanced_section_info'), // Callback
            'adless-setting-admin' // Page
        );

        add_settings_field(
            'customConfiguration', // ID
            'Custom Configuration', // Title 
            array($this, 'custom_configuration_callback'), // Callback
            'adless-setting-admin', // Page
            'advanced_section_id' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['earnerID']))
            $new_input['earnerID'] = sanitize_text_field($input['earnerID']);

        if (isset($input['services']))
            $new_input['services'] = explode(",", sanitize_text_field(str_replace(" ", "", $input['services'])));

        if (isset($input['paywallMessage']))
            $new_input['paywallMessage'] = base64_encode(wpautop($input['paywallMessage']));

        if (isset($input['customConfiguration']))
            $new_input['customConfiguration'] = sanitize_text_field($input['customConfiguration']);

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'We need to know your earner information to configure your Adless integration.<br /><br />Visit <a href="https://adless.net/get-started/wordpress?utm_source=wordpress&medium=integration">adless.net/get-started/wordpress</a> for more information on prerequisites.';
    }

    public function print_advanced_section_info()
    {
        print 'This section allows you full customization of Adless. This configuration will extend and when relevant override the settings above.<br /><br />Documentation can be found in <a href="https://adless.net/get-started?utm_source=wordpress&medium=integration#docs">adless.net/get-started</a>.';
    }

    public function print_paywall_section_info()
    {
        print 'Using the Adless paywall to protect your premium content is about as easy as it gets; after signing up, you just add the css classname <strong>adless-protected-content</strong> to the content you want protected.<br /><br />See <a href="https://adless.net/get-started/wordpress?utm_source=wordpress&medium=integration">adless.net/get-started/wordpress</a> for a simple instruction on how to get started.';
    }

    public function print_default_services_section_info()
    {
        print 'Default services are the services you agree to deliver on <strong>all</strong> pages on this website, for example if you never show any display advertisement (the NO_ADS service). You still need to sign up for the services in <a href="https://adless.net/earnings?utm_source=wordpress&medium=integration" target="_blank">Adless</a> before you receive any compensation so that you know the terms.';
    }

    public function earnerID_callback()
    {
        printf(
            '<input type="text" id="earnerID" name="adless_options[earnerID]" value="%s" class="regular-text" /><br /><p>Your earner ID can be found in <a href="https://adless.net/earnings?utm_source=wordpress&medium=integration" target="_blank">the earners section</a> when you have created an Adless account.</p>',
            isset($this->options['earnerID']) ? esc_attr($this->options['earnerID']) : ''
        );
    }

    public function services_callback()
    {
        printf(
            '<input type="text" id="services" name="adless_options[services]" value="%s" placeholder="SERVICE_1, SERVICE_2" class="regular-text" />',
            isset($this->options['services']) ? esc_attr(implode(", ", $this->options['services'])) : '',

        );
    }

    public function paywall_message_callback()
    {
        printf(
            wp_editor(
                !empty($this->options['paywallMessage']) ? base64_decode($this->options['paywallMessage']) : '<h2>Thank you for your interest!</h2>
                <p>This content is available for paying visitors only.</p>',
                "adless_paywallMessage",
                array(
                    'wpautop' => true,
                    'textarea_name' => 'adless_options[paywallMessage]'
                )
            )
        );
    }

    public function custom_configuration_callback()
    {
        printf(
            '<textarea id="customConfiguration" name="adless_options[customConfiguration]" placeholder="{}" class="large-text code" rows="10">%s</textarea>',
            isset($this->options['customConfiguration']) ? esc_attr($this->options['customConfiguration']) : '',

        );
    }
}

?>