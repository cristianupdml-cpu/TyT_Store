/* ===== Avisos (toast) y paneles de confirmación — ui.toast() / ui.confirm() ===== */
(() => {
  const ICONS = {success:'✓', error:'!', warning:'!', info:'i'};
  function stack(){
    let s=document.getElementById('toastStack');
    if(!s){s=document.createElement('div');s.id='toastStack';s.className='toast-stack';s.setAttribute('aria-live','polite');document.body.appendChild(s);}
    return s;
  }
  function toast(message,type='success',ms=4500){
    const el=document.createElement('div');
    el.className=`toast toast-${type}`;el.setAttribute('role',type==='error'?'alert':'status');
    const icon=document.createElement('span');icon.className='toast-icon';icon.textContent=ICONS[type]||'i';
    const text=document.createElement('span');text.className='toast-text';text.textContent=message;
    const close=document.createElement('button');close.type='button';close.className='toast-close';close.setAttribute('aria-label','Cerrar');close.textContent='×';
    el.append(icon,text,close);stack().appendChild(el);
    const remove=()=>{if(!el.isConnected)return;el.classList.add('toast-out');setTimeout(()=>el.remove(),220);};
    close.addEventListener('click',remove);setTimeout(remove,ms);
  }
  function confirmDialog({title='¿Estás seguro?',message='',confirmText='Confirmar',cancelText='Cancelar',danger=false}={}){
    return new Promise(resolve=>{
      const overlay=document.createElement('div');overlay.className='ui-overlay';
      const box=document.createElement('div');box.className='ui-confirm';box.setAttribute('role','alertdialog');box.setAttribute('aria-modal','true');
      const icon=document.createElement('div');icon.className='ui-confirm-icon'+(danger?' danger':'');icon.textContent=danger?'!':'?';
      const h=document.createElement('h3');h.textContent=title;
      const p=document.createElement('p');p.textContent=message;
      const actions=document.createElement('div');actions.className='ui-confirm-actions';
      const cancel=document.createElement('button');cancel.type='button';cancel.className='ui-btn ui-btn-cancel';cancel.textContent=cancelText;
      const ok=document.createElement('button');ok.type='button';ok.className='ui-btn '+(danger?'ui-btn-danger':'ui-btn-primary');ok.textContent=confirmText;
      actions.append(cancel,ok);box.append(icon,h,p,actions);overlay.appendChild(box);document.body.appendChild(overlay);
      requestAnimationFrame(()=>overlay.classList.add('show'));cancel.focus();
      let done=false;
      const finish=value=>{if(done)return;done=true;document.removeEventListener('keydown',onKey);overlay.classList.remove('show');setTimeout(()=>overlay.remove(),180);resolve(value);};
      const onKey=e=>{if(e.key==='Escape'){e.preventDefault();finish(false);}};
      document.addEventListener('keydown',onKey);
      cancel.addEventListener('click',()=>finish(false));ok.addEventListener('click',()=>finish(true));
      overlay.addEventListener('click',e=>{if(e.target===overlay)finish(false);});
    });
  }
  window.ui={toast,confirm:confirmDialog};
})();

(() => {
  const pages = [
    {file:'index.php',label:'Dashboard',icon:'▦'}, {file:'productos.php',label:'Productos',icon:'♢'},
    {file:'inventario.php',label:'Inventario',icon:'▦'}, {file:'ventas.php',label:'Ventas',icon:'◎'},
    {file:'proveedores.php',label:'Proveedores',icon:'◎'}, {file:'catalogos.php',label:'Catálogos',icon:'▥'},
    {file:'reportes.php',label:'Reportes',icon:'▣'}, {file:'usuarios.php',label:'Usuarios',icon:'♙',admin:true}
  ];
  const current=(location.pathname.split('/').pop()||'index.php').toLowerCase();
  const money=value=>'$'+Number(value||0).toLocaleString('es-CO');
  const escapeHtml=value=>String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  async function init(){
    try {
      const response=await fetch('api/auth/session');
      if(!response.ok)throw new Error('Sesión inválida');
      const session=await response.json();
      if(!session.user){location.href=session.needs_setup?'setup_admin.php':'login.php';return;}
      const user=session.user,isAdmin=user.rol==='administrador',nav=document.querySelector('.menu');
      document.body.classList.toggle('role-vendedor',!isAdmin);
      if(nav)nav.innerHTML=pages.filter(p=>isAdmin||(!p.admin&&!['proveedores.php','catalogos.php','reportes.php'].includes(p.file))).map(p=>`<a class="menu-item ${current===p.file?'active':''}" href="${p.file}"><span class="icon">${p.icon}</span><span>${p.label}</span></a>`).join('');
      const profile=document.querySelector('.profile');
      if(profile){const strong=profile.querySelector('strong'),span=profile.querySelector('span'),avatar=profile.querySelector('.avatar');if(strong)strong.textContent=user.nombre;if(span)span.textContent=user.rol==='administrador'?'Administrador':'Vendedor';if(avatar)avatar.textContent=user.nombre.charAt(0).toUpperCase();const out=document.createElement('button');out.type='button';out.className='logout-button';out.textContent='Cerrar sesión';out.addEventListener('click',async()=>{try{await fetch('api/auth/logout',{method:'POST'});}finally{location.href='login.php';}});profile.appendChild(out);}
      if(current!=='ventas.php')document.querySelectorAll('.new-sale').forEach(button=>{if(['newSaleBtn','newProduct','newProvider','addItemBtn','runReport'].includes(button.id))return;button.addEventListener('click',()=>location.href='ventas.php');});
      document.querySelectorAll('a').forEach(link=>{if(link.textContent.trim().toLowerCase().includes('ver todas'))link.href='ventas.php';});
      if(current==='index.php')await loadDashboard();
    } catch(error) { location.href='login.php'; }
  }

  async function loadDashboard(){
    const response=await fetch('api/dashboard');if(!response.ok)return;const data=await response.json();
    const cards=document.querySelectorAll('.stat-card');if(!cards.length)return;
    cards[0].querySelector('strong').textContent=money(data.today);cards[0].querySelector('small').textContent=`${data.today_count} transacciones`;
    cards[1].querySelector('strong').textContent=money(data.month);cards[1].querySelector('small').textContent=`${data.month_count} transacciones`;
    cards[2].querySelector('strong').textContent=data.products;cards[2].querySelector('small').textContent=`${data.variants} variantes en inventario`;
    cards[3].querySelector('strong').textContent=data.low_stock;
    const body=document.querySelector('.recent-panel tbody');if(body)body.innerHTML=data.recent.map(s=>`<tr><td>${escapeHtml(s.codigo)}</td><td>${new Date(s.fecha.replace(' ','T')).toLocaleDateString('es-CO')}</td><td>${escapeHtml(s.cliente)}</td><td>${escapeHtml(s.vendedor)}</td><td>${escapeHtml(s.metodo_pago)}</td><td class="money">${money(s.total)}</td></tr>`).join('')||'<tr><td colspan="6">No hay ventas registradas.</td></tr>';
    const stockList=document.querySelector('.stock-list');if(stockList)stockList.innerHTML=data.low_items.map(item=>`<div class="stock-row"><div><strong>${escapeHtml(item.product)}</strong><small>${escapeHtml(item.size)} / ${escapeHtml(item.color)} · mín. ${item.min}</small></div><b>${item.stock} uds.</b></div>`).join('')||'<p>Sin productos bajo el mínimo.</p>';
  }
  init();
})();
