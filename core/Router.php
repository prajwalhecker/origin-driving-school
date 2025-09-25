<?php
// core/Router.php
class Router {
  public static function route(array $parts): void {
    // controller name
    $controller = ucfirst($parts[0] ?? 'home') . "Controller";
    $method     = $parts[1] ?? 'index';
    $params     = array_slice($parts, 2);

    $file = "../controllers/$controller.php";
    if (!file_exists($file)) {
      // fallback to home
      if (file_exists("../views/home/index.php")) {
        require "../views/home/index.php";
        return;
      }
      http_response_code(404);
      echo "Controller not found.";
      return;
    }

    require_once $file;
    if (!class_exists($controller)) {
      http_response_code(500);
      echo "Controller class $controller not found.";
      return;
    }

    $instance = new $controller();

    if (!method_exists($instance, $method)) {
      // default to index() if method missing
      $method = "index";
      if (!method_exists($instance, $method)) {
        http_response_code(404);
        echo "Method not found.";
        return;
      }
    }

    // call controller method with params
    call_user_func_array([$instance, $method], $params);
  }
}
