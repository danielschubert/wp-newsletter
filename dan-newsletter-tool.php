<?php
/*
Plugin Name: Dan’s Newsletter Tool
Plugin URI: http://www.schubertdaniel.de
Description: Adds a Menu "Newsletter". Sends an email, if you wish.
Author: Daniel Schubert
Version: 1.0
Author URI: http://www.schubertdaniel.de/
*/

include_once(ABSPATH.'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/dan-newsletter-tool/options.php');

class DanNewsletterPage
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
        add_action( 'admin_menu', array( &$this, 'register_newsletter_page' ) );
        add_action( 'c_f_m', array( &$this, 'check_for_mailgun' ));

        $this->options = get_option( 'dnl_newsletter' );
    }

    /**
     *  check if the mailgun plugin is active, else use builtin mailing method
     */
    function check_for_mailgun() {
        if (is_plugin_active('mailgun/mailgun.php')){
            require_once( ABSPATH . 'wp-content/plugins/mailgun/includes/wp-mail.php' );
        } else {
            require_once( ABSPATH . 'wp-includes/pluggable.php');
        }
    }

    /**
     * Actually send the newsletter
     */
    public function dnl_send_mail()
    {
        do_action(c_f_m);
        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: "' . $this->options['newsletter_from_name'] . '" <' . $this->options['newsletter_from_email'] . '>');
        wp_mail($this->to, $this->options['newsletter_subject'] , apply_filters( 'the_content', $this->message ), $headers);
    }

    /**
     * create Newsletter menu entry
     */
    function register_newsletter_page(){
        add_object_page( 'Newsletter', 'Newsletter', 'edit_published_posts', 'Newsletter Page', array( $this, 'dnl_newsletter_page' ) , 'dashicons-email-alt', 6 );
    }

    /**
     * create Newsletter Page
     */
    public function dnl_newsletter_page(){
        ?>
          <div class="wrap">
              <h1>Newsletter Text</h1>
              <form action="" method="post">

                <?php
                  if (isset ($_POST['mg_newsletter_content']))
                     $content = $_POST['mg_newsletter_content'];

                  $editor_id = "mg_newsletter_content";
                  wp_editor( $content, $editor_id, array( 'media_buttons' => false , 'teeny' => true) );
                ?>
                <h2>Send Testmail</h2>
                Enter Test Recipient:
                <input type="text" class="regular-text" name="test-email" value="<?php print $this->options['test_recipient'] ?>"/>
                <?php
                  if ( isset ($_POST['test']))
                  {
                      submit_button('Send Test', 'secondary', 'test');
                      echo "<div style=\"color: green\"><strong>Test Sent</strong></div>";
                  } else  {
                      submit_button('Send Test', 'primary', 'test');
                  }
                ?>
                <hr />
                <h2>Send real Newsletter</h2>
                <div style="color: red"><strong>Really? </strong></div>
                Enter Subscriber List
                <input type="email" class="regular-text" name="nl-recepient" value="<?php print $this->options['newsletter_recipient'] ?>"/>
                <?php
                  submit_button('Send Newsletter!!', 'primary', 'notest');
                  if ( isset ($_POST['notest']))
                    echo "<div style=\"color: green\"><strong>Newsletter Sent</strong></div>";
                ?>
              </form>
          </div>
    <?php
    }
}

if( is_admin() )
    $dnl_newsletter = new DanNewsletterPage();

if ( isset ($_POST['test'])) {
    // Test Newsletter
    $to = $_POST['test-email'];
} elseif ( isset ($_POST['notest'])) {
    // Real Newsletter
    $to = $_POST['nl-recepient'];
}

if ( isset ($_POST['test']) || (isset ($_POST['notest']))){
    $dnl_newsletter->message = $_POST['mg_newsletter_content'];
    $dnl_newsletter->to = $to;
    $dnl_newsletter->dnl_send_mail();
}
