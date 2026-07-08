<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
interface INation{
  public function getCapital();
  public function initialize();
  public function getStates();
  public function search($targetResource);
}