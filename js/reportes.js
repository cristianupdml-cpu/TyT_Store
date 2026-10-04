const report$=id=>document.getElementById(id),reportMoney=v=>'$'+Number(v||0).toLocaleString('es-CO'),localDate=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;let reportData=null;
const eReport=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
async function requestReport(){const start=report$('reportStart').value,end=report$('reportEnd').value;if(!start||!end){ui.toast('Selecciona las dos fechas.','warning');return;}try{const r=await fetch(`api/reportes?desde=${encodeURIComponent(start)}&hasta=${encodeURIComponent(end)}`),d=await r.json();if(!r.ok)throw new Error(d.error||'No se pudo generar el reporte');reportData=d;renderReport();}catch(err){ui.toast(err.message,'error');}}
function rowsHtml(rows,render,columns){return rows.length?rows.map(render).join(''):`<tr><td colspan="${columns}">Sin datos para el periodo seleccionado.</td></tr>`;}
function renderReport(){const d=reportData;report$('reportRevenue').textContent=reportMoney(d.revenue);report$('reportCount').textContent=`${d.transactions} ${d.transactions===1?'venta':'ventas'} · descuentos ${reportMoney(d.discounts)}`;report$('reportProfit').textContent=reportMoney(d.profit);report$('reportAverage').textContent=reportMoney(d.average_ticket);report$('reportStockValue').textContent=reportMoney(d.inventory.reduce((s,x)=>s+Number(x.valor_costo),0));report$('topProductsRows').innerHTML=rowsHtml(d.top_products,x=>`<tr><td>${eReport(x.nombre)}</td><td>${x.quantity}</td><td>${reportMoney(x.amount)}</td></tr>`,3);report$('categoryRows').innerHTML=rowsHtml(d.categories,x=>`<tr><td>${eReport(x.category)}</td><td>${reportMoney(x.amount)}</td></tr>`,2);report$('paymentRows').innerHTML=rowsHtml(d.payments,x=>`<tr><td>${eReport(x.method)}</td><td>${x.transactions}</td><td>${reportMoney(x.amount)}</td></tr>`,3);report$('sellerRows').innerHTML=rowsHtml(d.sellers,x=>`<tr><td>${eReport(x.vendedor)}</td><td>${x.transactions}</td><td>${reportMoney(x.amount)}</td></tr>`,3);report$('reportInventoryRows').innerHTML=rowsHtml(d.inventory,x=>`<tr><td><strong>${eReport(x.nombre)}</strong></td><td>${eReport(x.sku)}</td><td>${eReport(x.categoria)}</td><td>${eReport(x.marca)}</td><td>${x.variantes}</td><td>${x.unidades}</td><td>${reportMoney(x.valor_costo)}</td></tr>`,7);}
function reportCsvCell(value){let text=String(value??'');if(/^[=+@]/.test(text))text="'"+text;return '"'+text.replace(/"/g,'""')+'"';}
function exportReportCsv(){
  if(!reportData){ui.toast('Genera primero el reporte.','warning');return;}
  const d=reportData,rows=[
    ['Sección','Elemento','Código','Categoría / detalle','Unidades / variantes','Transacciones','Valor ($)'],
    ['Periodo',`${d.desde} a ${d.hasta}`,'','','','',''],
    ['Resumen','Ingresos','','','','',d.revenue],
    ['Resumen','Descuentos','','','','',d.discounts],
    ['Resumen','Utilidad estimada','','','','',d.profit],
    ['Resumen','Ticket promedio','','','','',d.average_ticket],
    ['Resumen','Ventas','','','',d.transactions,''],
    ['Resumen','Valor del inventario','','','','',d.inventory.reduce((sum,item)=>sum+Number(item.valor_costo||0),0)],
    ...d.top_products.map(item=>['Productos más vendidos',item.nombre,'','',item.quantity,'',item.amount]),
    ...d.categories.map(item=>['Ventas por categoría',item.category,'','', '', '',item.amount]),
    ...d.payments.map(item=>['Métodos de pago',item.method,'','', '',item.transactions,item.amount]),
    ...d.sellers.map(item=>['Ventas por vendedor',item.vendedor,'','', '',item.transactions,item.amount]),
    ...d.inventory.map(item=>['Inventario',item.nombre,item.sku,`${item.categoria} / ${item.marca}`,item.unidades,'',item.valor_costo])
  ];
  const content='sep=;\r\n'+rows.map(row=>row.map(reportCsvCell).join(';')).join('\r\n');
  const bytes=new Uint8Array(2+content.length*2);bytes[0]=0xFF;bytes[1]=0xFE;
  for(let i=0;i<content.length;i++){const code=content.charCodeAt(i);bytes[2+i*2]=code&0xFF;bytes[3+i*2]=code>>8;}
  const url=URL.createObjectURL(new Blob([bytes],{type:'text/csv;charset=utf-16le'})),link=document.createElement('a');
  link.href=url;link.download=`reporte_tyt_${d.desde}_${d.hasta}.csv`;document.body.appendChild(link);link.click();link.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
}
report$('runReport').addEventListener('click',requestReport);report$('reportStart').value=localDate(new Date(new Date().getFullYear(),new Date().getMonth(),1));report$('reportEnd').value=localDate(new Date());report$('exportReport').addEventListener('click',exportReportCsv);requestReport();
