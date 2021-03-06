<?php

namespace TextocatSDK\Http;

use TextocatSDK\Textocat;

class Batch {
  private $rq;
  private $docs;

  private $id;
  private $status;

  /*
   * Public:
   */

  public function __construct(array $docs, $request) {
    if(count($docs) > Textocat::BATCH_MAX_SIZE) {
      throw new Exception("Max batch size is ".Textocat::BATCH_MAX_SIZE);
    }

    $this->docs = $docs;
    $this->rq = $request;
  }

  public function id() {
    return $this->id;
  }

  public function queue() {
    $resp = $this->rq()->postParamsSet($this->docs)->send('queue');

    $this->id = $resp['batchId'];
    $this->status = $resp['status'];

    return $this;
  }

  public function syncRetrieve($delay = Textocat::DELAY) {
    $this->queue();
    $this->sync($delay);

    return $this->isFinished() ? $this->retrieve() : false;
  }

  public function sync($delay = Textocat::DELAY) {
    do {
      usleep($delay);
      $this->request();
    } while($this->isInProgress());

    return $this;
  }

  public function request() {
    $resp = $this->rq()->getParamAdd($this->idParam())->send('request');

    $this->status = $resp['status'];

    return $this->isFinished();
  }

  public function retrieve() {
    $this->status = '';

    return $this->rq()->getParamAdd($this->idParam())->send('retrieve')['documents'];
  }

  public function update(array $response) {
    $this->id = $response['batchId'];
    $this->status = $response['status'];
  }

  public function isInProgress() {
    return $this->status == 'IN_PROGRESS';
  }

  public function isFinished() {
    return $this->status == 'FINISHED';
  }

  public function isFailed() {
    return $this->status == 'FAILED';
  }

  /*
   * Private:
   */

  private function rq() {
    return clone $this->rq;
  }

  private function idParam() {
    return ['batch_id' => $this->id];
  }
}