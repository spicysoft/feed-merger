<?php
  require_once __DIR__ . '/config.php';
  require_once __DIR__ . '/FeedMerger.php';
  require_once __DIR__ . '/SimpleXMLElement2.php';

  date_default_timezone_set( 'Asia/Tokyo' );

$config = ss_feed_merger\config();
$xml = ss_feed_merger\mergeFeeds($config['urls']);
//echo $xml;

function test($data)
{
  $r = SimpleXMLElement2::needCData($data);
  echo "Result:" . $r . ", " . $data . "\n";
}

//test('ABC');
//test('<');
//test('>');
//test('<script type="text/javascript" src="http://js.gsspcln.jp/t/099/343/a1099343.js"></script>');
