<?php
  require_once __DIR__ . '/config.php';
  require_once __DIR__ . '/FeedMerger.php';

  date_default_timezone_set( 'Asia/Tokyo' );

  $config = ss_feed_merger\config();
  $xml = ss_feed_merger\mergeFeeds($config['urls']);
  echo $xml;
