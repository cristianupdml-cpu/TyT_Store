const q=document.getElementById('search'),c=document.getElementById('category'),rows=document.getElementById('rows');let products=[];
async function productApi(url,options={}){const r=await fetch(url,{headers:{'Content-Type':'application/json'},...options}),d=await r.json();if(!r.ok)throw new Error(d.error||'Error al guardar');return d;}
const escP=v=>String(v??'').replace(/[&<>"']/g,x=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[x]));
function renderProducts(){const query=q.value.toLowerCase(),cat=c.value.toLowerCase();const list=products.filter(p=>(!query||`${p.sku} ${p.nombre}`.toLowerCase().includes(query))&&(!cat||String(p.categoria||'').toLowerCase()===cat));rows.innerHTML=list.map(p=>{const margin=Number(p.precio_venta)?Math.round((p.precio_venta-p.precio_compra)*100/p.precio_venta):0;return `<tr><td>${escP(p.sku)}</td><td><strong>${escP(p.nombre)}</strong><small>${escP(p.descripcion)}</small></td><td><span class="category-tag">${escP(p.categoria||'—')}</span></td><td>${escP(p.marca||'—')}</td><td>${Number(p.precio_compra).toLocaleString('es-CO')}</td><td><b>${Number(p.precio_venta).toLocaleString('es-CO')}</b></td><td><span class="margin">${margin}%</span></td><td><span class="status">${p.activo==1?'Activo':'Inactivo'}</span></td><td><button class="edit" data-edit="${p.id}">Editar</button><button class="${p.activo==1?'delete':'edit'}" data-toggle="${p.id}" data-active="${p.activo}">${p.activo==1?'Desactivar':'Activar'}</button></td></tr>`;}).join('')||'<tr><td colspan="9">No hay productos.</td></tr>';}
async function loadProducts(){products=await productApi('api/productos');renderProducts();}
q.addEventListener('input',renderProducts);c.addEventListener('change',renderProducts);
const productModal=document.getElementById('productModal'),productForm=document.getElementById('productForm'),productError=document.getElementById('productError'),saveBtn=document.getElementById('saveProduct'),marginPreview=document.getElementById('marginPreview');
const F={sku:document.getElementById('productSku'),nombre:document.getElementById('productName'),descripcion:document.getElementById('productDesc'),categoria:document.getElementById('productCategory'),marca:document.getElementById('productBrand'),proveedor:document.getElementById('productProvider'),compra:document.getElementById('productCost'),venta:document.getElementById('productPrice')};
let editingProduct=null;
const optionsHtml=(list,label,empty)=>`<option value="">${empty}</option>`+list.map(x=>`<option value="${x.id}">${escP(x[label])}</option>`).join('');
function updateMargin(){const c=Number(F.compra.value)||0,v=Number(F.venta.value)||0;marginPreview.innerHTML=`Margen estimado: <b>${v?Math.round((v-c)*100/v):0}%</b>`+(v&&v<c?' <span class="margin-warn">· el precio de venta es menor al de compra</span>':'');}
async function openProductModal(item=null){
  editingProduct=item;productForm.reset();productError.textContent='';
  document.getElementById('productModalTitle').textContent=item?'Editar producto':'Nuevo producto';
  saveBtn.textContent=item?'Guardar cambios':'Guardar producto';
  productModal.classList.add('show');F.sku.focus();
  const [cats,brands,provs]=await Promise.all([
    productApi('api/catalogos?tipo=categorias').catch(()=>[]),
    productApi('api/catalogos?tipo=marcas').catch(()=>[]),
    productApi('api/proveedores').catch(()=>[])
  ]);
  F.categoria.innerHTML=optionsHtml(cats,'nombre','Sin categoría');
  F.marca.innerHTML=optionsHtml(brands,'nombre','Sin marca');
  F.proveedor.innerHTML=optionsHtml(provs,'nombre','Sin proveedor');
  F.sku.value=item?.sku||'';F.nombre.value=item?.nombre||'';F.descripcion.value=item?.descripcion||'';
  F.categoria.value=item?.categoria_id||'';F.marca.value=item?.marca_id||'';F.proveedor.value=item?.proveedor_id||'';
  F.compra.value=item?.precio_compra??0;F.venta.value=item?.precio_venta??0;updateMargin();
}
const closeProductModal=()=>productModal.classList.remove('show');
document.getElementById('newProduct').addEventListener('click',()=>openProductModal());
document.getElementById('cancelProduct').addEventListener('click',closeProductModal);
document.getElementById('closeProduct').addEventListener('click',closeProductModal);
productModal.addEventListener('click',e=>{if(e.target===productModal)closeProductModal();});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&productModal.classList.contains('show'))closeProductModal();});
F.compra.addEventListener('input',updateMargin);F.venta.addEventListener('input',updateMargin);
productForm.addEventListener('submit',async e=>{
  e.preventDefault();productError.textContent='';
  const data={sku:F.sku.value.trim(),nombre:F.nombre.value.trim(),descripcion:F.descripcion.value.trim(),categoria_id:F.categoria.value||null,marca_id:F.marca.value||null,proveedor_id:F.proveedor.value||null,precio_compra:Number(F.compra.value),precio_venta:Number(F.venta.value)};
  if(!data.sku||!data.nombre){productError.textContent='El SKU y el nombre son obligatorios.';return;}
  saveBtn.disabled=true;
  try{
    if(editingProduct)await productApi(`api/productos/${editingProduct.id}`,{method:'PUT',body:JSON.stringify({...editingProduct,...data,activo:editingProduct.activo})});
    else await productApi('api/productos',{method:'POST',body:JSON.stringify(data)});
    const wasEditing=!!editingProduct;closeProductModal();await loadProducts();ui.toast(wasEditing?'Producto actualizado.':'Producto creado.','success');
  }catch(err){productError.textContent=err.message;}
  finally{saveBtn.disabled=false;}
});
rows.addEventListener('click',async e=>{
  const toggle=e.target.closest('[data-toggle]'),edit=e.target.closest('[data-edit]');
  if(toggle){const p=products.find(x=>x.id==toggle.dataset.toggle),active=Number(p.activo)!==1;if(!await ui.confirm({title:active?'Activar producto':'Desactivar producto',message:active?`"${p.nombre}" volverá a estar disponible para vender.`:`"${p.nombre}" dejará de aparecer para vender. Podrás activarlo de nuevo.`,confirmText:active?'Activar':'Desactivar',danger:!active}))return;try{await productApi(`api/productos/${p.id}`,{method:'PUT',body:JSON.stringify({...p,activo:active?1:0})});await loadProducts();ui.toast(active?'Producto activado.':'Producto desactivado.','success');}catch(err){ui.toast(err.message,'error');}return;}
  if(edit)openProductModal(products.find(x=>x.id==edit.dataset.edit));
});
loadProducts().catch(err=>ui.toast(`No se pudieron cargar los productos: ${err.message}`,'error'));