"use strict";

class Concejo {
  /**
   * Constructor de Concejo.
   */
  constructor(nombre, coordenadas) {
    this.nombre = nombre;
    this.coordenadas = coordenadas
      .split(',')
      .map(coord => parseFloat(coord.trim()));
    this.tiempoActual = {};   
    this.pronostico = [];       
  }

  /**
   * Inicia la petición a la Visual Crossing Timeline API.
   * Se obtienen los datos de currentConditions y days.
   */
  obtenerDatos() {
    const apiKey = 'YOUR_API_KEY';
    const [latitud, longitud] = this.coordenadas;
    const url = `https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/${latitud},${longitud}?unitGroup=metric&include=current,days&lang=es&key=${apiKey}`;

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
      this.tiempoActual = {
        temperatura: 'N/A',
        humedad: 'N/A',
        lluvia: 'N/A'
      };
    }

    // Pronóstico diario viene en data.days como array. El primer elemento es el día actual.
    if (Array.isArray(data.days)) {
      const dias = data.days.slice(1, 8);
      this.pronostico = dias.map(dia => ({
        fecha: dia.datetime,              
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
    const contenedor = $('main');
    contenedor.empty();

    const htmlActual = `
      <article>
        <h3>Clima actual en ${this.nombre}</h3>
        <p>Temperatura: ${this.tiempoActual.temperatura}°C</p>
        <p>Humedad: ${this.tiempoActual.humedad}%</p>
        <p>Precipitación (día): ${this.tiempoActual.lluvia} mm</p>
      </article>`;
    contenedor.append(htmlActual);

    contenedor.append(`
      <article>
        <h3>Pronóstico extendido (los próximos 7 días)</h3>
      </article>
    `);

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

$(document).ready(() => {
  const lena = new Concejo("Lena", "43.158437,-5.828922");
  lena.obtenerDatos();
});
