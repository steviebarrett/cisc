"use strict";

import { getJsonScript } from './utils.js';


const data = getJsonScript('recordings-map-data') || [];

console.log(data);

var map = L.map('map').setView([46.2000, -60.7500], 9);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);


for (const item of data) {

    if (typeof item.cn_lat !== 'number' || typeof item.cn_lng !== 'number') {
        continue;
    }

    // create Canadian place marker
    const marker = L.marker([item.cn_lat, item.cn_lng]).addTo(map);

    const url = window.BASE_PATH + encodeURI('recordings?place='+item.place);
    const popupHtml = `
        <div>
            <div><strong>${item.place || 'Untitled'}</strong></div>
            <div><a href="${url}" title="${item.place}">${item.inf_count} informants</div>
            
        </div>
    `;

    marker.bindPopup(popupHtml);

  // create Scottish place marker
  if (item.sc_lat) {
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


 //   bounds.extend([item.lat, item.lng]);
}
