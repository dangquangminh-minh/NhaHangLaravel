/* Presentation helpers. Business actions remain in app.js and server policies. */
(() => {
    const syncTables = root => {
        (root || document).querySelectorAll('table').forEach(table => {
            const boxes = [...table.querySelectorAll('tbody input[type="checkbox"]')];
            boxes.forEach(box => box.closest('tr')?.classList.toggle('is-selected', box.checked));
            const all = table.querySelector('[data-check-all]');
            if (all && boxes.length) { all.checked = boxes.every(box => box.checked); all.indeterminate = !all.checked && boxes.some(box => box.checked); }
        });
    };
    const enhance = (root = document) => {
        root.querySelectorAll('.table-responsive').forEach(frame => {
            if (frame.dataset.uiFrame) { return; }
            frame.dataset.uiFrame = 'true'; frame.tabIndex = 0; frame.setAttribute('role','region');
            frame.setAttribute('aria-label','Bảng dữ liệu; có thể cuộn ngang hoặc dọc để xem đầy đủ');
            frame.addEventListener('scroll', () => frame.classList.toggle('is-scrolled',frame.scrollTop > 0), {passive:true});
        });
        root.querySelectorAll('table input[type="checkbox"]').forEach(box => {
            if (box.closest('.app-checkbox-hit')) { return; }
            const label = document.createElement('label'); label.className = 'app-checkbox-hit';
            box.before(label); label.append(box);
        });
        root.querySelectorAll('.badge-status').forEach(badge => {
            const text=badge.textContent.trim().toLowerCase();
            badge.dataset.uiStatus=/hủy|ngừng/.test(text)?'danger':/chờ/.test(text)?'warning':/thành công|thanh toán/.test(text)?'success':'info';
        });
        root.querySelectorAll('.table td').forEach(cell=>{
            if(cell.children.length || cell.textContent.trim().length<90) { return; }
            const text=cell.textContent.trim(),span=document.createElement('span');span.className='app-truncated';span.textContent=text;span.title=text;span.tabIndex=0;span.dataset.uiTooltip='true';cell.replaceChildren(span);
            if(window.bootstrap?.Tooltip) { new bootstrap.Tooltip(span,{trigger:'hover focus'}); }
        });
        root.querySelectorAll('.table .action-row a.btn').forEach(link => {
            if(link.dataset.uiTooltip) { return; }
            const action=link.textContent.trim();
            const icon=action==='Chi tiết'?'eye':action==='Sửa'?'form':null;
            if(!icon) { return; }
            link.dataset.uiTooltip='true';
            const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');svg.classList.add('ui-icon');svg.setAttribute('aria-hidden','true');
            const use=document.createElementNS(svg.namespaceURI,'use');use.setAttribute('href','#icon-'+icon);svg.append(use);link.prepend(svg);
            if(window.bootstrap?.Tooltip) { new bootstrap.Tooltip(link,{title:action,trigger:'hover focus'}); }
        });
        syncTables(root);
    };
    const dispose = root => root.querySelectorAll('[data-ui-tooltip]').forEach(e=>window.bootstrap?.Tooltip?.getInstance(e)?.dispose());
    const confirm = (message, {danger=false, trigger=document.activeElement, name=''} = {}) => {
        const element=document.getElementById('action-confirmation');
        if(!element || !window.bootstrap?.Modal) { return Promise.resolve(window.confirm(message+(name?'\nĐối tượng: '+name:''))); }
        if(element.classList.contains('show')) { return Promise.resolve(false); }
        document.getElementById('action-confirmation-message').textContent=message+(name?'\nĐối tượng: '+name:'');
        document.getElementById('action-confirmation-title').textContent=danger?'Xác nhận thay đổi dữ liệu':'Xác nhận thao tác';
        const button=element.querySelector('[data-ui-confirm]');button.className='btn '+(danger?'btn-danger':'btn-primary');
        const modal=bootstrap.Modal.getOrCreateInstance(element);
        return new Promise(resolve=>{
            let accepted=false;
            const accept=()=>{accepted=true;modal.hide();};
            button.addEventListener('click',accept,{once:true});
            element.addEventListener('hidden.bs.modal',()=>{
                button.removeEventListener('click',accept);
                if(trigger?.isConnected) { requestAnimationFrame(()=>trigger.focus({preventScroll:true})); }
                resolve(accepted);
            },{once:true});
            modal.show();
        });
    };
    const confirmationName = form => {
        if(form.dataset.confirmName) { return form.dataset.confirmName; }
        const row=form.closest('tr');
        const selected=form.id?[...document.querySelectorAll('input[name="ids[]"]:checked')].filter(e=>e.form===form):[];
        if(selected.length) { return selected.map(e=>e.getAttribute('aria-label')?.replace(/^Chọn /,'')||e.value).join(', ').slice(0,300); }
        const box=row?.querySelector('input[name="ids[]"]');
        if(box) { return box.getAttribute('aria-label')?.replace(/^Chọn /,'')||''; }
        if(row) { return row.cells[0]?.textContent.trim().slice(0,180)||''; }
        return document.querySelector('main h1')?.textContent.trim()||'';
    };
    const markSubmitting = (form,button) => {
        if(form.method.toLowerCase()==='get') { return; }
        form.dataset.uiSubmitting='true';form.setAttribute('aria-busy','true');
        if(!button) { return; }
        if(!button.querySelector('.ui-button-label')) {
            const label=document.createElement('span');label.className='ui-button-label';
            while(button.firstChild) { label.append(button.firstChild); }button.append(label);
            const spinner=document.createElement('span');spinner.className='ui-spinner';spinner.setAttribute('aria-hidden','true');button.append(spinner);
        }
        button.classList.add('is-loading');button.setAttribute('aria-disabled','true');button.setAttribute('aria-busy','true');
    };
    const setLoading = (results,loading) => {
        results.setAttribute('aria-busy',String(loading));
        const previous=results.querySelector(':scope > .app-loading-cover');
        if(!loading) { previous?.remove();return; }
        if(previous) { return; }
        const cover=document.createElement('div');cover.className='app-loading-cover';cover.setAttribute('aria-hidden','true');
        for(let i=0;i<3;i++) { const row=document.createElement('div');row.className='ui-skeleton-row';for(const name of ['ui-skeleton-avatar','ui-skeleton-name','ui-skeleton-amount']) { const cell=document.createElement('span');cell.className='ui-skeleton '+name;row.append(cell); }cover.append(row); }
        results.prepend(cover);
    };
    const showRetry = (status,message,retry) => {
        status.classList.remove('visually-hidden');status.classList.add('app-search-error');status.setAttribute('role','alert');status.textContent=message+' ';
        const button=document.createElement('button');button.type='button';button.className='btn btn-outline-danger';button.textContent='Thử lại';button.addEventListener('click',retry,{once:true});status.append(button);
    };
    const resetSubmitting = () => {
        document.querySelectorAll('[data-ui-submitting]').forEach(form=>{delete form.dataset.uiSubmitting;form.removeAttribute('aria-busy');});
        document.querySelectorAll('.btn.is-loading').forEach(button=>{button.classList.remove('is-loading');button.removeAttribute('aria-disabled');button.removeAttribute('aria-busy');});
    };
    window.addEventListener('pageshow',resetSubmitting);
    window.RestaurantUI = {enhance, syncTables, dispose, confirm, confirmationName, markSubmitting, setLoading, showRetry};
    enhance();
    document.querySelectorAll('[data-ui-success]').forEach(alert=>{
        let timer;
        const schedule=()=>{clearTimeout(timer);timer=setTimeout(()=>{if(!alert.matches(':focus-within')) { bootstrap.Alert.getOrCreateInstance(alert).close(); }},7000);};
        alert.addEventListener('mouseenter',()=>clearTimeout(timer));alert.addEventListener('focusin',()=>clearTimeout(timer));alert.addEventListener('mouseleave',schedule);alert.addEventListener('focusout',schedule);schedule();
    });
    const sidebarToggle=document.querySelector('[data-ui-sidebar-toggle]');
    if(sidebarToggle) {
        const apply=collapsed=>{document.body.classList.toggle('is-sidebar-collapsed',collapsed);sidebarToggle.setAttribute('aria-expanded',String(!collapsed));sidebarToggle.setAttribute('aria-label',collapsed?'Hiện thanh quản trị':'Ẩn thanh quản trị');};
        try { apply(localStorage.getItem('restaurant-sidebar-collapsed')==='true'); } catch {}
        sidebarToggle.addEventListener('click',()=>{const collapsed=!document.body.classList.contains('is-sidebar-collapsed');apply(collapsed);try{localStorage.setItem('restaurant-sidebar-collapsed',String(collapsed));}catch{}});
    }
    const invalid=document.querySelector('main [aria-invalid="true"]');
    if(invalid) { invalid.focus({preventScroll:true}); }

    document.addEventListener('click',event=>{if(event.target.closest('[data-ui-focus-search]')) { document.getElementById('search-q')?.focus(); }});
    document.addEventListener('change', event => { if(event.target.matches('table input[type="checkbox"]')) { setTimeout(()=>syncTables(),0); } });
    const header = document.querySelector('.ui-topbar');
    const onScroll = () => header?.classList.toggle('is-scrolled',scrollY > 4);
    window.addEventListener('scroll',onScroll,{passive:true});onScroll();
})();
