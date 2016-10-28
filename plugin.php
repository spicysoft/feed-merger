<?php
/*
Plugin Name: ss_feed_merger
Plugin URI: 
Description: Merge feeds of other wordpresses
Version: 0.0
Author: Motoyasu, Yamada
Author URI: m_yamada@spicysoft.com
License: Apache License Version 2.0
*/
require_once __DIR__ . '/FeedMerger.php';
require_once __DIR__ . '/config.php';

class ss_feed_merger_plugin
{
  private $path;
  private $urls;

  public function __construct()
  {
    $config = ss_feed_merger\config();
    $this->path = $config['path'];
    $this->urls = $config['urls'];

    add_filter('query_vars', array($this, 'my_query_vars'));
    add_action('init', array($this, 'my_init'));
    add_action('template_redirect', array($this,'my_template_redirect'));
  }

  public function my_init() 
  {
    add_rewrite_endpoint($this->path , EP_ROOT);
    flush_rewrite_rules();
  }

  public function my_query_vars($vars) 
  {
    $vars[] = $this->path ;
    return $vars;
  }

  public function my_template_redirect() 
  {
    global $wp_query;
    if (!isset($wp_query->query[$this->path])) {
      return;
    }

    date_default_timezone_set( 'Asia/Tokyo' );

    $xml = ss_feed_merger\mergeFeeds($this->urls);

    header('Content-Type: text/xml; charset=utf-8');
    header('Content-Length:' . strlen($xml));
    echo $xml;
    exit;
  }
}

new ss_feed_merger_plugin;



