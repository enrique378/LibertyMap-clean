// Mapa
var map = L.map('map', {
zoom: 5,
minZoom: 5,
maxZoom: 10,
zoomControl: true,
scrollWheelZoom: true,
doubleClickZoom: true,
maxBounds: [
  [33.0, -118.2],
  [14.4, -86.5]
],
maxBoundsViscosity: 1.0,
touchZoom: true, 
boxZoom: true,
keyboard: true 
}).setView([23.6345, -102.5528], 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
attribution: '© OpenStreetMap contributors',
maxZoom: 19
}).addTo(map);

// Bordes de Estados
var estadoscom = {
"type": "FeatureCollection",
"features": [
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-117.23547301319679, 32.78209651209578],
        [-114.71917980809042, 32.7331002111104],
        [-114.95021067526096, 32.55773350399612],
        [-111.03305992844008, 31.28450020978356],
        [-108.2150716103202, 31.345898752853273],
        [-108.22028491397108, 31.722195190231176],
        [-106.49927315580297, 31.760548918995696],
        [-104.87073081222502, 30.511160807327457],
        [-104.62077025639081, 29.739303684824108],
        [-103.09521591341712, 28.89644061627463],
        [-102.7051374449114, 29.762543529744846],
        [-102.15201069677755, 29.869500975056496],
        [-101.3108449751584, 29.71067305553248],
        [-100.67991874089961, 29.175922265415295],
        [-100.31717086344457, 28.43170516430176],
        [-99.9011964132257, 27.824353057658712],
        [-99.48612186697468, 27.541088497649227],
        [-99.41561578414172, 26.83780527881585],
        [-99.02738372610997, 26.382835381556234],
        [-98.21232293043266, 26.090192469229976],
        [-97.59607828487594, 26.014136404061432],
        [-97.4017756124369, 25.882206811586556],
        [-97.15005543019609, 25.970209090710355]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-97.15002079277594, 25.957215159880216],
        [-97.13344259829309, 25.677911597286197],
        [-97.48417307622002, 25.10011881151975],
        [-97.53575123631198, 25.342755501789227],
        [-97.71111643373047, 25.277477877306694],
        [-97.78332642327527, 22.80955353948174],
        [-97.89679907498702, 22.676366061217962],
        [-97.84521984735083, 22.419132974905267],
        [-97.65953881039155, 21.89367191756449]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-97.6613855483922, 21.895343257819056],
        [-97.23122885809232, 21.52643925922105],
        [-97.46966404427661, 21.24773731719317],
        [-97.11989725391726, 20.593172179373127],
        [-96.34436655779331, 19.742063879915136],
        [-96.30873870704013, 19.316353201195554],
        [-95.87159575549202, 18.890563114354606],
        [-95.56746875364851, 18.63720769294548],
        [-95.08461577973875, 18.61774154793423],
        [-94.37021082401867, 18.139362171209214],
        [-93.30240508595955, 18.386122674229895]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-93.35240587475316, 18.386122674229895],
        [-92.33956925783345, 18.668487942322628],
        [-91.78517433113616, 18.698786748363602],
        [-90.68704596847518, 19.40420368416939],
        [-90.67638452192476, 19.846055543584896],
        [-90.4205101137272, 19.916237314765723],
        [-90.46315565978158, 20.81577433750813],
        [-89.94074516717667, 21.233742568264276],
        [-88.33086795632472, 21.551402620374574],
        [-86.98752592702681, 21.52165187785438],
        [-86.74231278637198, 21.144277004146858],
        [-86.96620433816565, 20.656240740632924],
        [-86.64635981829021, 20.546465247864973],
        [-87.04083313151683, 20.186648017591438],
        [-87.4353078441132, 20.116585841726533],
        [-87.43530644474342, 19.273425383569688],
        [-87.81911831707242, 18.14245486535654],
        [-88.03234740388326, 18.172846489350903],
        [-88.04175921002766, 18.415788243458437],
        [-88.27631126363188, 18.405672542110793],
        [-88.48954137822906, 18.486581868123324],
        [-88.85203260635663, 17.888989731182818],
        [-89.1079088393101, 17.980281035592995],
        [-89.13989198955377, 17.81795485481416],
        [-90.99497388272982, 17.807802883445845],
        [-91.00563460389976, 17.228290951842595],
        [-91.46406943945355, 17.299564445965913],
        [-90.40859645589634, 16.39142563306801],
        [-90.49388718390907, 16.063860456421068],
        [-91.77319953283879, 16.084438115542113],
        [-92.2102434519308, 15.273668505019728],
        [-92.07169123187849, 15.078105040520839]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-92.06894410521245, 15.086322204702796],
        [-92.1967484092626, 14.849412408557214],
        [-92.16475193931555, 14.694774418758196],
        [-92.25003799132526, 14.46777655880119],
        [-93.56139983756209, 15.764599863397649],
        [-94.45694983448772, 16.17459925979854],
        [-95.24589347964154, 16.164358186637386],
        [-96.2267372711202, 15.67223626144552],
        [-97.33552808090108, 15.836411328563244],
        [-98.61489776614543, 16.31789819271529],
        [-98.71084458923194, 16.46109128432738],
        [-98.82811840616837, 16.542868848773068],
        [-99.89425139109481, 16.80840554700582]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-99.9616699715472, 16.83091826287786],
        [-101.67351018964544, 17.652529995969488],
        [-102.02553238234941, 17.952922572281437],
        [-103.23817062984779, 18.196295086285232],
        [-103.50686900368282, 18.383448004433532],
        [-103.80516690285833, 18.7309501186715],
        [-105.04847329807126, 19.386804665734218],
        [-105.74051309582552, 20.355032343963842],
        [-105.19655099053861, 20.658637812287736],
        [-105.56258139305928, 20.868165585048985],
        [-105.24773089232673, 21.07384550277642],
        [-105.27058638741292, 21.47654475427946],
        [-105.69379651942899, 22.05099097871299],
        [-105.72030160200484, 22.456485501464],
        [-107.33334401034395, 24.26744979332247],
        [-107.67667192204965, 24.33495249557305],
        [-107.78684819593616, 24.362182574562738]
      ],
      "type": "LineString"
    }
  },
  {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "coordinates": [
        [-107.76713685177668, 24.37858299228489],
        [-108.46532150387924, 25.300581452932647],
        [-109.51814554755867, 25.839081163089602],
        [-109.19827850939768, 26.258344424308987],
        [-109.44644183514458, 26.705846645096514],
        [-109.86903781450735, 26.730016668760683],
        [-110.59203605166601, 27.555988104291586],
        [-110.55123725989131, 27.832933190226044],
        [-111.14087668265597, 27.984533220245112],
        [-111.95269161938319, 28.67276226929316],
        [-112.5110164507347, 28.786576666252913],
        [-112.42069119332453, 29.441957816716297],
        [-113.15674653723164, 31.042672537890766],
        [-113.62571945536035, 31.407866587449718],
        [-114.86435669673085, 31.835328008374432],
        [-114.56834494989596, 30.134519481654436],
        [-113.63946856490634, 29.336950979394317],
        [-113.5319921309287, 29.63362145708915],
        [-113.03671739709478, 29.160852614198973],
        [-113.1810568119908, 28.73433081532498],
        [-111.42651393399905, 26.43683010520452],
        [-111.0866804730154, 25.48552590233919],
        [-110.51204645484961, 24.88684342211998],
        [-110.67848185414115, 24.360259560535994],
        [-110.29919430030915, 24.420666217635556],
        [-109.36965148501275, 23.348221183821266],
        [-109.86054769650963, 22.82192702212565],
        [-110.27244540882901, 23.101270650510074],
        [-110.28791750293951, 23.52131715283346],
        [-111.20079814202626, 24.3329012999082],
        [-111.90208479361758, 24.270130408665494],
        [-112.4963721406304, 24.847641425979674],
        [-112.0638639860162, 25.24344850748318],
        [-112.3547084420722, 26.160367500643844],
        [-113.14029078185382, 26.596930625706833],
        [-113.16074385379045, 26.841550545185726],
        [-113.7986282172912, 26.605936509423557],
        [-115.07977435166345, 27.79314594222059],
        [-114.26423341522451, 27.92200339569868],
        [-114.10777855863881, 28.43089727449822],
        [-114.95861512296898, 29.304167486461438],
        [-115.78140970856225, 29.8508972507081],
        [-117.23639663560937, 32.76213910016966]
      ],
      "type": "LineString"
    }
  }
]
};

L.geoJson(estadoscom, {
style: {
  color: '#ff6b6b',
  weight: 3,
  opacity: 0.6
}
}).addTo(map);

// Variables
var marcadores = [];
var preguntasPorBatalla = {};
var batallasData = [];
var filtrosActivos = {
  año: '',
  estado: '',
  busqueda: ''
};

// Cargar Preguntas
fetch('Conexiones/obtener_preguntas.php')
.then(res => {
  if (!res.ok) {
    throw new Error('Error al cargar preguntas: ' + res.status);
  }
  return res.json();
})
.then(data => {
  console.log("Preguntas recibidas:", data);
  
  // ✅ CAMBIO: Usar idBatalla directamente en lugar de calcular
  data.forEach(item => {
    const batallaId = item.idBatalla; // Usar el ID de batalla del PHP
    
    if (!batallaId) {
      console.warn('Pregunta sin idBatalla:', item);
      return; // Saltar preguntas sin batalla asociada
    }
    
    if (!preguntasPorBatalla[batallaId]) {
      preguntasPorBatalla[batallaId] = [];
    }
    
    preguntasPorBatalla[batallaId].push({
      idPregunta: item.idPregunta,
      pregunta: item.pregunta,
      respuesta: item.respuesta
    });
  });
  
  console.log("Preguntas agrupadas por batalla:", preguntasPorBatalla);
})
.catch(error => {
  console.error('Error al cargar preguntas:', error);
});

// Mostrar Preguntas
function mostrarPreguntas(batallaId) {
const preguntas = preguntasPorBatalla[batallaId];

if (!preguntas || preguntas.length === 0) {
  return '<p style="color: #718096; font-style: italic;">No hay preguntas disponibles para esta batalla.</p>';
}

let html = '<div class="preguntas-section">';
html += '<h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;"> Preguntas de Estudio</h3>';

preguntas.forEach((p, index) => {
  html += `
    <div class="pregunta-item">
      <p style="margin-bottom: 8px;"><strong>Pregunta ${index + 1}:</strong> ¿${p.pregunta}?</p>
      <details>
        <summary>Ver respuesta</summary>
        <p>${p.respuesta}</p>
      </details>
    </div>
  `;
});

html += '</div>';
return html;
}

// Cargar Batallas y Marcadores
fetch('Conexiones/obtener_batallas.php')
.then(res => {
  if (!res.ok) {
    throw new Error('Error al cargar batallas: ' + res.status);
  }
  return res.json();
})
.then(data => {
  console.log("Batallas recibidas:", data);

  if (!Array.isArray(data) || data.length === 0) {
    console.warn('No se encontraron batallas');
    return;
  }

  batallasData = data;
  crearMarcadores(data);
})
.catch(error => {
  console.error('Error al cargar batallas:', error);
});

// Crear marcadores en el mapa
function crearMarcadores(batallas) {
  marcadores.forEach(m => map.removeLayer(m));
  marcadores = [];

  batallas.forEach(item => {
    if (!item.latitud || !item.longitud || !item.nombre) {
      console.warn('Batalla con datos incompletos:', item);
      return;
    }

    var marker = L.marker([parseFloat(item.latitud), parseFloat(item.longitud)], {
      icon: L.divIcon({
        html: '<div style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); border: 3px solid white; cursor: pointer;">⚔️</div>',
        className: '',
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -36]
      })
    }).addTo(map);
    
    console.log(`Marcador agregado: ${item.nombre} en [${item.latitud}, ${item.longitud}]`);

    marker.idBatalla = parseInt(item.id);
    marker.nombreBatalla = item.nombre;
    marker.fechaBatalla = item.fecha;
    marker.estadoBatalla = item.estado;
    marcadores.push(marker);

    marker.bindPopup(`<b>${item.nombre}</b>`);

    marker.on('click', function() {
      const batallaId = this.idBatalla;
      console.log("Cargando batalla con ID:", batallaId);
      
      fetch(`Conexiones/obtener_batalla.php?id=${batallaId}`)
        .then(res => {
          if (!res.ok) {
            throw new Error('Error al cargar batalla: ' + res.status);
          }
          return res.json();
        })
        .then(data => {
          console.log("Datos recibidos:", data);
          
          const batalla = Array.isArray(data) ? data.find(b => b.id == batallaId) : data;
          
          if (!batalla) {
            console.error('No se encontró la batalla con ID:', batallaId);
            return;
          }

          if (batalla.latitud && batalla.longitud) {
            const lat = parseFloat(batalla.latitud);
            const lng = parseFloat(batalla.longitud);
            
            map.setView([lat, lng], 8);
            
            setTimeout(() => {
              if (window.innerWidth > 1024) {
                const currentCenter = map.getCenter();
                const panelWidth = 420;
                const mapWidth = map.getSize().x;
                const bounds = map.getBounds();
                const lngDiff = bounds.getEast() - bounds.getWest();
                const offset = (panelWidth / mapWidth) * lngDiff / 2;
                
                map.setView([currentCenter.lat, currentCenter.lng + offset], 8, {animate: true});
              }
            }, 100);
          }

          mostrarInfoPanel(batalla, batallaId);
          
        })
        .catch(error => {
          console.error('Error al obtener detalles de batalla:', error);
          mostrarError();
        });
    });
  });
}

// Función para formatear fechas
function formatearFecha(fecha) {
  console.log("Fecha recibida:", fecha, "Tipo:", typeof fecha); // Debug
  
  if (!fecha || fecha === '0000-00-00' || fecha === '' || fecha === null) {
    return "Fecha desconocida";
  }

  const meses = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
  ];

  try {
    // Si la fecha viene como string en formato YYYY-MM-DD
    const partes = fecha.toString().split('-');
    
    if (partes.length === 3) {
      const anio = partes[0];
      const mes = parseInt(partes[1]) - 1;
      const dia = parseInt(partes[2]);
      
      // Validar que los valores sean válidos
      if (anio && anio !== '0000' && mes >= 0 && mes < 12 && dia > 0 && dia <= 31) {
        return `${dia} de ${meses[mes]} de ${anio}`;
      }
    }
    
    // Si no se pudo formatear, devolver la fecha tal cual
    return fecha.toString();
  } catch (e) {
    console.error("Error al formatear fecha:", e);
    return fecha.toString();
  }
}

// Mostrar Panel de Información
function mostrarInfoPanel(batalla, batallaId) {
const infoPanel = document.getElementById('infoPanel');
const infoContent = document.getElementById('infoContent');
const panelOverlay = document.getElementById('panelOverlay');

if (!infoPanel || !infoContent) {
  console.error('No se encontraron los elementos del panel');
  return;
}

infoContent.innerHTML = `
  <h2>${batalla.nombre || 'Sin nombre'}</h2>
  <p><strong> Fecha:</strong> ${formatearFecha(batalla.fecha) || 'No disponible'}</p>
  <p><strong> Personajes:</strong> ${batalla.personajes || 'No disponible'}</p>
  <p><strong> Ganador:</strong> ${batalla.ganador || 'No disponible'}</p>
  <p><strong> Ubicación:</strong> ${batalla.ciudad || 'N/A'}, ${batalla.estado || 'N/A'}</p>
  <p><strong> Descripción:</strong> ${batalla.descripcion || 'No disponible'}</p>
  <hr>
  ${mostrarPreguntas(batallaId)}
`;

infoPanel.classList.add('active');
panelOverlay.classList.add('active');
}

// Cerrar Panel
function cerrarPanel() {
const infoPanel = document.getElementById('infoPanel');
const panelOverlay = document.getElementById('panelOverlay');

if (infoPanel) {
  infoPanel.classList.remove('active');
}
if (panelOverlay) {
  panelOverlay.classList.remove('active');
}
}

// Mostrar Error
function mostrarError() {
const infoContent = document.getElementById('infoContent');
if (infoContent) {
  infoContent.innerHTML = `
    <h2>Error</h2>
    <p style="color: #e53e3e;">No se pudo cargar la información de la batalla.</p>
  `;
}
}

// Batalla Aleatoria
window.mostrarBatallaAleatoria = function() {
const batallasVisibles = marcadores.filter(m => map.hasLayer(m));
  
if (batallasVisibles.length === 0) {
  alert('No hay batallas disponibles con los filtros actuales');
  return;
}

const randomIndex = Math.floor(Math.random() * batallasVisibles.length);
const markerAleatorio = batallasVisibles[randomIndex];
const batallaId = markerAleatorio.idBatalla;

console.log("Cargando detalles de batalla aleatoria ID:", batallaId);

fetch(`Conexiones/obtener_batalla.php?id=${batallaId}`)
  .then(res => {
    if (!res.ok) {
      throw new Error('Error al cargar batalla: ' + res.status);
    }
    return res.json();
  })
  .then(detalleData => {
    const batalla = Array.isArray(detalleData) ? detalleData.find(b => b.id == batallaId) : detalleData;
    
    if (!batalla) {
      console.error('No se encontró la batalla con ID:', batallaId);
      return;
    }

    if (batalla.latitud && batalla.longitud) {
      const lat = parseFloat(batalla.latitud);
      const lng = parseFloat(batalla.longitud);
      
      map.setView([lat, lng], 8);
      
      setTimeout(() => {
        if (window.innerWidth > 1024) {
          const currentCenter = map.getCenter();
          const panelWidth = 420;
          const mapWidth = map.getSize().x;
          const bounds = map.getBounds();
          const lngDiff = bounds.getEast() - bounds.getWest();
          const offset = (panelWidth / mapWidth) * lngDiff / 2;
          
          map.setView([currentCenter.lat, currentCenter.lng + offset], 8, {animate: true});
        }
      }, 100);
    }

    mostrarInfoPanel(batalla, batallaId);
    
    setTimeout(() => {
      markerAleatorio.openPopup();
    }, 500);
  })
  .catch(error => {
    console.error('Error al obtener batalla aleatoria:', error);
    mostrarError();
  });
};

// FUNCIONES DE FILTROS

// Abrir modal de filtros
window.abrirFiltros = function() {
  console.log("Abriendo modal de filtros");
  const modal = document.getElementById('filterModal');
  const overlay = document.getElementById('filterModalOverlay');
  
  if (modal && overlay) {
    modal.classList.add('active');
    overlay.classList.add('active');
  } else {
    console.error("No se encontraron elementos del modal");
  }
};

// Cerrar modal de filtros
window.cerrarFiltros = function() {
  console.log("Cerrando modal de filtros");
  const modal = document.getElementById('filterModal');
  const overlay = document.getElementById('filterModalOverlay');
  
  if (modal && overlay) {
    modal.classList.remove('active');
    overlay.classList.remove('active');
  }
};

// Aplicar filtros
window.aplicarFiltros = function() {
  console.log("Aplicando filtros...");
  
  const añoElement = document.getElementById('filterYear');
  const estadoElement = document.getElementById('filterState');
  const busquedaElement = document.getElementById('searchBattle');
  
  if (!añoElement || !estadoElement || !busquedaElement) {
    console.error("No se encontraron los elementos de filtro");
    return;
  }
  
  const año = añoElement.value;
  const estado = estadoElement.value;
  const busqueda = busquedaElement.value.toLowerCase().trim();

  console.log("Filtros aplicados:", { año, estado, busqueda });

  filtrosActivos = { año, estado, busqueda };

  const batallasFiltradas = batallasData.filter(batalla => {
    let cumpleAño = true;
    let cumpleEstado = true;
    let cumpleBusqueda = true;

    // Filtro por año
    if (año) {
      cumpleAño = batalla.fecha && batalla.fecha.startsWith(año);
    }

    // Filtro por estado
    if (estado) {
      cumpleEstado = batalla.estado && batalla.estado.trim() === estado.trim();
    }

    // Filtro por búsqueda
    if (busqueda) {
      cumpleBusqueda = batalla.nombre && batalla.nombre.toLowerCase().includes(busqueda);
    }

    return cumpleAño && cumpleEstado && cumpleBusqueda;
  });

  console.log(`Batallas filtradas: ${batallasFiltradas.length} de ${batallasData.length}`);

  crearMarcadores(batallasFiltradas);
  cerrarFiltros();

  if (batallasFiltradas.length === 0) {
    alert('No se encontraron batallas con los filtros seleccionados');
  } else {
    // Ajustar el zoom del mapa para mostrar todas las batallas filtradas
    if (batallasFiltradas.length > 0 && batallasFiltradas.every(b => b.latitud && b.longitud)) {
      try {
        const bounds = L.latLngBounds(
          batallasFiltradas.map(b => [parseFloat(b.latitud), parseFloat(b.longitud)])
        );
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 8 });
      } catch(e) {
        console.error("Error ajustando zoom:", e);
      }
    }
  }
};

// Limpiar filtros
window.limpiarFiltros = function() {
  console.log("Limpiando filtros");
  
  const yearElement = document.getElementById('filterYear');
  const stateElement = document.getElementById('filterState');
  const searchElement = document.getElementById('searchBattle');
  const resultsElement = document.getElementById('searchResults');
  
  if (yearElement) yearElement.value = '';
  if (stateElement) stateElement.value = '';
  if (searchElement) searchElement.value = '';
  if (resultsElement) {
    resultsElement.innerHTML = '';
    resultsElement.style.display = 'none';
  }
  
  filtrosActivos = { año: '', estado: '', busqueda: '' };
  
  crearMarcadores(batallasData);
  cerrarFiltros();
  
  // Resetear vista del mapa
  map.setView([23.6345, -102.5528], 5);
};

// Buscador en tiempo real
document.addEventListener('DOMContentLoaded', function() {
  console.log("Inicializando buscador en tiempo real");
  
  const searchInput = document.getElementById('searchBattle');
  const searchResults = document.getElementById('searchResults');

  if (searchInput && searchResults) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      
      if (query.length < 2) {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
        return;
      }

      const resultados = batallasData.filter(batalla => 
        batalla.nombre && batalla.nombre.toLowerCase().includes(query)
      );

      if (resultados.length > 0) {
        searchResults.innerHTML = resultados.map(batalla => `
          <div class="search-result-item" onclick="seleccionarBatalla(${batalla.id}, '${batalla.nombre.replace(/'/g, "\\'")}')">
            <strong>${batalla.nombre}</strong>
            <span>${formatearFecha(batalla.fecha)} - ${batalla.estado || 'Sin ubicación'}</span>
          </div>
        `).join('');
        searchResults.style.display = 'block';
      } else {
        searchResults.innerHTML = '<div class="search-result-item">No se encontraron resultados</div>';
        searchResults.style.display = 'block';
      }
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });
  } else {
    console.warn("No se encontraron elementos de búsqueda");
  }
});

// Seleccionar batalla desde el buscador
window.seleccionarBatalla = function(batallaId, nombreBatalla) {
  console.log("Batalla seleccionada:", batallaId, nombreBatalla);
  
  const searchInput = document.getElementById('searchBattle');
  const searchResults = document.getElementById('searchResults');
  
  if (searchInput) searchInput.value = nombreBatalla;
  if (searchResults) {
    searchResults.innerHTML = '';
    searchResults.style.display = 'none';
  }
  
  const marcadorEncontrado = marcadores.find(m => m.idBatalla === batallaId);
  
  if (marcadorEncontrado) {
    cerrarFiltros();
    
    const lat = marcadorEncontrado.getLatLng().lat;
    const lng = marcadorEncontrado.getLatLng().lng;
    
    map.setView([lat, lng], 8);
    
    setTimeout(() => {
      marcadorEncontrado.fire('click');
    }, 300);
  } else {
    console.warn("No se encontró el marcador para la batalla:", batallaId);
  }
};

// Toggle Barra Lateral
document.getElementById('toggleSidebar').addEventListener('click', function() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('active');
});

// Cerrar Barra Lateral
document.querySelectorAll('.nav-item').forEach(item => {
  item.addEventListener('click', function() {
    if (window.innerWidth <= 1024) {
      document.getElementById('sidebar').classList.remove('active');
    }
  });
});

// Cerrar Panel con Tecla ESC
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    cerrarPanel();
    cerrarFiltros();
  }
});

// Cambiar Tema
function toggleTheme() {
  document.body.classList.toggle('dark-mode');

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('theme', 'dark');
  } else {
    localStorage.setItem('theme', 'light');
  }
}

// Cargar Tema Guardado
document.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
  }
});