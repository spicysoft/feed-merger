<?php namespace ss_feed_merger;
require_once __DIR__  . '/SimpleXMLElement2.php';

function mergeFeeds($urls)
{
  $xmls = array();
  $namespaces = array();
  $sort = array();
  $entries = array();

  for ($i = 0; $i < count($urls); $i++) {
    $url = $urls[$i];
    $xml= simplexml_load_file($url, 'SimpleXMLElement2', LIBXML_NOCDATA);
    $xmls[]  = $xml;
    foreach ($xml->getDocNamespaces() as $n => $u) {
      $namespaces[$n] = $u;
    }
    foreach($xml->xpath('channel/item') as $item) {
      $entries[] = $item;
      $d = $item->pubDate->__toString();
      $sort[] = strtotime($d);
    }
  }

  array_multisort($sort, SORT_DESC, SORT_NUMERIC , $entries);

  $attributes = '';
  foreach($namespaces as $n => $u) {
    $attributes .= ' ' . 'xmlns:' . $n . '="' . $u . '"';
  }

  $output = new \SimpleXMLElement2('<?xml version="1.0" encoding="UTF-8"?>'. "\n" .'<rss version="2.0" xmlns="http://purl.org/rss/1.0/"' . $attributes . '></rss>');
  $channel = $output->addChild('channel');

  $xml = $xmls[0];
  $tmp = $xml->xpath('channel');
  $src_channel = $tmp[0];
  $channel->copyFrom($src_channel, array('item', 'generator'));

  foreach($entries as $e) {
      $item = $channel->addChild('item');
      $item->copyFrom($e);
  }

  return $output->asPrettyXML();
}

