<?php require __DIR__ . '/config/page_auth.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>TyT Store - Inventario</title>
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
      <a class="menu-item active" href="inventario.php"><span class="icon">▦</span><span>Inventario</span></a>
      <a class="menu-item" href="#"><span class="icon">◎</span><span>Ventas</span></a>
      <a class="menu-item" href="proveedores.php"><span class="icon">◎</span><span>Proveedores</span></a>
      <a class="menu-item" href="#"><span class="icon">▥</span><span>Catálogos</span></a>
      <a class="menu-item" href="#"><span class="icon">▣</span><span>Reportes</span></a>
    </nav>
    <div class="profile">
      <div class="avatar">A</div>
      <div><strong>Admin TyT</strong><span>Administrador</span></div>
    </div>
  </aside>

  <main class="content inventory-content">
    <header class="topbar">
      <div>
        <h2>Inventario</h2>
        <p id="inventorySubtitle">26 variantes · 196 unidades</p>
      </div>
      <button class="new-sale" id="addItemBtn">+ &nbsp;Agregar ítem</button>
    </header>

    <section class="inventory-stats">
      <article class="inventory-stat-card">
        <span class="stat-label">TOTAL UNIDADES</span>
        <strong id="totalUnits">196</strong>
      </article>
      <article class="inventory-stat-card">
        <span class="stat-label">VALOR EN STOCK</span>
        <strong id="stockValue">$26.086.000</strong>
      </article>
      <article class="inventory-stat-card low">
        <span class="stat-label">STOCK BAJO</span>
        <strong id="lowStockCount">4</strong>
      </article>
    </section>

    <section class="inventory-filters">
      <input id="inventorySearch" type="search" placeholder="Buscar producto o color...">
      <select id="inventoryCategory">
        <option value="">Todas categorías</option>
        <option>Camisetas</option>
        <option>Pantalones</option>
        <option>Calzado</option>
        <option>Chaquetas</option>
        <option>Zapatos</option>
      </select>
      <select id="inventoryStatus">
        <option value="">Todo el stock</option>
        <option value="normal">Normal</option>
        <option value="bajo">Stock bajo</option>
      </select>
    </section>

    <section class="panel inventory-table-panel">
      <div class="table-wrap">
        <table class="inventory-table">
          <thead>
            <tr>
              <th>PRODUCTO</th>
              <th>TALLA</th>
              <th>COLOR</th>
              <th>STOCK</th>
              <th>MÍNIMO</th>
              <th>ESTADO</th>
              <th>ACCIONES</th>
            </tr>
          </thead>
          <tbody id="inventoryRows"></tbody>
        </table>
      </div>
    </section>

    <section class="panel movements-panel">
      <div class="inventory-panel-heading"><h3>Últimos Movimientos</h3></div>
      <div id="movementList"></div>
    </section>
  </main>
</div>

<div class="modal-overlay" id="movementModal">
  <div class="modal movement-modal">
    <div class="modal-title-row">
      <div><h3>Registrar movimiento</h3><p id="movementProductName"></p></div>
      <button class="modal-close" data-close="movementModal">×</button>
    </div>
    <div class="form-grid">
      <div class="form-field"><label for="movementType">TIPO</label><select id="movementType"><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="ajuste">Ajuste</option></select></div>
      <div class="form-field"><label for="movementQuantity">CANTIDAD</label><input id="movementQuantity" type="number" min="1" value="1"></div>
      <div class="form-field full"><label for="movementNote">MOTIVO / NOTA</label><input id="movementNote" type="text" placeholder="Ej. Reposición de proveedor"></div>
    </div>
    <div class="modal-actions"><button class="modal-cancel" data-close="movementModal">Cancelar</button><button class="modal-save" id="saveMovement">Guardar movimiento</button></div>
  </div>
</div>

<div class="modal-overlay" id="itemModal">
  <div class="modal">
    <div class="modal-title-row"><h3>Agregar ítem</h3><button class="modal-close" data-close="itemModal">×</button></div>
    <div class="form-grid">
      <div class="form-field full"><label for="itemProduct">PRODUCTO</label><select id="itemProduct"><option>Camiseta Básica Oversize</option><option>Camiseta Polo Clásica</option><option>Jean Skinny Mujer</option><option>Jean Slim Hombre</option><option>Tenis Air Max Running</option><option>Tenis Ultraboost</option><option>Chaqueta Deportiva</option><option>Blusa Lino Mujer</option><option>Legging Deportivo</option><option>Zapato Cuero Oxford</option></select></div>
      <div class="form-field"><label for="itemSize">TALLA</label><input id="itemSize" placeholder="M"></div>
      <div class="form-field"><label for="itemColor">COLOR</label><input id="itemColor" placeholder="Negro"></div>
      <div class="form-field"><label for="itemStock">STOCK INICIAL</label><input id="itemStock" type="number" min="0" value="1"></div>
      <div class="form-field"><label for="itemMin">STOCK MÍNIMO</label><input id="itemMin" type="number" min="0" value="3"></div>
    </div>
    <div class="modal-actions"><button class="modal-cancel" data-close="itemModal">Cancelar</button><button class="modal-save" id="saveItem">Agregar ítem</button></div>
  </div>
</div>

<script src="js/request-security.js"></script>
<script src="js/inventario.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
