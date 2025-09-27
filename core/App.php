<?php
// core/App.php
class App {
  public function __construct() {
    $url = $this->parseUrl();
    Router::route($url); // delegate to Router
  }

  private function parseUrl(): array {
    $raw = $_GET['url'] ?? $_POST['url'] ?? '';

    if ($raw === '') {
      return ['home', 'index']; // controller HomeController (optional), method index
    }

    $u = rtrim($raw, '/');
    $u = filter_var($u, FILTER_SANITIZE_URL);
    return explode('/', $u);
  }
}
