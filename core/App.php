<?php
// core/App.php
class App {
  public function __construct() {
    $url = $this->parseUrl();
    Router::route($url); // delegate to Router
  }

  private function parseUrl(): array {
    if (!isset($_GET['url']) || $_GET['url'] === '') {
      return ['home', 'index']; // controller HomeController (optional), method index
    }
    $u = rtrim($_GET['url'], '/');
    $u = filter_var($u, FILTER_SANITIZE_URL);
    return explode('/', $u);
  }
}
