<?php

namespace Qweb\Request;

class JsonRequest extends Request {
  protected $header = [
    'Content-type: application/json',
    'Accept: application/json'
  ];

  protected $dataEncFn = 'json_encode';

  public function send($extraPath = '', $javishArrays = false) {
    return json_decode(parent::send($extraPath, $javishArrays)->body, true);
  }
}