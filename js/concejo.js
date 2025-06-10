"use strict";

/*
  Clase Concejo adaptada para utilizar la Visual Crossing Timeline Weather API.
  Esta versión muestra:
    1) El clima actual (currentConditions) para la ubicación especificada.
    2) Un pronóstico extendido de 7 días (excluyendo el día actual).
*/

class Concejo {
  /**
   * Constructor de Concejo.
   * @param {string} nombre - Nombre descriptivo del concejo (p.ej. "Lena").
   * @param {string} coordenadas - Latitud y longitud separadas por coma (p.ej. "43.158437,-5.828922").
   */
  constructor(nombre, coordenadas) {
    this.nombre = nombre;
    // Convertimos la cadena "lat,lon" a un array de floats: [lat, lon]
    this.coordenadas = coordenadas
      .split(',')
      .map(coord => parseFloat(coord.trim()));
    this.tiempoActual = {};     // Aquí almacenaremos los datos de currentConditions
    this.pronostico = [];       // Aquí almacenaremos el array de pronóstico diario
  }

  /**
   * Inicia la petición a la Visual Crossing Timeline API.
   * Se obtienen los datos de currentConditions y days.
   */
  obtenerDatos() {
    const apiKey = '4YN6GXVJJUP8H6FMT2FCV6U9P';
    const [latitud, longitud] = this.coordenadas;
    // Construimos la URL de la API. Pedimos:
    //   - unitGroup=metric para unidades métricas
    //   - include=current,days para traer currentConditions + pronóstico diario
    //   - lang=es para texto en español
    const url = `https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/${latitud},${longitud}?unitGroup=metric&include=current,days&lang=es&key=${apiKey}`;

    // Usamos fetch nativo para obtener los datos
    fetch(url)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Error en la petición: ${response.status} ${response.statusText}`);
        }
        return response.json();
      })
      .then(data => {
        this.procesarDatosVisualCrossing(data);
        this.mostrarPronostico();
      })
      .catch(error => {
        console.error('Error al obtener datos de Visual Crossing:', error);
      });
  }

  /**
   * Procesa la respuesta JSON de Visual Crossing.
   * @param {Object} data - El JSON retornado por la API.
   */
  procesarDatosVisualCrossing(data) {
    // currentConditions viene en data.currentConditions
    if (data.currentConditions) {
      const cc = data.currentConditions;
      this.tiempoActual = {
        temperatura: cc.temp.toFixed(1),
        humedad: cc.humidity,
        lluvia: cc.precip !== undefined ? cc.precip.toFixed(1) : '0.0'
      };
    } else {
      // Si no hay currentConditions, dejamos campos vacíos o en cero
      this.tiempoActual = {
        temperatura: 'N/A',
        humedad: 'N/A',
        lluvia: 'N/A'
      };
    }

    // Pronóstico diario viene en data.days como array. El primer elemento es el día actual.
    if (Array.isArray(data.days)) {
      // Queremos desde mañana (índice 1) hasta los próximos 7 días => índices 1..7 inclusive
      // Si hay menos de 8 elementos, tomamos todos excepto el índice 0
      const dias = data.days.slice(1, 8);
      this.pronostico = dias.map(dia => ({
        fecha: dia.datetime,               // p.ej. "2025-06-05"
        maxTemp: dia.tempmax.toFixed(1),    // máxima del día
        minTemp: dia.tempmin.toFixed(1),    // mínima del día
        humedad: dia.humidity.toFixed(1),   // humedad promedio
        lluvia: (dia.precip !== undefined ? dia.precip.toFixed(1) : '0.0') // precipitación total día
      }));
    } else {
      this.pronostico = [];
    }
  }

  /**
   * Inserta en el <main> de la página los datos actuales y el pronóstico extendido.
   */
  mostrarPronostico() {
    // Seleccionamos <main> y limpiamos cualquier contenido previo
    const contenedor = $('main');
    contenedor.empty();

    // Mostramos datos actuales
    const htmlActual = `
      <article>
        <h3>Clima actual en ${this.nombre}</h3>
        <p>Temperatura: ${this.tiempoActual.temperatura}°C</p>
        <p>Humedad: ${this.tiempoActual.humedad}%</p>
        <p>Precipitación (día): ${this.tiempoActual.lluvia} mm</p>
      </article>`;
    contenedor.append(htmlActual);

    // Título del bloque de pronóstico extendido
    contenedor.append(`
      <article>
        <h3>Pronóstico extendido (los próximos 7 días)</h3>
      </article>
    `);

    // Iteramos sobre cada día del pronóstico
    this.pronostico.forEach(dia => {
      const htmlDia = `
        <article>
          <h4>Fecha: ${dia.fecha}</h4>
          <p>Máxima: ${dia.maxTemp}°C</p>
          <p>Mínima: ${dia.minTemp}°C</p>
          <p>Humedad promedio: ${dia.humedad}%</p>
          <p>Precipitación acumulada: ${dia.lluvia} mm</p>
        </article>`;
      contenedor.append(htmlDia);
    });
  }
}

// Cuando el DOM esté listo, inicializamos y pedimos los datos para Lena
$(document).ready(() => {
  const lena = new Concejo("Lena", "43.158437,-5.828922");
  lena.obtenerDatos();
});
