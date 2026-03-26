(function(){
    let map, markers = [];

    function initMap(){
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: -27.45, lng: -58.99 },
            zoom: 12,
        });
    }

    function clearMarkers(){
        markers.forEach(m => m.setMap(null));
        markers = [];
    }

    function addMarker(lat, lng, title){
        const m = new google.maps.Marker({ position: {lat, lng}, map, title });
        markers.push(m);
    }

    async function fetchData(){
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
        const res = await fetch(`/registro-eti/informe?${params.toString()}`);
        const json = await res.json();
        render(json.data || []);
    }

    function render(data){
        clearMarkers();
        data.forEach(reg => {
            const paciente = reg.paciente || {};
            const lat = parseFloat(paciente.Latitud);
            const lng = parseFloat(paciente.Longitud);
            if(!isNaN(lat) && !isNaN(lng)){
                const title = `${paciente.ApellidoNombre || ''} - ${reg.Consulta || ''}`;
                addMarker(lat, lng, title);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        initMap();
        const btn = document.getElementById('btn_buscar');
        if(btn){ btn.addEventListener('click', fetchData); }
    });
})();