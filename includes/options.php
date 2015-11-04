<?php
class DnlSettingsPage
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
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Newsletter Settings',
            'Newsletter Settings',
            'manage_options',
            'newsletter-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'dnl_newsletter' );
        ?>
        <div class="wrap">
            <h2>My Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'dnl_newsletter_group' );
                do_settings_sections( 'dnl_newsletter_admin' );
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
            'dnl_newsletter_group', // Option group
            'dnl_newsletter', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'newsletter_settings', // ID
            'Newsletter Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'dnl_newsletter_admin' // Page
        );

        add_settings_field(
            'test_recipient', // ID
            'Default Test Recipient', // Title
            array( $this, 'test_recipient_callback' ), // Callback
            'dnl_newsletter_admin', // Page
            'newsletter_settings' // Section
        );


        add_settings_field(
            'newsletter_recipient',
            'Default Newsletter Recipient',
            array( $this, 'newsletter_recipient_callback' ),
            'dnl_newsletter_admin',
            'newsletter_settings'
        );

        add_settings_field(
            'newsletter_from_name',
            'Newsletter Sender Name',
            array( $this, 'newsletter_from_name_callback' ),
            'dnl_newsletter_admin',
            'newsletter_settings'
        );

        add_settings_field(
            'newsletter_from_email',
            'Newsletter Sender email Address, e.g. news@examle.com',
            array( $this, 'newsletter_from_email_callback' ),
            'dnl_newsletter_admin',
            'newsletter_settings'
        );

        add_settings_field(
            'newsletter_subject',
            'Newsletter Subject',
            array( $this, 'newsletter_subject_callback' ),
            'dnl_newsletter_admin',
            'newsletter_settings'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['test_recipient'] ) )
            $new_input['test_recipient'] = sanitize_email( $input['test_recipient'] );

        if( isset( $input['newsletter_recipient'] ) )
            $new_input['newsletter_recipient'] = sanitize_email( $input['newsletter_recipient'] );

        if( isset( $input['newsletter_from_email'] ) )
            $new_input['newsletter_from_email'] = sanitize_email( $input['newsletter_from_email'] );

        if( isset( $input['newsletter_from_name'] ) )
            $new_input['newsletter_from_name'] = sanitize_text_field( $input['newsletter_from_name'] );

        if( isset( $input['newsletter_subject'] ) )
            $new_input['newsletter_subject'] = sanitize_text_field( $input['newsletter_subject'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function test_recipient_callback()
    {
        printf(
            '<input type="email" id="test_recipient" size="40" required name="dnl_newsletter[test_recipient]" value="%s" />',
            isset( $this->options['test_recipient'] ) ? esc_attr( $this->options['test_recipient']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function newsletter_recipient_callback()
    {
        printf(
            '<input type="email" id="newsletter_recipient" size="40" required name="dnl_newsletter[newsletter_recipient]" value="%s" />',
            isset( $this->options['newsletter_recipient'] ) ? esc_attr( $this->options['newsletter_recipient']) : ''
        );
    }

    public function newsletter_from_name_callback()
    {
        printf(
            '<input type="text" id="newsletter_from_name" size="40" required name="dnl_newsletter[newsletter_from_name]" value="%s" />',
            isset( $this->options['newsletter_from_name'] ) ? esc_attr( $this->options['newsletter_from_name']) : ''
        );
    }

    public function newsletter_from_email_callback()
    {
        printf(
            '<input type="email" id="newsletter_from_email" size="40" required name="dnl_newsletter[newsletter_from_email]" value="%s" />',
            isset( $this->options['newsletter_from_email'] ) ? esc_attr( $this->options['newsletter_from_email']) : ''
        );
    }

    public function newsletter_subject_callback()
    {
        printf(
            '<input type="text" id="newsletter_subject" size="60" required name="dnl_newsletter[newsletter_subject]" value="%s" />',
            isset( $this->options['newsletter_subject'] ) ? esc_attr( $this->options['newsletter_subject']) : ''
        );
    }
}

if( is_admin() )
    $dnl_settings_page = new DnlSettingsPage();
