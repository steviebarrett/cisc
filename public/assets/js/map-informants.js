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

    // create place marker
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

    let sidebar_html = '<div class="map-sidebar">';
    sidebar_html += '<h3>' + item.place + '</h3>';

    for (const informant of item.informants) {
        sidebar_html += '<div class="mb-3"><details>';
        sidebar_html += '<summary>' + informant.name_en + ' <em>' + informant.name_gd + '</em></summary>';
        sidebar_html += '<div class="card"><div class="card-body">';

        sidebar_html += 'ID: ' + informant.informant_id;

        if (informant.dates_raw) {
            sidebar_html += '<p>' + informant.dates_raw + '</p>';
        }

        if (informant.recordings && informant.recordings.length > 0) {
            sidebar_html += '<h5>Recordings</h5><ul>';
            for (const recording of informant.recordings) {
                sidebar_html += '<li>' + recording.title + '</li>';
            }
            sidebar_html += '</ul>';
        }

        sidebar_html += '</div></div></details></div>';
    }

    sidebar_html += '</div>';

    marker.bindPopup(popupHtml)
      .on('mouseover', function () { this.openPopup(); })
      .on('click', function () {
          document.getElementById('map-results').innerHTML = sidebar_html;});
}
