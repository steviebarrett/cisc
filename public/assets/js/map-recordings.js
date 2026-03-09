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

   console.log(item.cn_lat);
    if (typeof item.cn_lat !== 'number' || typeof item.cn_lng !== 'number') {
        console.log('NaN');
        continue;
    }

    const marker = L.marker([item.cn_lat, item.cn_lng]).addTo(map);

    const url = encodeURI('/recordings?place='+item.place);
    const popupHtml = `
        <div>
            <div><strong>${item.place || 'Untitled'}</strong></div>
            <div><a href="${url}" title="${item.place}">${item.rec_count} recordings</div>
            
        </div>
    `;

    /*
    const popupHtml = `
        <div>
            <div><strong>${escapeHtml(item.title || item.id || 'Untitled')}</strong></div>
            ${item.informant_name ? `<div>${escapeHtml(item.informant_name)}</div>` : ''}
            ${item.url ? `<div><a href="${encodeURI(item.url)}">View record</a></div>` : ''}
        </div>
    `;
    */


    marker.bindPopup(popupHtml);
 //   bounds.extend([item.lat, item.lng]);
}
