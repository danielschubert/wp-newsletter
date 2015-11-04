<?php
/*
Plugin Name: Dan’s Newsletter Tool
Plugin URI: https://github.com/danielschubert/wp-newsletter
Description: Sends email using mailgun, includes and requires my fork of the official mailgun plugin ( https://github.com/mailgun/wordpress-plugin )
Author: Daniel Schubert <mail@schubertdaniel.de>
Version: 2.0
Author URI: http://www.schubertdaniel.de
*/

include_once(ABSPATH.'wp-admin/includes/plugin.php');

/* include settings page for admin */
if (is_admin())
    require_once(__DIR__ . '/includes/options.php');

/* Include my fork of the official mailgun plugin ( https://github.com/mailgun/wordpress-plugin )*/
require_once(__DIR__ . '/includes/mailgun/mailgun.php');

class DansNewsletterTool
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
        date_default_timezone_set("Europe/Berlin");
        add_action('admin_menu', array( &$this, 'load_datepicker'));
        add_action( 'admin_menu', array( &$this, 'register_newsletter_page' ) );
        $this->options = get_option( 'dnl_newsletter' );
    }

    /**
     * create Newsletter menu entry
     */
    function register_newsletter_page(){
        add_object_page( 'Newsletter', 'Newsletter', 'edit_published_posts', 'Newsletter Page', array( $this, 'dnl_newsletter_page' ) , 'dashicons-email-alt', 6 );
    }

    /**
     *  load javascript and css for the date- and timepicker
     */
    function load_datepicker() {
        $dir = plugins_url() . "/" . dirname( plugin_basename( __FILE__ ) );
  		wp_enqueue_script('jquery-ui', $dir . '/jquery-ui/jquery-ui.min.js');
        wp_enqueue_script('timepicker', $dir . '/jquery-ui/jquery.ui.timepicker.js');
        wp_enqueue_style('timepicker', $dir . '/jquery-ui/jquery.ui.timepicker.css');
    }

    /**
     * Actually send the newsletter
     */
    public function dnl_send_mail()
    {
        $headers = array('Content-Type: multipart/alternative; charset=UTF-8', 'From: "' .
            $this->options['newsletter_from_name'] . '" <' . $this->options['newsletter_from_email'] . '>');

        $html = apply_filters( 'the_content', $this->html_message ) .
            "<p><a href='%mailing_list_unsubscribe_url%'>Unsubscribe Link</a></p> ";

        $txt =  strip_tags($this->txt_message) . "\r\n" . "Unsubscribe Link:" . "\r\n" .  "%mailing_list_unsubscribe_url% ";
    
        /*  Finally Send the Newsletter  */
        wp_mail($this->to, $this->options['newsletter_subject'] , $html, $txt, $headers, $this->deliverytime);
    }

    /**
     * create Newsletter Page
     */
    public function dnl_newsletter_page(){ ?>
      <div class="wrap">
          <h1>Newsletter Text</h1>
          <div>
            <p>Enter 2 Versions of your message. A HTML version and a Plaintext version.
          </div>

          <h3>HTML Variant</h3>
          <form action="" method="post" id="snl">
            <?php
              if (isset ($_POST['html_content'])) {
                 $html_content = $_POST['html_content'];
              } 

              if (isset ($_POST['txt_content'])) {
                  $txt_content = $_POST['txt_content'];
              } else {
                  $txt_content .= strip_tags( $html_content );
              }

              wp_editor( $html_content, "html_content", array( 'media_buttons' => false , 'teeny' => true) );
            ?>
            <hr />

            <h3>Plaintext Variant</h3>
            <textarea name="txt_content" rows="30" cols="80"><?php echo $txt_content; ?></textarea>

            <h2>Send Testmail</h2>
            Give recipients email address:
            <input type="text" class="regular-text" name="test-email" value="<?php print $this->options['test_recipient'] ?>"/>
            <?php
              if ( isset ($_POST['test']))
              {
                  submit_button('Send Test', 'secondary', 'test');
                  echo "<div style=\"color: green\"><strong>Test sent</strong></div>";
              } else  {
                  submit_button('Send Test', 'primary', 'test');
              }
            ?>
            <hr />

            <h2>Send real Newsletter</h2>
            <div style="color: red"><strong>Careful, this might be serious!! </strong></div>
            <div>
              <h3>Choose delivery time:</h3>The letter is being sent imediately if none.
              <p>Choose date<input type="text" id="datepicker" name="date" size="20"> (max 3 Tagen, a Mailgun limit)</p>
              <p>Choose time<input type="text" id="timepicker" name="time" size="5"></p>
            </div>
            
            Give recipients email address (your mailing list):
            <input type="email" class="regular-text" name="nl-recepient" value="<?php print $this->options['newsletter_recipient'] ?>"/>
            <?php
              submit_button('Send Newsletter !!', 'primary', 'notest');
              if ( isset ($_POST['notest']))
                echo "<div style=\"color: green\"><strong>Newsletter delivery time: " . date("d.m.y, H:i", $this->deliverytime) . "</strong></div>";
            ?>
          </form>

          <script type="text/javascript">
             jQuery(function() {
                 jQuery('#snl').submit(function() {
                      var c = confirm("Really? Are you sure??");
                      return c;
                  });
                  jQuery( "#datepicker" ).datepicker({ minDate: -0, maxDate: "+3D", dateFormat:  "yy-m-d" });
                  jQuery('#timepicker').timepicker();
             });
         </script>
      </div>
      <?php
    }
}

if( is_admin() ) {
    $newsletter = new DansNewsletterTool();

    if ( isset ($_POST['test']))
        $newsletter->to = $_POST['test-email'];

    if ( isset ($_POST['notest']))
        $newsletter->to = $_POST['nl-recepient'];

    if ( isset ($_POST['test']) || (isset ($_POST['notest']))){
        $deliverytime = strtotime($_POST['date'] . "T" .  $_POST['time']);
        
        $newsletter->html_message = $_POST['html_content'];
        $newsletter->txt_message = $_POST['txt_content'];
        $newsletter->deliverytime = $deliverytime;
         
        $newsletter->dnl_send_mail();
 
    }
}
