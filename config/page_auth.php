<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
start_app_session();
$csrfToken = issue_csrf_token();
setcookie('TYTCSRF',$csrfToken,['expires'=>0,'path'=>session_get_cookie_params()['path'],'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>false,'samesite'=>'Strict']);
$pageUser = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = db()->prepare('SELECT u.id,u.nombre,u.correo,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id=? AND u.activo=1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $pageUser = $stmt->fetch() ?: null;
}
if (!$pageUser) {
    $count = (int)db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    header('Location: ' . ($count === 0 ? 'setup_admin.php' : 'login.php'));
    exit;
}
$page = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($pageUser['rol'] === 'vendedor' && in_array($page, ['proveedores.php','compras.php','creditos.php','catalogos.php','reportes.php','usuarios.php'], true)) {
    http_response_code(403);
    echo 'No tienes permiso para acceder a esta sección.';
    exit;
}
