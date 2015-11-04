<?php
global $wp_query;

$event_type = 'future-events';
$date_format = 'd.m.';

/* Evnts Categories */
$cat = get_post_meta($wp_query->post->ID, '_event_categories', true);

$_type = array(
         'taxonomy' => 'wp_event_type',
         'field' => 'slug',
         'terms' => $event_type
        );
$_cat = array(
         'taxonomy' => 'wp_event_categories',
         'field' => 'slug',
         'terms' => $cat
        );
$tax = array($_type);

if (isset($cat) && is_array($cat)) {

  /* All categories */
  if ($cat[0] == '_all' && count($cat) == 1) $tax = array($_type);
  else $tax = array($_type, $_cat);
}

$args = array(
  'post_type'        => 'wp_events_manager',
  'tax_query'        => $tax,
  'showposts'        => 1000,
  'paged'            => 0,
  'orderby'          => 'meta_value',
  'meta_key'         => '_event_date_start',
  'order'            => 'ASC',
  'suppress_filters' => 0 // WPML FIX
  );

$wp_query = new WP_Query();
$wp_query->query($args);

remove_filter('the_title', 'colorize');

$content = "<strong>Rainer von Vielen Live:</strong>"."\r\n";

while (have_posts()) :
    the_post();

    /* Event Date */
    $event_date_start = strtotime(get_post_meta($wp_query->post->ID, '_event_date_start', true));

    /* Location */
    $event_location = get_post_meta($wp_query->post->ID, '_event_location', true);
    
    $content .= the_title(date($date_format, $event_date_start)." - ",", ".$event_location."\r\n", "", false);
endwhile;
?>
