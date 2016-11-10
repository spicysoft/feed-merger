<?php

class SimpleXMLElement2 extends \SimpleXMLElement 
{
  const NAMESPACE_RSS = 'http://purl.org/rss/1.0/';
  private static $CDATAEncoding = array('description', 'content:encoded', 'summary', 'dc:creator', 'category', 'snf:analytics', 'snf:advertisement');

  /**
   * CDATAセクションを自身の内部に生成
   * 
   * @param string $data
   * @return SimpleXMElement2 $this
   */
  public function setCData($data) {
      $dom = dom_import_simplexml($this);
      $dom->appendChild($dom->ownerDocument->createCDATASection($data));
      return $this;
  }

  /**
   * CDATAセクションを新しい要素の中に生成
   * 
   * @param string $name
   * @param string $data
   * @param string [$namespace]
   * @return SimpleXMElement2 新しい要素
   */
  public function addChildWithCData($name, $data, $namespace = null) {
      $child = $this->addChild($name, null, $namespace);
      $dom = dom_import_simplexml($child);
      $dom->appendChild($dom->ownerDocument->createCDATASection($data));
      return $child;
  }

  /**
   * XMLを整形して出力
   * 
   * @param string [$filename]
   * @return mixed ファイル名を省略したときは文字列、指定したときは書き込み結果
   */
  public function asPrettyXML($filename = null) {
      $dom = dom_import_simplexml($this);
      $dom->ownerDocument->formatOutput = true;
      $data = $dom->ownerDocument->saveXML(
          $dom->ownerDocument !== $dom->parentNode ? $dom : null
      );
      return $filename !== null ? (bool)file_put_contents($filename, $data) : $data;
  }

  public static function needCData($data)
  {
    return preg_match('/[<>&"\']/', $data);
  }

  public function addChildOrCData($name, $data, $namespace = null, $namespace_key = null)
  {
    $tagName = $namespace_key != null ? $namespace_key. ':' . $name : $name;
    $encoding = in_array($tagName, self::$CDATAEncoding) || self::needCData($data);

    if ($encoding) {
      return $this->addChildWithCData($name, $data, $namespace == null ? self::NAMESPACE_RSS : $namespace);
    } else {
      return $this->addChild($name, $data, $namespace == null ? self::NAMESPACE_RSS : $namespace);
    }     
  }

  private function hasChildren($namespaces, $element)
  {
    if (count($element->children()) != 0 )  {
      return true;
    }
    foreach($namespaces as $n => $u) {
      if (count($element->children($n, true)) != 0) {
        return true;
      }
    }
    return false;
  }

  private function copyChild($k, $v, $namespace, $namespace_key, $namespaces, $excludeChildren)
  {
    $tagName = $namespace == null ? $k : $namespace_key . ':' . $k;

    if (in_array($tagName, $excludeChildren)) {
      return;
    }
    if ($this->hasChildren($namespaces, $v)) {
      $add = $this->addChild($k, null, $namespace == null ? self::NAMESPACE_RSS : $namespace);
      $add->copyFrom($v, $excludeChildren);
    } else {
      $add = $this->addChildOrCData($k, $v, $namespace, $namespace_key);
    }    
  }

  public function copyFrom($src, $excludeChildren = array())
  {
    $namespaces = $src->getDocNamespaces();   

    foreach ($src->attributes() as $an => $av) {
      $this->addAttribute($an, $av);
    }
    foreach($namespaces as $n => $u) {
      foreach ($src->attributes($u, true) as $an => $av) {
        $this->addAttribute($an, $av, $u);
      }      
    }

    foreach ($src->children() as $k => $v) {
      $this->copyChild($k, $v, null, null, $namespaces, $excludeChildren);
    }

    foreach($namespaces as $n => $u) {
      foreach($src->children($u, false) as $k=> $v) {
        $this->copyChild($k, $v, $u, $n, $namespaces, $excludeChildren);
      }
    }
  }
}
