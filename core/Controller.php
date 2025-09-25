<?php
// core/Controller.php
class Controller {
  /** @var PDO */
  protected $db;

  public function __construct() {
    // use the global PDO from config/database.php
    global $pdo;
    if (!($pdo instanceof PDO)) {
      throw new RuntimeException("Database connection not initialised.");
    }
    $this->db = $pdo;
  }

  /** Load a model class from /models and return an instance */
  protected function model(string $name) {
    $path = "../models/$name.php";
    if (!file_exists($path)) {
      throw new RuntimeException("Model $name not found at $path");
    }
    require_once $path;
    if (!class_exists($name)) {
      throw new RuntimeException("Model class $name not found.");
    }
    return new $name($this->db);
  }

  /** Render a view from /views */
  protected function view(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    $path = "../views/$view.php";
    if (!file_exists($path)) {
      http_response_code(404);
      echo "View $view not found.";
      return;
    }
    require "../views/layouts/header.php";
    require $path;
    require "../views/layouts/footer.php";
  }

  /** Session/Access helpers */
  protected function requireLogin(): void {
    @session_start();
    if (empty($_SESSION['user_id'])) {
      $_SESSION['flash_error'] = "Please log in.";
      $this->redirect("index.php?url=auth/login");
    }
  }

  protected function requireRole($roles): void {
    $this->requireLogin();
    @session_start();
    $roles = (array)$roles;
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
      $_SESSION['flash_error'] = "Access denied.";
      $this->redirect("index.php?url=home/index");
    }
  }

  protected function redirect(string $url): void {
    header("Location: $url");
    exit;
  }

  protected function flash(string $key, string $msg): void {
    @session_start();
    $_SESSION[$key] = $msg;
  }

  protected function takeFlash(string $key): ?string {
    @session_start();
    $m = $_SESSION[$key] ?? null;
    if (isset($_SESSION[$key])) unset($_SESSION[$key]);
    return $m;
  }
}
