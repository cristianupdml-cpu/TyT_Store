<?php require __DIR__ . '/config/page_auth.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>TyT Store - Ventas</title>
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
      <a class="menu-item" href="#"><span class="icon">▦</span><span>Dashboard</span></a>
      <a class="menu-item" href="productos.php"><span class="icon">♢</span><span>Productos</span></a>
      <a class="menu-item" href="inventario.php"><span class="icon">▦</span><span>Inventario</span></a>
      <a class="menu-item active" href="ventas.php"><span class="icon">◎</span><span>Ventas</span></a>
      <a class="menu-item" href="proveedores.php"><span class="icon">◎</span><span>Proveedores</span></a>
      <a class="menu-item" href="#"><span class="icon">▥</span><span>Catálogos</span></a>
      <a class="menu-item" href="#"><span class="icon">▣</span><span>Reportes</span></a>
    </nav>
    <div class="profile">
      <div class="avatar">A</div>
      <div><strong>Admin TyT</strong><span>Administrador</span></div>
    </div>
  </aside>

  <main class="content sales-content">
    <header class="topbar">
      <div>
        <h2>Ventas</h2>
        <p id="salesSubtitle">5 ventas registradas</p>
      </div>
      <button class="new-sale" id="newSaleBtn">+ &nbsp;Nueva Venta</button>
    </header>

    <section class="sales-stats">
      <article class="sales-stat-card">
        <span class="stat-label">TOTAL FACTURADO</span>
        <strong id="salesTotal">$1.388.000</strong>
      </article>
      <article class="sales-stat-card">
        <span class="stat-label">DESCUENTOS</span>
        <strong class="discount" id="salesDiscount">−$39.000</strong>
      </article>
      <article class="sales-stat-card">
        <span class="stat-label">TRANSACCIONES</span>
        <strong id="salesTransactions">5</strong>
      </article>
    </section>

    <section class="sales-filters">
      <input id="salesSearch" type="search" placeholder="Buscar por cliente, vendedor o ID...">
      <select id="salesPeriod">
        <option value="all">Todo el período</option>
        <option value="today">Hoy</option>
        <option value="week">Últimos 7 días</option>
        <option value="month">Este mes</option>
      </select>
    </section>

    <section class="panel sales-table-panel">
      <div class="table-wrap">
        <table class="sales-table">
          <thead>
            <tr>
              <th>ID VENTA</th>
              <th>FECHA</th>
              <th>CLIENTE</th>
              <th>VENDEDOR</th>
              <th>ÍTEMS</th>
              <th>DESCUENTO</th>
              <th>TOTAL</th>
              <th>MÉTODO</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="salesRows"></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<div class="modal-overlay" id="saleModal">
  <div class="modal sale-modal">
    <div class="modal-title-row"><div><h3>Nueva venta</h3><p>Agrega productos disponibles; el sistema calculará el total y actualizará el inventario al confirmar.</p></div><button class="modal-close" data-close="saleModal">×</button></div>
    <div class="form-grid">
      <div class="form-field"><label for="saleClient">CLIENTE</label><input id="saleClient" placeholder="Nombre del cliente"></div>
      <div class="form-field"><label for="saleMethod">MÉTODO DE PAGO</label><select id="saleMethod"><option value="tarjeta">Tarjeta</option><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option></select></div>
      <div class="form-field full"><label for="saleVariant">PRODUCTO / VARIANTE</label><select id="saleVariant"></select></div>
      <div class="form-field"><label for="saleQuantity">CANTIDAD</label><input id="saleQuantity" type="number" min="1" value="1"></div>
      <div class="form-field"><label>&nbsp;</label><button class="modal-cancel" id="addSaleItem" type="button">Agregar producto</button></div>
      <div class="form-field full"><div class="table-wrap"><table><thead><tr><th>PRODUCTO</th><th>PRECIO</th><th>CANTIDAD</th><th>SUBTOTAL</th><th></th></tr></thead><tbody id="saleCartRows"></tbody></table></div></div>
      <div class="form-field"><label for="saleDiscount">DESCUENTO (COP)</label><input id="saleDiscount" type="number" min="0" value="0"></div>
      <div class="form-field"><label>TOTAL</label><strong id="saleTotalDisplay">$0</strong></div>
    </div>
    <div class="modal-actions"><button class="modal-cancel" data-close="saleModal">Cancelar</button><button class="modal-save" id="saveSale">Registrar venta</button></div>
  </div>
</div>

<script src="js/request-security.js"></script>
<script src="js/ventas.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
