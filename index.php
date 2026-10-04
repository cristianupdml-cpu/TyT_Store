<?php require __DIR__ . '/config/page_auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TyT Store - Dashboard</title>
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
        <div>
          <h1>TyT Store</h1>
          <span>Gestión de Inventario</span>
        </div>
      </div>

      <nav class="menu">
        <a class="menu-item active" href="#">
          <span class="icon">▦</span><span>Dashboard</span>
        </a>
        <a class="menu-item" href="#"><span class="icon">♢</span><span>Productos</span></a>
        <a class="menu-item" href="#"><span class="icon">▦</span><span>Inventario</span></a>
        <a class="menu-item" href="#"><span class="icon">◎</span><span>Ventas</span></a>
        <a class="menu-item" href="#"><span class="icon">◎</span><span>Proveedores</span></a>
        <a class="menu-item" href="#"><span class="icon">▥</span><span>Catálogos</span></a>
        <a class="menu-item" href="#"><span class="icon">▣</span><span>Reportes</span></a>
      </nav>

      <div class="profile">
        <div class="avatar">A</div>
        <div>
          <strong>Admin TyT</strong>
          <span>Administrador</span>
        </div>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <h2>Dashboard</h2>
          <p id="today">miércoles, 30 de septiembre de 2026</p>
        </div>
        <button class="new-sale"><span>+</span> Nueva Venta</button>
      </header>

      <section class="stats">
        <article class="stat-card">
          <div>
            <span class="stat-label">VENTAS HOY</span>
            <strong>$0</strong>
            <small>0 transacciones</small>
          </div>
          <span class="stat-emoji">💰</span>
        </article>

        <article class="stat-card">
          <div>
            <span class="stat-label">VENTAS DEL MES</span>
            <strong>$1.388.000</strong>
            <small>5 transacciones</small>
          </div>
          <span class="stat-emoji">📈</span>
        </article>

        <article class="stat-card">
          <div>
            <span class="stat-label">PRODUCTOS ACTIVOS</span>
            <strong>10</strong>
            <small>26 variantes en inventario</small>
          </div>
          <span class="stat-emoji">👕</span>
        </article>

        <article class="stat-card low-stat">
          <div>
            <span class="stat-label">STOCK BAJO</span>
            <strong>4</strong>
            <small>Ítems bajo mínimo</small>
          </div>
          <span class="stat-emoji">⚠️</span>
        </article>
      </section>

      <section class="middle-grid">
        <article class="panel sales-panel">
          <h3>Ventas — Últimos 7 días</h3>
          <div class="chart">
            <div class="chart-area">
              <div class="line l1"></div>
              <div class="line l2"></div>
              <div class="line l3"></div>
            </div>
            <div class="days">
              <div><span></span><small>Jue</small></div>
              <div><span></span><small>Vie</small></div>
              <div><span></span><small>Sáb</small></div>
              <div class="highlight"><span></span><small>Dom</small></div>
              <div class="highlight"><span></span><small>Lun</small></div>
              <div class="highlight"><span></span><small>Mar</small></div>
              <div><span></span><small>Mié</small></div>
            </div>
          </div>
        </article>

        <article class="panel stock-panel">
          <h3>⚠️ Stock Bajo</h3>
          <div class="stock-list">
            <div class="stock-row">
              <div><strong>Blanco</strong><small>Talla · mín. 5</small></div>
              <b>3 uds.</b>
            </div>
            <div class="stock-row">
              <div><strong>Azul Oscuro</strong><small>Talla · mín. 4</small></div>
              <b>2 uds.</b>
            </div>
            <div class="stock-row">
              <div><strong>Negro</strong><small>Talla · mín. 4</small></div>
              <b>1 uds.</b>
            </div>
            <div class="stock-row">
              <div><strong>Negro</strong><small>Talla · mín. 3</small></div>
              <b>2 uds.</b>
            </div>
          </div>
        </article>
      </section>

      <section class="panel recent-panel">
        <div class="panel-heading">
          <h3>Ventas Recientes</h3>
          <a href="#">Ver todas →</a>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>FECHA</th>
                <th>CLIENTE</th>
                <th>VENDEDOR</th>
                <th>MÉTODO</th>
                <th>TOTAL</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>#V2</td><td>29 de sept de 2026 <em>11:40 a. m.</em></td><td>Andrés Ospina</td><td>Carlos Vega</td><td><span class="badge card">tarjeta</span></td><td class="money">$300.000</td></tr>
              <tr><td>#V1</td><td>29 de sept de 2026 <em>09:15 a. m.</em></td><td>Laura Martínez</td><td>Andrea Torres</td><td><span class="badge cash">efectivo</span></td><td class="money">$265.000</td></tr>
              <tr><td>#V4</td><td>28 de sept de 2026 <em>04:05 p. m.</em></td><td>Camila Ríos</td><td>Natalia Cano</td><td><span class="badge cash">efectivo</span></td><td class="money">$300.000</td></tr>
              <tr><td>#V3</td><td>28 de sept de 2026 <em>02:20 p. m.</em></td><td>Pedro Salcedo</td><td>Andrea Torres</td><td><span class="badge transfer">transferencia</span></td><td class="money">$273.000</td></tr>
              <tr><td>#V5</td><td>27 de sept de 2026 <em>10:30 a. m.</em></td><td>Roberto Muñoz</td><td>Carlos Vega</td><td><span class="badge card">tarjeta</span></td><td class="money">$250.000</td></tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="js/request-security.js"></script>
<script src="js/app.js"></script>
</body>
</html>
