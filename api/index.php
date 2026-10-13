<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
date_default_timezone_set('America/Bogota');
start_app_session();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

function respond(mixed $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function body(): array {
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_POST;
}
function required(array $data, array $keys): void {
    foreach ($keys as $key) if (!isset($data[$key]) || $data[$key] === '') respond(['error' => "Falta el campo requerido: $key"], 422);
}

try {
    $pdo = db();
    $parts = array_values(array_filter(explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'))));
    $apiPos = array_search('api', $parts, true);
    $resource = $parts[$apiPos + 1] ?? '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($parts[$apiPos + 2]) ? (int)$parts[$apiPos + 2] : null);
    $method = $_SERVER['REQUEST_METHOD'];
    $input = body();
    $action = $parts[$apiPos + 2] ?? '';

    if ($resource === 'auth' && $action === 'setup-status' && $method === 'GET') {
        respond(['needs_setup' => (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0]);
    }
    if ($resource === 'auth' && $action === 'setup' && $method === 'POST') {
        if ((int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() !== 0) respond(['error'=>'La configuración inicial ya se realizó.'],409);
        required($input,['nombre','correo','password']);
        if (!filter_var($input['correo'],FILTER_VALIDATE_EMAIL) || strlen((string)$input['password']) < 8) respond(['error'=>'Usa un correo válido y una contraseña de al menos 8 caracteres.'],422);
        $roleId=(int)$pdo->query("SELECT id FROM roles WHERE nombre='administrador'")->fetchColumn();
        if (!$roleId) respond(['error'=>'Faltan los roles. Importa la migración de base de datos.'],500);
        $s=$pdo->prepare('INSERT INTO usuarios(nombre,correo,password_hash,rol_id) VALUES(?,?,?,?)');
        $s->execute([trim($input['nombre']),strtolower(trim($input['correo'])) ,password_hash($input['password'],PASSWORD_DEFAULT),$roleId]);
        session_regenerate_id(true); $_SESSION['user_id']=(int)$pdo->lastInsertId(); $token=issue_csrf_token();
        setcookie('TYTCSRF',$token,['expires'=>0,'path'=>session_get_cookie_params()['path'],'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>false,'samesite'=>'Strict']);
        respond(['user'=>['id'=>$_SESSION['user_id'],'nombre'=>trim($input['nombre']),'correo'=>trim($input['correo']),'rol'=>'administrador'],'csrf_token'=>$token],201);
    }
    if ($resource === 'auth' && $action === 'login' && $method === 'POST') {
        required($input,['correo','password']);
        $s=$pdo->prepare('SELECT u.id,u.nombre,u.correo,u.password_hash,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.correo=? AND u.activo=1 LIMIT 1');
        $s->execute([strtolower(trim($input['correo']))]); $user=$s->fetch();
        if (!$user || !password_verify($input['password'],$user['password_hash'])) respond(['error'=>'Correo o contraseña incorrectos.'],401);
        session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $token=issue_csrf_token();
        setcookie('TYTCSRF',$token,['expires'=>0,'path'=>session_get_cookie_params()['path'],'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>false,'samesite'=>'Strict']);
        unset($user['password_hash']); respond(['user'=>$user,'csrf_token'=>$token]);
    }
    if ($resource === 'auth' && $action === 'session' && $method === 'GET') {
        $user=null;
        if (!empty($_SESSION['user_id'])) { $s=$pdo->prepare('SELECT u.id,u.nombre,u.correo,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id=? AND u.activo=1');$s->execute([(int)$_SESSION['user_id']]);$user=$s->fetch()?:null; }
        if ($user) { $token=issue_csrf_token();setcookie('TYTCSRF',$token,['expires'=>0,'path'=>session_get_cookie_params()['path'],'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>false,'samesite'=>'Strict']); }
        respond(['user'=>$user,'needs_setup'=>(int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn()===0,'csrf_token'=>$user?issue_csrf_token():null]);
    }
    if ($resource === 'auth' && $action === 'logout' && $method === 'POST') {
        if (empty($_SESSION['user_id'])) respond(['logged_out'=>true]);
        $provided=$_SERVER['HTTP_X_CSRF_TOKEN']??''; if (!$provided || !hash_equals((string)($_SESSION['csrf_token']??''),$provided)) respond(['error'=>'Token de seguridad inválido. Recarga la página e intenta otra vez.'],419);
        $_SESSION=[]; session_regenerate_id(true); session_destroy(); setcookie('TYTCSRF','',['expires'=>time()-3600,'path'=>session_get_cookie_params()['path'],'samesite'=>'Strict']); respond(['logged_out'=>true]);
    }

    $currentUser=null;
    if (!empty($_SESSION['user_id'])) { $s=$pdo->prepare('SELECT u.id,u.nombre,u.correo,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id=? AND u.activo=1');$s->execute([(int)$_SESSION['user_id']]);$currentUser=$s->fetch()?:null; }
    if (!$currentUser) respond(['error'=>'Debes iniciar sesión.'],401);
    if (!in_array($currentUser['rol'],['administrador','vendedor'],true)) respond(['error'=>'Rol no válido.'],403);
    if (!in_array($method,['GET','HEAD','OPTIONS'],true)) {
        $provided=$_SERVER['HTTP_X_CSRF_TOKEN']??''; if (!$provided || !hash_equals((string)($_SESSION['csrf_token']??''),$provided)) respond(['error'=>'Token de seguridad inválido. Recarga la página e intenta otra vez.'],419);
    }
    $isAdmin=$currentUser['rol']==='administrador';
    if (!$isAdmin && (in_array($resource,['proveedores','compras','creditos','catalogos','reportes','usuarios'],true) || ($resource==='productos' && $method!=='GET') || ($resource==='inventario' && $method!=='GET') || ($resource==='dashboard' && $method!=='GET'))) respond(['error'=>'No tienes permiso para realizar esta operación.'],403);

    if ($resource === 'health') respond(['ok' => true, 'database' => DB_NAME]);

    if ($resource === 'usuarios') {
        if ($method==='GET') respond($pdo->query('SELECT u.id,u.nombre,u.correo,r.nombre AS rol,u.activo FROM usuarios u JOIN roles r ON r.id=u.rol_id ORDER BY u.nombre')->fetchAll());
        if ($method==='POST') { required($input,['nombre','correo','password','rol']); if(!filter_var($input['correo'],FILTER_VALIDATE_EMAIL)||strlen((string)$input['password'])<8)respond(['error'=>'Revisa el correo y usa una contraseña de al menos 8 caracteres.'],422); if(!in_array($input['rol'],['administrador','vendedor'],true))respond(['error'=>'Rol inválido.'],422);$s=$pdo->prepare('SELECT id FROM roles WHERE nombre=?');$s->execute([$input['rol']]);$roleId=$s->fetchColumn();if(!$roleId)respond(['error'=>'Rol no encontrado.'],422);$s=$pdo->prepare('INSERT INTO usuarios(nombre,correo,password_hash,rol_id) VALUES(?,?,?,?)');$s->execute([trim($input['nombre']),strtolower(trim($input['correo'])),password_hash($input['password'],PASSWORD_DEFAULT),(int)$roleId]);respond(['id'=>(int)$pdo->lastInsertId()],201); }
        if ($method==='PUT' && $id) { required($input,['activo']);$s=$pdo->prepare('UPDATE usuarios SET activo=? WHERE id=? AND id<>?');$s->execute([(int)(bool)$input['activo'],$id,(int)$currentUser['id']]);respond(['updated'=>$s->rowCount()]); }
    }

    if ($resource === 'catalogos') {
        $type = $_GET['tipo'] ?? '';
        $tables = ['categorias' => ['categorias','nombre, descripcion'], 'marcas' => ['marcas','nombre, descripcion'], 'tallas' => ['tallas','etiqueta, tipo']];
        if (!isset($tables[$type])) respond(['error'=>'Tipo de catálogo inválido'], 404);
        [$table, $columns] = $tables[$type];
        if ($method === 'GET') respond($pdo->query("SELECT id, $columns FROM $table ORDER BY id")->fetchAll());
        if ($method === 'POST') {
            $fields = $type === 'tallas' ? ['etiqueta','tipo'] : ['nombre','descripcion']; required($input, [$fields[0]]);
            $stmt = $pdo->prepare("INSERT INTO $table (".implode(',', $fields).") VALUES (".implode(',', array_fill(0,count($fields),'?')).")");
            $stmt->execute(array_map(fn($f)=>$input[$f] ?? '', $fields)); respond(['id'=>(int)$pdo->lastInsertId()], 201);
        }
        if ($method === 'PUT' && $id) {
            $fields = $type === 'tallas' ? ['etiqueta','tipo'] : ['nombre','descripcion']; required($input, [$fields[0]]);
            $stmt=$pdo->prepare("UPDATE $table SET ".implode('=?,', $fields).'=? WHERE id=?'); $stmt->execute([...array_map(fn($f)=>$input[$f] ?? '',$fields),$id]); respond(['updated'=>$stmt->rowCount()]);
        }
        if ($method === 'DELETE' && $id) { $stmt=$pdo->prepare("DELETE FROM $table WHERE id=?"); $stmt->execute([$id]); respond(['deleted'=>$stmt->rowCount()]); }
    }

    if ($resource === 'proveedores') {
        if ($method === 'GET') respond($pdo->query('SELECT id,nombre,contacto,telefono,correo,ciudad,nit FROM proveedores ORDER BY nombre')->fetchAll());
        if ($method === 'POST' || $method === 'PUT') {
            required($input,['nombre','contacto','telefono','correo','ciudad','nit']);
            if ($method==='POST') { $s=$pdo->prepare('INSERT INTO proveedores(nombre,contacto,telefono,correo,ciudad,nit) VALUES(?,?,?,?,?,?)'); $s->execute(array_values(array_intersect_key($input,array_flip(['nombre','contacto','telefono','correo','ciudad','nit'])))); respond(['id'=>(int)$pdo->lastInsertId()],201); }
            if (!$id) respond(['error'=>'ID requerido'],422); $s=$pdo->prepare('UPDATE proveedores SET nombre=?,contacto=?,telefono=?,correo=?,ciudad=?,nit=? WHERE id=?'); $s->execute([$input['nombre'],$input['contacto'],$input['telefono'],$input['correo'],$input['ciudad'],$input['nit'],$id]); respond(['updated'=>$s->rowCount()]);
        }
        if ($method==='DELETE' && $id) { $s=$pdo->prepare('DELETE FROM proveedores WHERE id=?'); $s->execute([$id]); respond(['deleted'=>$s->rowCount()]); }
    }

    if ($resource === 'productos') {
        if ($method === 'GET') respond($pdo->query('SELECT p.id,p.sku,p.nombre,p.descripcion,p.categoria_id,p.marca_id,p.proveedor_id,p.precio_compra,p.precio_venta,p.activo,c.nombre AS categoria,m.nombre AS marca FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id LEFT JOIN marcas m ON m.id=p.marca_id ORDER BY p.nombre')->fetchAll());
        if ($method === 'POST' || $method === 'PUT') {
            required($input,['sku','nombre','precio_compra','precio_venta']);
            if ((int)$input['precio_compra']<0 || (int)$input['precio_venta']<0) respond(['error'=>'Los precios no pueden ser negativos.'],422);
            $values=[$input['sku'],$input['nombre'],$input['descripcion']??'',$input['categoria_id']?:null,$input['marca_id']?:null,$input['proveedor_id']?:null,(int)$input['precio_compra'],(int)$input['precio_venta']];
            if ($method==='POST') { $s=$pdo->prepare('INSERT INTO productos(sku,nombre,descripcion,categoria_id,marca_id,proveedor_id,precio_compra,precio_venta) VALUES(?,?,?,?,?,?,?,?)');$s->execute($values);respond(['id'=>(int)$pdo->lastInsertId()],201); }
            if (!$id) respond(['error'=>'ID requerido'],422);$s=$pdo->prepare('UPDATE productos SET sku=?,nombre=?,descripcion=?,categoria_id=?,marca_id=?,proveedor_id=?,precio_compra=?,precio_venta=?,activo=? WHERE id=?');$s->execute([...$values,(int)($input['activo']??1),$id]);respond(['updated'=>$s->rowCount()]);
        }
        if ($method==='DELETE' && $id) { $s=$pdo->prepare('UPDATE productos SET activo=0 WHERE id=?');$s->execute([$id]);respond(['deactivated'=>$s->rowCount()]); }
    }

    if ($resource === 'inventario') {
        if ($method==='GET' && ($parts[$apiPos+2] ?? '')==='movimientos') respond($pdo->query('SELECT m.id,m.tipo AS type,p.nombre AS product,v.talla AS size,v.color,m.nota AS note,m.cantidad AS qty,m.fecha AS created_at,u.nombre AS usuario FROM movimientos_inventario m JOIN variantes v ON v.id=m.variante_id JOIN productos p ON p.id=v.producto_id LEFT JOIN usuarios u ON u.id=m.usuario_id ORDER BY m.fecha DESC LIMIT 20')->fetchAll());
        if ($method==='GET') respond($pdo->query('SELECT v.id,v.producto_id,p.nombre AS product,p.activo,c.nombre AS category,v.talla AS size,v.color,v.stock,v.stock_minimo AS min,p.precio_venta AS price,p.precio_compra AS purchase_price FROM variantes v JOIN productos p ON p.id=v.producto_id LEFT JOIN categorias c ON c.id=p.categoria_id ORDER BY p.nombre,v.talla')->fetchAll());
        if ($method==='POST' && ($parts[$apiPos+2] ?? '')==='variantes') { required($input,['producto_id','talla','color','stock_minimo']); $s=$pdo->prepare('INSERT INTO variantes(producto_id,talla,color,stock,stock_minimo) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE stock=stock+VALUES(stock),stock_minimo=VALUES(stock_minimo)');$s->execute([(int)$input['producto_id'],$input['talla'],$input['color'],max(0,(int)($input['stock']??0)),(int)$input['stock_minimo']]);respond(['saved'=>true],201); }
        if ($method==='POST' && ($parts[$apiPos+2] ?? '')==='movimientos') {
            required($input,['variante_id','tipo','cantidad']);
            if (!in_array($input['tipo'],['entrada','salida','ajuste'],true) || (int)$input['cantidad']<0) respond(['error'=>'Movimiento inválido'],422);
            $pdo->beginTransaction(); $s=$pdo->prepare('SELECT stock FROM variantes WHERE id=? FOR UPDATE'); $s->execute([(int)$input['variante_id']]); $stock=$s->fetchColumn(); if($stock===false){$pdo->rollBack();respond(['error'=>'Variante no encontrada'],404);}
            $qty=(int)$input['cantidad']; $new=$input['tipo']==='entrada'?(int)$stock+$qty:($input['tipo']==='salida'?(int)$stock-$qty:$qty); if($new<0){$pdo->rollBack();respond(['error'=>'Stock insuficiente'],422);}
            $pdo->prepare('UPDATE variantes SET stock=? WHERE id=?')->execute([$new,(int)$input['variante_id']]);
            $pdo->prepare('INSERT INTO movimientos_inventario(variante_id,tipo,cantidad,nota,usuario_id) VALUES(?,?,?,?,?)')->execute([(int)$input['variante_id'],$input['tipo'],$qty,$input['nota']??'Movimiento manual',(int)$currentUser['id']]); $pdo->commit(); respond(['stock'=>$new],201);
        }
    }

    if ($resource === 'ventas') {
        if ($method==='GET') respond($pdo->query('SELECT v.id,v.codigo AS sale_code,v.fecha AS created_at,v.cliente AS client,v.vendedor AS seller,v.items_count AS items,v.descuento AS discount,v.total,v.metodo_pago AS method,cc.fecha_vencimiento AS due_date,cc.saldo_original-COALESCE(SUM(pc.monto),0) AS balance FROM ventas v LEFT JOIN cuentas_cobrar cc ON cc.venta_id=v.id LEFT JOIN pagos_cobrar pc ON pc.cuenta_id=cc.id GROUP BY v.id ORDER BY v.fecha DESC')->fetchAll());
        if ($method==='POST') {
            required($input,['client','discount','method','items']);
            if(!in_array($input['method'],['tarjeta','efectivo','transferencia','credito'],true))respond(['error'=>'Método de pago inválido.'],422);
            if($input['method']==='credito') required($input,['due_date']);
            if($input['method']==='credito'&&!trim((string)$input['client']))respond(['error'=>'Escribe el nombre del cliente para la venta a crédito.'],422);
            if(!is_array($input['items'])||count($input['items'])<1||count($input['items'])>50)respond(['error'=>'Agrega entre 1 y 50 productos a la venta.'],422);
            $cart=[];foreach($input['items'] as $line){$variant=(int)($line['variante_id']??0);$qty=(int)($line['cantidad']??0);if($variant<1||$qty<1)respond(['error'=>'Cada producto debe tener una variante y una cantidad válida.'],422);$cart[$variant]=($cart[$variant]??0)+$qty;}
            $discount=(int)$input['discount'];if($discount<0)respond(['error'=>'El descuento no puede ser negativo.'],422);
            $pdo->beginTransaction();$items=[];$subtotal=0;$quantityTotal=0;
            foreach($cart as $variantId=>$qty){$s=$pdo->prepare('SELECT v.stock,v.talla,v.color,p.id AS producto_id,p.nombre,p.precio_venta,p.precio_compra,p.activo FROM variantes v JOIN productos p ON p.id=v.producto_id WHERE v.id=? FOR UPDATE');$s->execute([$variantId]);$row=$s->fetch();if(!$row){$pdo->rollBack();respond(['error'=>'Uno de los productos ya no existe.'],404);}if(!(int)$row['activo']){$pdo->rollBack();respond(['error'=>'Uno de los productos está inactivo.'],422);}if((int)$row['stock']<$qty){$pdo->rollBack();respond(['error'=>"Stock insuficiente para {$row['nombre']} ({$row['talla']} / {$row['color']})."],422);}$lineTotal=(int)$row['precio_venta']*$qty;$subtotal+=$lineTotal;$quantityTotal+=$qty;$items[]=['variante_id'=>$variantId,'cantidad'=>$qty,'precio'=>(int)$row['precio_venta'],'subtotal'=>$lineTotal,'nombre'=>$row['nombre']];}
            if($discount>$subtotal){$pdo->rollBack();respond(['error'=>'El descuento no puede superar el subtotal.'],422);}
            $total=$subtotal-$discount;
            $year=(int)date('Y');$pdo->prepare('INSERT IGNORE INTO consecutivos_factura(anio,ultimo_numero) VALUES(?,0)')->execute([$year]);$seq=$pdo->prepare('SELECT ultimo_numero FROM consecutivos_factura WHERE anio=? FOR UPDATE');$seq->execute([$year]);$number=(int)$seq->fetchColumn()+1;$pdo->prepare('UPDATE consecutivos_factura SET ultimo_numero=? WHERE anio=?')->execute([$number,$year]);$code=sprintf('FAC-%d-%04d',$year,$number);
            $s=$pdo->prepare('INSERT INTO ventas(codigo,cliente,vendedor,items_count,descuento,total,metodo_pago,usuario_id) VALUES(?,?,?,?,?,?,?,?)');$s->execute([$code,trim((string)$input['client']),$currentUser['nombre'],$quantityTotal,$discount,$total,$input['method'],(int)$currentUser['id']]);$saleId=(int)$pdo->lastInsertId();
            if($input['method']==='credito'){
                $due=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$input['due_date']);if(!$due||$due->format('Y-m-d')!==$input['due_date']||$due->format('Y-m-d')<date('Y-m-d')){$pdo->rollBack();respond(['error'=>'La fecha de vencimiento debe ser válida y no anterior a hoy.'],422);}
                $paid=(int)($input['paid_amount']??0);if($paid<0||$paid>$total){$pdo->rollBack();respond(['error'=>'El abono inicial debe estar entre cero y el total.'],422);}$pdo->prepare('INSERT INTO cuentas_cobrar(venta_id,cliente,saldo_original,fecha_vencimiento) VALUES(?,?,?,?)')->execute([$saleId,trim((string)$input['client']),$total,$due->format('Y-m-d')]);$arId=(int)$pdo->lastInsertId();if($paid>0)$pdo->prepare('INSERT INTO pagos_cobrar(cuenta_id,monto,usuario_id) VALUES(?,?,?)')->execute([$arId,$paid,(int)$currentUser['id']]);
            }
            $detail=$pdo->prepare('INSERT INTO detalle_venta(venta_id,variante_id,cantidad,precio_unitario,subtotal) VALUES(?,?,?,?,?)');$stockUpdate=$pdo->prepare('UPDATE variantes SET stock=stock-? WHERE id=?');$movement=$pdo->prepare("INSERT INTO movimientos_inventario(variante_id,tipo,cantidad,nota,usuario_id) VALUES(?,'salida',?,?,?)");
            foreach($items as $line){$detail->execute([$saleId,$line['variante_id'],$line['cantidad'],$line['precio'],$line['subtotal']]);$stockUpdate->execute([$line['cantidad'],$line['variante_id']]);$movement->execute([$line['variante_id'],$line['cantidad'],'Venta '.$code,(int)$currentUser['id']]);}
            $pdo->commit();respond(['id'=>$saleId,'code'=>$code,'subtotal'=>$subtotal,'total'=>$total],201);
        }
    }

    if ($resource === 'creditos') {
        $kind=$_GET['tipo']??'';
        if(!in_array($kind,['cobrar','pagar'],true))respond(['error'=>'Tipo de cuenta inválido.'],422);
        $table=$kind==='cobrar'?'cuentas_cobrar':'cuentas_pagar';$payments=$kind==='cobrar'?'pagos_cobrar':'pagos_pagar';
        if($method==='GET'&&$action==='abonos'){
            $accountId=(int)($_GET['cuenta_id']??0);if($accountId<1)respond(['error'=>'Cuenta inválida.'],422);
            $check=$pdo->prepare("SELECT id FROM {$table} WHERE id=?");$check->execute([$accountId]);if(!$check->fetchColumn())respond(['error'=>'Cuenta no encontrada.'],404);
            $s=$pdo->prepare("SELECT p.id,p.monto,p.fecha,u.nombre AS usuario FROM {$payments} p LEFT JOIN usuarios u ON u.id=p.usuario_id WHERE p.cuenta_id=? ORDER BY p.fecha DESC,p.id DESC");$s->execute([$accountId]);respond($s->fetchAll());
        }
        if($method==='GET'){
            if($kind==='cobrar')$sql="SELECT c.id,c.cliente AS tercero,c.fecha_vencimiento,c.saldo_original,COALESCE(SUM(p.monto),0) abonado,c.saldo_original-COALESCE(SUM(p.monto),0) saldo,v.codigo documento,v.fecha FROM cuentas_cobrar c JOIN ventas v ON v.id=c.venta_id LEFT JOIN pagos_cobrar p ON p.cuenta_id=c.id GROUP BY c.id ORDER BY (c.fecha_vencimiento<CURDATE()) DESC,c.fecha_vencimiento";
            else $sql="SELECT c.id,pr.nombre AS tercero,c.fecha_vencimiento,c.saldo_original,COALESCE(SUM(p.monto),0) abonado,c.saldo_original-COALESCE(SUM(p.monto),0) saldo,co.codigo documento,co.fecha FROM cuentas_pagar c JOIN compras co ON co.id=c.compra_id JOIN proveedores pr ON pr.id=co.proveedor_id LEFT JOIN pagos_pagar p ON p.cuenta_id=c.id GROUP BY c.id ORDER BY (c.fecha_vencimiento<CURDATE()) DESC,c.fecha_vencimiento";
            respond($pdo->query($sql)->fetchAll());
        }
        if($method==='POST'&&$action==='abonos'){
            required($input,['cuenta_id','monto']);$amount=(int)$input['monto'];if($amount<1)respond(['error'=>'El abono debe ser mayor a cero.'],422);
            $pdo->beginTransaction();$s=$pdo->prepare("SELECT saldo_original FROM {$table} WHERE id=? FOR UPDATE");$s->execute([(int)$input['cuenta_id']]);$original=$s->fetchColumn();if($original===false){$pdo->rollBack();respond(['error'=>'Cuenta no encontrada.'],404);}$paid=$pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM {$payments} WHERE cuenta_id=?");$paid->execute([(int)$input['cuenta_id']]);$balance=(int)$original-(int)$paid->fetchColumn();if($amount>$balance){$pdo->rollBack();respond(['error'=>'El abono supera el saldo pendiente.'],422);}$pdo->prepare("INSERT INTO {$payments}(cuenta_id,monto,usuario_id) VALUES(?,?,?)")->execute([(int)$input['cuenta_id'],$amount,(int)$currentUser['id']]);$pdo->commit();respond(['saldo'=>$balance-$amount],201);
        }
    }

    if ($resource === 'compras') {
        if($method==='GET')respond($pdo->query("SELECT co.id,co.codigo,co.fecha,pr.nombre proveedor,co.total,co.metodo_pago,co.fecha_vencimiento,CASE WHEN co.metodo_pago='credito' THEN co.total-COALESCE(SUM(pp.monto),0) ELSE 0 END saldo FROM compras co JOIN proveedores pr ON pr.id=co.proveedor_id LEFT JOIN cuentas_pagar cp ON cp.compra_id=co.id LEFT JOIN pagos_pagar pp ON pp.cuenta_id=cp.id GROUP BY co.id ORDER BY co.fecha DESC")->fetchAll());
        if($method==='POST'){
            required($input,['proveedor_id','method','items']);if(!in_array($input['method'],['efectivo','transferencia','tarjeta','credito'],true))respond(['error'=>'Método de pago inválido.'],422);if($input['method']==='credito')required($input,['due_date']);if(!is_array($input['items'])||!count($input['items']))respond(['error'=>'Agrega productos a la compra.'],422);
            $pdo->beginTransaction();$items=[];$total=0;$year=(int)date('Y');$pdo->prepare('INSERT IGNORE INTO consecutivos_compra(anio,ultimo_numero) VALUES(?,0)')->execute([$year]);$seq=$pdo->prepare('SELECT ultimo_numero FROM consecutivos_compra WHERE anio=? FOR UPDATE');$seq->execute([$year]);$number=(int)$seq->fetchColumn()+1;$pdo->prepare('UPDATE consecutivos_compra SET ultimo_numero=? WHERE anio=?')->execute([$number,$year]);$code=sprintf('COM-%d-%04d',$year,$number);
            foreach($input['items'] as $line){$variant=(int)($line['variante_id']??0);$qty=(int)($line['cantidad']??0);$cost=(int)($line['precio']??0);if($variant<1||$qty<1||$cost<0){$pdo->rollBack();respond(['error'=>'Revisa producto, cantidad y costo.'],422);}$s=$pdo->prepare('SELECT id FROM variantes WHERE id=? FOR UPDATE');$s->execute([$variant]);if(!$s->fetchColumn()){$pdo->rollBack();respond(['error'=>'Variante no encontrada.'],404);}$total+=$qty*$cost;$items[]=[$variant,$qty,$cost];}
            $due=null;if($input['method']==='credito'){$date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$input['due_date']);if(!$date||$date->format('Y-m-d')!==$input['due_date']||$date->format('Y-m-d')<date('Y-m-d')){$pdo->rollBack();respond(['error'=>'Fecha de vencimiento inválida.'],422);}$due=$date->format('Y-m-d');}
            $pdo->prepare('INSERT INTO compras(codigo,proveedor_id,total,metodo_pago,fecha_vencimiento,usuario_id) VALUES(?,?,?,?,?,?)')->execute([$code,(int)$input['proveedor_id'],$total,$input['method'],$due,(int)$currentUser['id']]);$purchaseId=(int)$pdo->lastInsertId();$detail=$pdo->prepare('INSERT INTO detalle_compra(compra_id,variante_id,cantidad,precio_unitario,subtotal) VALUES(?,?,?,?,?)');$stock=$pdo->prepare('UPDATE variantes SET stock=stock+? WHERE id=?');$move=$pdo->prepare("INSERT INTO movimientos_inventario(variante_id,tipo,cantidad,nota,usuario_id) VALUES(?,'entrada',?,?,?)");foreach($items as [$variant,$qty,$cost]){$detail->execute([$purchaseId,$variant,$qty,$cost,$qty*$cost]);$stock->execute([$qty,$variant]);$move->execute([$variant,$qty,'Compra '.$code,(int)$currentUser['id']]);}
            if($input['method']==='credito')$pdo->prepare('INSERT INTO cuentas_pagar(compra_id,saldo_original,fecha_vencimiento) VALUES(?,?,?)')->execute([$purchaseId,$total,$due]);$pdo->commit();respond(['id'=>$purchaseId,'code'=>$code,'total'=>$total],201);
        }
    }

    if ($resource === 'dashboard' && $method==='GET') {
        $today=$pdo->query('SELECT COALESCE(SUM(total),0) total,COUNT(*) n FROM ventas WHERE fecha>=CURDATE() AND fecha<CURDATE()+INTERVAL 1 DAY')->fetch();$month=$pdo->query('SELECT COALESCE(SUM(total),0) total,COUNT(*) n FROM ventas WHERE fecha>=DATE_FORMAT(CURDATE(),\'%Y-%m-01\') AND fecha<DATE_FORMAT(CURDATE()+INTERVAL 1 MONTH,\'%Y-%m-01\')')->fetch();
        respond(['today'=>(int)$today['total'],'today_count'=>(int)$today['n'],'month'=>(int)$month['total'],'month_count'=>(int)$month['n'],'products'=>(int)$pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1')->fetchColumn(),'variants'=>(int)$pdo->query('SELECT COUNT(*) FROM variantes')->fetchColumn(),'low_stock'=>(int)$pdo->query('SELECT COUNT(*) FROM variantes WHERE stock <= stock_minimo')->fetchColumn(),'low_items'=>$pdo->query('SELECT p.nombre product,v.talla size,v.color,v.stock,v.stock_minimo min FROM variantes v JOIN productos p ON p.id=v.producto_id WHERE v.stock<=v.stock_minimo ORDER BY v.stock ASC LIMIT 5')->fetchAll(),'recent'=>$pdo->query('SELECT codigo,fecha,cliente,vendedor,metodo_pago,total FROM ventas ORDER BY fecha DESC LIMIT 5')->fetchAll()]);
    }

    if ($resource === 'reportes' && $method==='GET') {
        $start=$_GET['desde']??date('Y-m-01');$end=$_GET['hasta']??date('Y-m-d');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$start)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$end)||$start>$end)respond(['error'=>'Selecciona un periodo válido.'],422);
        $from=$start.' 00:00:00';$to=(new DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d').' 00:00:00';
        $s=$pdo->prepare('SELECT COALESCE(SUM(total),0) revenue,COALESCE(SUM(descuento),0) discounts,COUNT(*) transactions,COALESCE(AVG(total),0) average_ticket FROM ventas WHERE fecha>=? AND fecha<?');$s->execute([$from,$to]);$summary=$s->fetch();
        $s=$pdo->prepare('SELECT COALESCE(SUM((d.precio_unitario-p.precio_compra)*d.cantidad),0) profit FROM detalle_venta d JOIN variantes v ON v.id=d.variante_id JOIN productos p ON p.id=v.producto_id JOIN ventas ve ON ve.id=d.venta_id WHERE ve.fecha>=? AND ve.fecha<?');$s->execute([$from,$to]);$profit=(int)$s->fetchColumn();
        $s=$pdo->prepare('SELECT p.nombre,SUM(d.cantidad) quantity,SUM(d.subtotal) amount FROM detalle_venta d JOIN variantes v ON v.id=d.variante_id JOIN productos p ON p.id=v.producto_id JOIN ventas ve ON ve.id=d.venta_id WHERE ve.fecha>=? AND ve.fecha<? GROUP BY p.id,p.nombre ORDER BY quantity DESC,amount DESC LIMIT 10');$s->execute([$from,$to]);$top=$s->fetchAll();
        $s=$pdo->prepare('SELECT COALESCE(c.nombre,\'Sin categoría\') category,SUM(d.subtotal) amount FROM detalle_venta d JOIN variantes v ON v.id=d.variante_id JOIN productos p ON p.id=v.producto_id LEFT JOIN categorias c ON c.id=p.categoria_id JOIN ventas ve ON ve.id=d.venta_id WHERE ve.fecha>=? AND ve.fecha<? GROUP BY c.id,c.nombre ORDER BY amount DESC');$s->execute([$from,$to]);$categories=$s->fetchAll();
        $s=$pdo->prepare('SELECT metodo_pago method,SUM(total) amount,COUNT(*) transactions FROM ventas WHERE fecha>=? AND fecha<? GROUP BY metodo_pago ORDER BY amount DESC');$s->execute([$from,$to]);$payments=$s->fetchAll();
        $s=$pdo->prepare('SELECT vendedor,COUNT(*) transactions,SUM(total) amount FROM ventas WHERE fecha>=? AND fecha<? GROUP BY vendedor ORDER BY amount DESC');$s->execute([$from,$to]);$sellers=$s->fetchAll();
        $stock=$pdo->query('SELECT p.nombre,p.sku,COALESCE(c.nombre,\'Sin categoría\') categoria,COALESCE(m.nombre,\'—\') marca,COUNT(v.id) variantes,COALESCE(SUM(v.stock),0) unidades,COALESCE(SUM(v.stock*p.precio_compra),0) valor_costo FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id LEFT JOIN marcas m ON m.id=p.marca_id LEFT JOIN variantes v ON v.producto_id=p.id WHERE p.activo=1 GROUP BY p.id,p.nombre,p.sku,c.nombre,m.nombre ORDER BY p.nombre')->fetchAll();
        respond(['desde'=>$start,'hasta'=>$end,'revenue'=>(int)$summary['revenue'],'discounts'=>(int)$summary['discounts'],'transactions'=>(int)$summary['transactions'],'average_ticket'=>(int)$summary['average_ticket'],'profit'=>$profit,'top_products'=>$top,'categories'=>$categories,'payments'=>$payments,'sellers'=>$sellers,'inventory'=>$stock]);
    }
    respond(['error'=>'Ruta no encontrada'],404);
} catch (PDOException $e) {
    error_log($e->getMessage()); respond(['error'=>'Error de base de datos. Revisa la configuración de XAMPP.'],500);
} catch (Throwable $e) { error_log($e->getMessage()); respond(['error'=>'Error interno del servidor'],500); }
