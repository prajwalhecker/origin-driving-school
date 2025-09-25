<?php
class HomeController extends Controller {
  public function index(): void {
    // You can pass stats or just render
    $this->view("home/index");
  }
}
