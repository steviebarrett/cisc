"use strict";

import { getJsonScript } from './utils.js';


const data = getJsonScript('informants-map-data') || [];

console.log(data);

var map = L.map('map').setView([46.2000, -60.7500], 9);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);


for (const item of data) {

    console.log('hit');

    const inf_count = item.inf_count;
    // create Canadian place marker
    const markerStyle = {radius: 20 * (.2 * inf_count),
      color: '#009',
      weight: 1,
      fillColor: '#009',
      fillOpacity: 0.3 * inf_count};
    const marker = L.circleMarker([item.lat, item.lng], markerStyle).addTo(map);

    const url = window.BASE_PATH + encodeURI('recordings?place='+item.place);
    const popupHtml = `
        <div>
            <div><strong>${item.place || 'Untitled'}</strong></div>
            <div>Informants: ${item.inf_count}</div>
            
        </div>
    `;

    marker.bindPopup(popupHtml)
      .on('mouseover', function () { this.openPopup(); })
      .on('click', function () {
        let html = '<b>' + item.place + '</b>';
        document.getElementById('map-results').innerHTML = html;});




  // create Scottish place marker
 /* if (item.sc_lat) {
    const marker_sc = L.marker([item.sc_lat, item.sc_lng]).addTo(map);

    const url = window.BASE_PATH + encodeURI('recordings?place=' + item.place_scotland);
    const popupHtml = `
        <div>
            <div><strong>${item.place_scotland || 'Untitled'}</strong></div>
            <div><a href="${url}" title="${item.place_scotland}">${item.inf_count} informants</div>
            
        </div>
    `;

    marker_sc.bindPopup(popupHtml);
  }
*/

 //   bounds.extend([item.lat, item.lng]);
}
