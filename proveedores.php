<?php require __DIR__ . '/config/page_auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TyT Store - Proveedores</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
<aside class="sidebar">
  <div class="brand">
    <div class="brand-mark">T</div>
    <div><h1>TyT Store</h1><span>Gestión de Inventario</span></div>
  </div>
  <nav class="menu">
    <a class="menu-item" href="index.php"><span class="icon">▦</span>Dashboard</a>
    <a class="menu-item" href="productos.php"><span class="icon">♢</span>Productos</a>
    <a class="menu-item" href="#"><span class="icon">▦</span>Inventario</a>
    <a class="menu-item" href="#"><span class="icon">◎</span>Ventas</a>
    <a class="menu-item active" href="proveedores.php"><span class="icon">◎</span>Proveedores</a>
    <a class="menu-item" href="#"><span class="icon">▥</span>Catálogos</a>
    <a class="menu-item" href="#"><span class="icon">▣</span>Reportes</a>
  </nav>
  <div class="profile">
    <div class="avatar">A</div>
    <div><strong>Admin TyT</strong><span>Administrador</span></div>
  </div>
</aside>

<main class="content providers-content">
<header class="topbar">
  <div><h2>Proveedores</h2><p id="providerCount">5 proveedores registrados</p></div>
  <button class="new-sale" id="newProvider">+ &nbsp; Nuevo Proveedor</button>
</header>

<div class="provider-search">
  <input id="providerSearch" type="search" placeholder="Buscar por nombre o ciudad...">
</div>

<section class="providers-grid" id="providersGrid">

<article class="provider-card" data-search="distribuidora moda estilo carlos ramirez medellin ventas moda estilo 900123456 3 productos">
  <div class="provider-card-head"><div class="provider-letter">D</div><div class="provider-actions"><button class="provider-edit">Editar</button><button class="provider-delete">×</button></div></div>
  <h3>Distribuidora Moda &amp; Estilo</h3>
  <div class="provider-info">
    <p><span class="info-icon">♟</span>Carlos Ramírez</p>
    <p><span class="info-icon">⌕</span>310 456 7890</p>
    <p><span class="info-icon">✉</span>ventas@modaestilo.com</p>
    <p><span class="info-icon">📍</span>Medellín</p>
    <p><span class="info-icon">▥</span>NIT: 900.123.456-1</p>
  </div>
  <div class="provider-divider"></div><span class="provider-products">3 productos</span>
</article>

<article class="provider-card" data-search="nike colombia sas ana gomez bogota distribuciones nikeco 6012345678 800987654 2 productos">
  <div class="provider-card-head"><div class="provider-letter">N</div><div class="provider-actions"><button class="provider-edit">Editar</button><button class="provider-delete">×</button></div></div>
  <h3>Nike Colombia SAS</h3>
  <div class="provider-info">
    <p><span class="info-icon">♟</span>Ana Gómez</p>
    <p><span class="info-icon">⌕</span>601 234 5678</p>
    <p><span class="info-icon">✉</span>distribuciones@nikeco.com</p>
    <p><span class="info-icon">📍</span>Bogotá</p>
    <p><span class="info-icon">▥</span>NIT: 800.987.654-3</p>
  </div>
  <div class="provider-divider"></div><span class="provider-products">2 productos</span>
</article>

<article class="provider-card" data-search="calzatodo mayorista luis herrera bucaramanga pedidos calzatodo 3507890123 900345678 2 productos">
  <div class="provider-card-head"><div class="provider-letter">C</div><div class="provider-actions"><button class="provider-edit">Editar</button><button class="provider-delete">×</button></div></div>
  <h3>Calzatodo Mayorista</h3>
  <div class="provider-info">
    <p><span class="info-icon">♟</span>Luis Herrera</p>
    <p><span class="info-icon">⌕</span>350 789 0123</p>
    <p><span class="info-icon">✉</span>pedidos@calzatodo.com.co</p>
    <p><span class="info-icon">📍</span>Bucaramanga</p>
    <p><span class="info-icon">▥</span>NIT: 900.345.678-9</p>
  </div>
  <div class="provider-divider"></div><span class="provider-products">2 productos</span>
</article>

<article class="provider-card" data-search="textiles del valle maria ospina cali contacto textilesdelvalle 3189012345 830456789 2 productos">
  <div class="provider-card-head"><div class="provider-letter">T</div><div class="provider-actions"><button class="provider-edit">Editar</button><button class="provider-delete">×</button></div></div>
  <h3>Textiles del Valle</h3>
  <div class="provider-info">
    <p><span class="info-icon">♟</span>María Ospina</p>
    <p><span class="info-icon">⌕</span>318 901 2345</p>
    <p><span class="info-icon">✉</span>contacto@textilesdelValle.co</p>
    <p><span class="info-icon">📍</span>Cali</p>
    <p><span class="info-icon">▥</span>NIT: 830.456.789-2</p>
  </div>
  <div class="provider-divider"></div><span class="provider-products">2 productos</span>
</article>

<article class="provider-card" data-search="fashion import ltda jorge cifuentes barranquilla jorge fashionimport 3145678901 860789012 1 productos">
  <div class="provider-card-head"><div class="provider-letter">F</div><div class="provider-actions"><button class="provider-edit">Editar</button><button class="provider-delete">×</button></div></div>
  <h3>Fashion Import Ltda</h3>
  <div class="provider-info">
    <p><span class="info-icon">♟</span>Jorge Cifuentes</p>
    <p><span class="info-icon">⌕</span>314 567 8901</p>
    <p><span class="info-icon">✉</span>jorge@fashionimport.co</p>
    <p><span class="info-icon">📍</span>Barranquilla</p>
    <p><span class="info-icon">▥</span>NIT: 860.789.012-7</p>
  </div>
  <div class="provider-divider"></div><span class="provider-products">1 productos</span>
</article>

</section>
</main>
</div>

<div class="modal-overlay" id="providerModal">
  <div class="modal">
    <h3>Nuevo proveedor</h3>
    <form id="providerForm">
      <div class="form-grid">
        <div class="form-field full"><label>Empresa</label><input id="providerName" required></div>
        <div class="form-field"><label>Contacto</label><input id="providerContact" required></div>
        <div class="form-field"><label>Teléfono</label><input id="providerPhone" required></div>
        <div class="form-field full"><label>Correo</label><input id="providerEmail" type="email" required></div>
        <div class="form-field"><label>Ciudad</label><input id="providerCity" required></div>
        <div class="form-field"><label>NIT</label><input id="providerNit" required></div>
      </div>
      <div class="modal-actions">
        <button type="button" class="modal-cancel" id="cancelProvider">Cancelar</button>
        <button type="submit" class="modal-save">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="js/request-security.js"></script>
<script src="js/app.js"></script>
<script src="js/proveedores.js"></script>
</body>
</html>
