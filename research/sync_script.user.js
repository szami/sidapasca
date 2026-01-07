// ==UserScript==
// @name         SIDA Pasca Sync Tool
// @namespace    http://tampermonkey.net/
// @version      1.0
// @description  Sync Physical Verification from SIDA Pasca to Admisi ULM
// @author       Antigravity
// @match        https://admisipasca.ulm.ac.id/administrator/kartu*
// @grant        GM_xmlhttpRequest
// @connect      sidapasca-ulm.inovasidigital.link
// ==/UserScript==

(function () {
    'use strict';

    // CONFIGURATION
    // Update this URL if your local SIDA Pasca is running on a different address
    const SIDA_PASCA_URL = 'https://sidapasca-ulm.inovasidigital.link/admin/verification/physical/api-sync-data';

    // Add Sync Button to the UI
    function injectUI() {
        const target = document.querySelector('.x_content .row.clearfix');
        if (!target) return;

        const btnContainer = document.createElement('div');
        btnContainer.className = 'col-md-2';
        btnContainer.style.marginBottom = '5px';

        const btn = document.createElement('button');
        btn.className = 'btn btn-warning';
        btn.style.width = '100%';
        btn.innerHTML = '<i class="fa fa-refresh"></i> Sync dari SIDA';
        btn.onclick = startSync;

        btnContainer.appendChild(btn);
        target.appendChild(btnContainer);
    }

    async function startSync() {
        if (!confirm('Mulai sinkronisasi data dari SIDA Pasca Lokal?')) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mentransfer...';

        GM_xmlhttpRequest({
            method: "GET",
            url: SIDA_PASCA_URL,
            onload: async function (response) {
                try {
                    const result = JSON.parse(response.responseText);
                    if (result.success && result.data) {
                        await processData(result.data, btn);
                    } else {
                        alert('Gagal mengambil data: ' + (result.message || 'Unknown error'));
                    }
                } catch (e) {
                    alert('Error parsing data: ' + e.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            },
            onerror: function (err) {
                alert('Gagal terhubung ke SIDA Pasca. Pastikan Laragon aktif dan URL benar.\nURL: ' + SIDA_PASCA_URL);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    const delay = ms => new Promise(res => setTimeout(res, ms));

    async function processData(data, btn) {
        const total = data.length;
        let successCount = 0;

        for (let i = 0; i < total; i++) {
            const item = data[i];
            const status = item.status_verifikasi_fisik === 'lengkap' ? '1' : '0';
            const prodi = status === '1' ? item.kode_prodi : 'null';

            btn.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${i + 1}/${total}`;

            try {
                // Call the native function on the page
                // isberkas(status, noujian, no, kode_prodi)
                // Note: 'no' is just for table reload, we can pass 0
                await new Promise((resolve) => {
                    $.ajax({
                        url: "https://admisipasca.ulm.ac.id/administrator/kartu/isberkas/" + status + '/' + item.nomor_peserta + '/' + prodi,
                        type: 'POST',
                        success: function (msg) {
                            successCount++;
                            resolve();
                        },
                        error: function () {
                            resolve();
                        }
                    });
                });
            } catch (e) {
                console.error('Sync failed for ' + item.nomor_peserta, e);
            }

            // Small delay to prevent hammering
            await delay(100);
        }

        alert('Sinkronisasi selesai!\nBerhasil: ' + successCount + ' dari ' + total + ' data.');
        location.reload(); // Reload to see changes
    }

    // Initialize
    setTimeout(injectUI, 1000);
})();
