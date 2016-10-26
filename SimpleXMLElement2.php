<?php

class SimpleXMLElement2 extends \SimpleXMLElement 
{
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


  public function addChildOrCData($name, $data, $namespace = null, $namespace_key = null)
  {
    $tagName = $namespace_key != null ? $namespace_key. ':' . $name : $name;
    $encoding = in_array($tagName, self::$CDATAEncoding);

    if ($encoding) {
      return $this->addChildWithCData($name, $data, $namespace);
    } else {
      return $this->addChild($name, $data, $namespace);
    }     
  }

  public function copyFrom($src, $excludeChildren = array())
  {
    $namespaces = $this->getDocNamespaces();    
    foreach ($src->children() as $k => $v) {
      if (!in_array($k, $excludeChildren)) {    
        if ($v->count() != 0) {
          $add = $this->addChild($k);
          $add->copyFrom($v,$excludeChildren);
        } else {
          $add = $this->addChildOrCData($k, $v);
        }
        foreach ($v->attributes() as $an => $av) {
          $add->addAttribute($an, $av);
        }
      }
    }

    foreach($namespaces as $n => $u) {
      if ($n == '') {
        continue;
      }
      foreach($src->children($n, true) as $k=> $v) {
        $tagName = $n . ':' . $k;
        if (!in_array($tagName, $excludeChildren)) {
          if ($v->count() != 0) {
            $add = $this->addChild($k, null, $u); 
            $add->copyFrom($v, $excludeChildren);
          } else {
            $add = $this->addChildOrCData($k, $v, $u, $n); 
          }
          foreach ($v->attributes() as $an => $av) {
            $add->addAttribute($an, $av);
          }      
        }
      }
    }
  }
}
