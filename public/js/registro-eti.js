(function(){
    async function buscarDni(){
        const dni = document.getElementById('dni_buscar').value.trim();
        if(!dni){ alert('Ingrese DNI'); return; }
        const res = await fetch(`/registro-eti/buscar-dni?dni=${encodeURIComponent(dni)}`);
        const json = await res.json();
        if(json && json.paciente){
            fillPaciente(json.paciente);
            document.getElementById('form_persona').style.display = 'block';
        }else{
            // limpiar y mostrar form
            clearPaciente();
            document.getElementById('form_persona').style.display = 'block';
        }
    }

    function fillPaciente(p){
        setVal('dni', p.DNI);
        setVal('apellido_nombre', p.ApellidoNombre);
        setVal('sexo', p.Sexo);
        setVal('fecha_nacimiento', p.FechaNacimiento);
        setVal('celular', p.Celular);
        setVal('domicilio', p.Domicilio);
        setVal('departamento', p.Departamento);
        setVal('localidad', p.Localidad);
        setVal('barrio', p.Barrio);
        setVal('referencias', p.Referencias);
        setVal('latitud', p.Latitud);
        setVal('longitud', p.Longitud);
    }

    function clearPaciente(){
        ['dni','apellido_nombre','sexo','fecha_nacimiento','celular','domicilio','departamento','localidad','barrio','referencias','latitud','longitud'].forEach(id=> setVal(id, ''));
    }

    function setVal(id, val){
        const el = document.getElementById(id);
        if(el) el.value = val == null ? '' : val;
    }

    async function guardar(){
        const payload = collectFormData();
        const res = await fetch('/registro-eti', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=>({ ok:false }));
        if(res.ok && json){
            alert('Registro guardado');
            filtrar();
        }else{
            alert('Error al guardar');
        }
    }

    function collectFormData(){
        return {
            // paciente
            DNI: getVal('dni'),
            ApellidoNombre: getVal('apellido_nombre'),
            Sexo: getVal('sexo'),
            FechaNacimiento: getVal('fecha_nacimiento'),
            Celular: getVal('celular'),
            Domicilio: getVal('domicilio'),
            Departamento: getVal('departamento'),
            Localidad: getVal('localidad'),
            Barrio: getVal('barrio'),
            Referencias: getVal('referencias'),
            Latitud: getVal('latitud'),
            Longitud: getVal('longitud'),
            // registro eti
            Efector_Id: getVal('efector_sel'),
            Semana: getVal('semana'),
            Fis: getVal('fis'),
            Consulta: getVal('consulta'),
            FechaTomaMuestra: getVal('fecha_muestra'),
            Laboratorio: getVal('laboratorio'),
            Observaciones: getVal('observaciones'),
            febril: 2
        };
    }

    function getVal(id){
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    async function filtrar(){
        const params = new URLSearchParams();
        const d = document.getElementById('f_desde').value;
        const h = document.getElementById('f_hasta').value;
        const usuario = document.getElementById('usuario_sel').value;
        const efector = document.getElementById('efector_sel').value;
        const region = document.getElementById('region_sel').value;
        if(d) params.append('d', d);
        if(h) params.append('h', h);
        if(usuario) params.append('id_usuario', usuario);
        if(efector) params.append('efector', efector);
        if(region) params.append('reg', region);
        const res = await fetch(`/registro-eti/filtrar?${params.toString()}`);
        const json = await res.json();
        renderTabla(json.data || []);
    }

    function renderTabla(data){
        const tbody = document.querySelector('#lista tbody');
        if(!tbody) return;
        tbody.innerHTML = '';
        data.forEach(reg => {
            const p = reg.paciente || {};
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.DNI || ''}</td>
                <td>${p.ApellidoNombre || ''}</td>
                <td>${reg.Consulta || ''}</td>
                <td>${reg.Semana || ''}</td>
                <td>${reg.Efector ? (reg.Efector.Nombre || '') : ''}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        const btnBuscar = document.getElementById('btn_buscar_dni');
        const btnGuardar = document.getElementById('btn_guardar');
        const btnFiltrar = document.getElementById('btn_filtrar');
        if(btnBuscar) btnBuscar.addEventListener('click', buscarDni);
        if(btnGuardar) btnGuardar.addEventListener('click', guardar);
        if(btnFiltrar) btnFiltrar.addEventListener('click', filtrar);
        // carga inicial de lista
        filtrar();
    });
})();