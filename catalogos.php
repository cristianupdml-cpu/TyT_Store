<?php require __DIR__ . '/config/page_auth.php'; ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>TyT Store - Catálogos</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><div class="brand-mark">T</div><div><h1>TyT Store</h1><span>Gestión de Inventario</span></div></div>
    <nav class="menu">
      <a class="menu-item" href="index.php">▦ &nbsp; Dashboard</a>
      <a class="menu-item" href="productos.php">♢ &nbsp; Productos</a>
      <a class="menu-item" href="inventario.php">▦ &nbsp; Inventario</a>
      <a class="menu-item" href="ventas.php">◎ &nbsp; Ventas</a>
      <a class="menu-item" href="proveedores.php">◎ &nbsp; Proveedores</a>
      <a class="menu-item active" href="catalogos.php">▥ &nbsp; Catálogos</a>
      <a class="menu-item" href="#">▣ &nbsp; Reportes</a>
    </nav>
    <div class="profile"><div class="avatar">A</div><div><strong>Admin TyT</strong><span>Administrador</span></div></div>
  </aside>

  <main class="content catalog-content">
    <header class="topbar catalog-topbar">
      <div><h2>Catálogos</h2><p>Gestiona categorías, marcas y tallas</p></div>
    </header>

    <div class="catalog-tabs" role="tablist" aria-label="Catálogos">
      <button class="catalog-tab active" data-tab="categorias" role="tab">Categorías <b id="categoryCount">6</b></button>
      <button class="catalog-tab" data-tab="marcas" role="tab">Marcas <b id="brandCount">8</b></button>
      <button class="catalog-tab" data-tab="tallas" role="tab">Tallas <b id="sizeCount">16</b></button>
    </div>

    <section class="catalog-view active" id="tab-categorias">
      <div class="catalog-grid">
        <section class="catalog-form-card">
          <h3>Nueva Categoría</h3>
          <form id="categoryForm">
            <div class="catalog-field"><label for="categoryName">Nombre</label><input id="categoryName" required placeholder="Ej: Camisetas"></div>
            <div class="catalog-field"><label for="categoryDescription">Descripción</label><input id="categoryDescription" placeholder=""></div>
            <button class="catalog-add-btn" type="submit">Agregar</button>
          </form>
        </section>
        <section class="catalog-list-card">
          <div class="catalog-table-head"><span>NOMBRE</span><span>DESCRIPCIÓN</span><span></span></div>
          <div id="categoryRows"></div>
        </section>
      </div>
    </section>

    <section class="catalog-view" id="tab-marcas">
      <div class="catalog-grid">
        <section class="catalog-form-card">
          <h3>Nueva Marca</h3>
          <form id="brandForm">
            <div class="catalog-field"><label for="brandName">Nombre</label><input id="brandName" required placeholder="Ej: Nike"></div>
            <div class="catalog-field"><label for="brandDescription">Descripción</label><input id="brandDescription" placeholder=""></div>
            <button class="catalog-add-btn" type="submit">Agregar</button>
          </form>
        </section>
        <section class="catalog-list-card">
          <div class="catalog-table-head"><span>NOMBRE</span><span>DESCRIPCIÓN</span><span></span></div>
          <div id="brandRows"></div>
        </section>
      </div>
    </section>

    <section class="catalog-view" id="tab-tallas">
      <div class="catalog-grid size-grid">
        <section class="catalog-form-card">
          <h3>Nueva Talla</h3>
          <form id="sizeForm">
            <div class="catalog-field"><label for="sizeLabel">Etiqueta</label><input id="sizeLabel" required placeholder="Ej: M, L, 38, 42"></div>
            <div class="catalog-field"><label for="sizeType">Tipo</label><select id="sizeType"><option value="Ropa">Ropa</option><option value="Calzado">Calzado</option></select></div>
            <button class="catalog-add-btn" type="submit">Agregar Talla</button>
          </form>
        </section>
        <section class="catalog-list-card sizes-card">
          <h3>Tallas registradas</h3>
          <div class="size-group"><span class="size-group-title">ROPA</span><div id="clothingSizes" class="size-tags"></div></div>
          <div class="size-group"><span class="size-group-title">CALZADO</span><div id="shoeSizes" class="size-tags shoe"></div></div>
        </section>
      </div>
    </section>
  </main>
</div>

<div class="modal-overlay" id="catalogEditModal">
  <div class="modal catalog-edit-modal">
    <div class="modal-title-row"><div><h3 id="editTitle">Editar</h3><p id="editSubtitle">Actualiza la información.</p></div><button class="modal-close" id="closeEdit">×</button></div>
    <form id="editForm">
      <div id="editTextFields"></div>
      <div class="modal-actions"><button type="button" class="modal-cancel" id="cancelEdit">Cancelar</button><button type="submit" class="modal-save">Guardar cambios</button></div>
    </form>
  </div>
</div>

<script src="js/request-security.js"></script>
<script src="js/catalogos.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
