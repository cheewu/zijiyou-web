<?php


class IndexController extends MfController
{ 
  public function index()
  {
    $this->url->redirect('/list/region');
  }
}