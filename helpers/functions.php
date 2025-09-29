<?php
/**
 * Generic helper functions (safe output, CSRF, old input, uploads, pagination, etc.)
 * Include this in header.php (once) or in public/index.php after config.
 */

// ---------- BASIC SANITISERS ----------
function e($value): string {                 // Escape for HTML
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string {
  $normalized = ltrim($path, '/');
  $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
  $scriptDir = str_replace('\\', '/', dirname($scriptName));

  if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
    $scriptDir = '';
  }

  $base = rtrim($scriptDir, '/');
  $prefix = $base === '' ? '/assets/' : $base . '/assets/';

  return $prefix . $normalized;
}

function clean($value): string {             // Trim + strip tags (for plain inputs)
  return trim(strip_tags((string)$value));
}

function post(string $key, $default = null) {
  return $_POST[$key] ?? $default;
}

function get(string $key, $default = null) {
  return $_GET[$key] ?? $default;
}

// ---------- SESSIONS / FLASH ----------
function session_start_safe(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function flash_set(string $key, string $msg): void {
  session_start_safe();
  $_SESSION[$key] = $msg;
}

function flash_get(string $key): ?string {
  session_start_safe();
  if (!isset($_SESSION[$key])) return null;
  $msg = $_SESSION[$key];
  unset($_SESSION[$key]);
  return $msg;
}

// ---------- CSRF PROTECTION ----------
function csrf_token(): string {
  session_start_safe();
  if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['_csrf'];
}

function csrf_field(): string {
  $t = csrf_token();
  return '<input type="hidden" name="_csrf" value="'.e($t).'">';
}

function csrf_check(): bool {
  session_start_safe();
  $ok = isset($_POST['_csrf'], $_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $_POST['_csrf']);
  if (!$ok) {
    http_response_code(419); // auth/timeout-like
  }
  return $ok;
}

// ---------- OLD INPUT (sticky forms) ----------
function old(string $key, $default = '') {
  session_start_safe();
  if (isset($_SESSION['_old_input'][$key])) {
    return $_SESSION['_old_input'][$key];
  }
  return $default;
}

function remember_old_input(): void {
  session_start_safe();
  $_SESSION['_old_input'] = $_POST ?? [];
}

function clear_old_input(): void {
  session_start_safe();
  unset($_SESSION['_old_input']);
}

// ---------- REDIRECT ----------
function redirect(string $url): void {
  header("Location: $url");
  exit;
}

// ---------- RANDOMS ----------
function str_random(int $len = 16): string {
  return substr(bin2hex(random_bytes($len)), 0, $len);
}

// ---------- DATE/TIME ----------
function dt_human($dt): string {
  if (!$dt) return '';
  return date('Y-m-d H:i', is_numeric($dt) ? (int)$dt : strtotime($dt));
}

// ---------- FILE UPLOADS ----------
/**
 * Upload a file safely.
 * @param string $inputName  HTML file input name
 * @param string $destFolder Destination path (absolute or relative to project root)
 * @param array $allowedExts e.g. ['pdf','jpg','png']
 * @param int $maxBytes      e.g. 5 * 1024 * 1024 for 5MB
 * @return array {ok:bool, path?:string, error?:string, name?:string}
 */
function upload_file(string $inputName, string $destFolder, array $allowedExts = ['pdf','png','jpg','jpeg'], int $maxBytes = 5242880): array {
  if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
    return ['ok' => false, 'error' => 'No file uploaded.'];
  }

  $f = $_FILES[$inputName];
  if ($f['error'] !== UPLOAD_ERR_OK) {
    return ['ok' => false, 'error' => 'Upload error code: '.$f['error']];
  }

  if ($f['size'] > $maxBytes) {
    return ['ok' => false, 'error' => 'File too large.'];
  }

  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExts, true)) {
    return ['ok' => false, 'error' => 'Invalid file type.'];
  }

  // Avoid executing files from uploads (Apache .htaccess is also recommended)
  $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($f['name']));
  $newName  = time() . '_' . str_random(6) . '.' . $ext;

  // Make sure dest folder exists
  if (!is_dir($destFolder)) @mkdir($destFolder, 0775, true);

  $target = rtrim($destFolder, '/\\') . DIRECTORY_SEPARATOR . $newName;
  if (!move_uploaded_file($f['tmp_name'], $target)) {
    return ['ok' => false, 'error' => 'Could not move file.'];
  }

  // Permissions (optional, depends on OS)
  @chmod($target, 0644);
  return ['ok' => true, 'path' => $target, 'name' => $newName];
}

// ---------- PAGINATION ----------
/**
 * Return pagination info + items (simple)
 * @param PDO $db
 * @param string $baseSql   "FROM table WHERE ..."
 * @param array $params     bound params for WHERE
 * @param int $page         current page (>=1)
 * @param int $perPage      per-page count
 * @param string $orderBy   e.g. "created_at DESC"
 * @return array {items, page, pages, total}
 */
function paginate(PDO $db, string $baseSql, array $params, int $page = 1, int $perPage = 10, string $orderBy = 'id DESC'): array {
  $page = max(1, $page);
  $offset = ($page - 1) * $perPage;

  // count
  $cntStmt = $db->prepare("SELECT COUNT(*) ".$baseSql);
  $cntStmt->execute($params);
  $total = (int)$cntStmt->fetchColumn();

  // items
  $itemsStmt = $db->prepare("SELECT * ".$baseSql." ORDER BY $orderBy LIMIT :lim OFFSET :off");
  foreach ($params as $i => $v) {
    // positional params only
    $itemsStmt->bindValue($i+1, $v);
  }
  $itemsStmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
  $itemsStmt->bindValue(':off', $offset, PDO::PARAM_INT);
  $itemsStmt->execute();
  $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

  $pages = (int)ceil($total / $perPage);
  return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
}

/** Render simple Bootstrap pagination */
function render_pagination(string $baseUrl, int $page, int $pages): string {
  if ($pages <= 1) return '';
  $html = '<nav><ul class="pagination">';
  for ($p = 1; $p <= $pages; $p++) {
    $active = $p === $page ? ' active' : '';
    $html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.e($baseUrl).'&page='.$p.'">'.$p.'</a></li>';
  }
  $html .= '</ul></nav>';
  return $html;
}
