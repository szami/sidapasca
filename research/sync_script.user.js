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
    // Remote: https://sidapasca-ulm.inovasidigital.link
    // Local:  http://pmb-pps-ulm.test
    const SIDA_PASCA_BASE = 'https://sidapasca-ulm.inovasidigital.link';
    const SIDA_PASCA_URL = `${SIDA_PASCA_BASE}/admin/verification/physical/api-sync-data`;

    // Add Sync Buttons to the UI
    function injectUI() {
        const target = document.querySelector('.x_content .row.clearfix');
        if (!target) return;

        // Button 1: Sync Berkas
        const btnContainer1 = document.createElement('div');
        btnContainer1.className = 'col-md-2';
        btnContainer1.style.marginBottom = '5px';

        const btn1 = document.createElement('button');
        btn1.className = 'btn btn-warning';
        btn1.style.width = '100%';
        btn1.innerHTML = '<i class="fa fa-refresh"></i> Sync Berkas';
        btn1.onclick = () => startSync('berkas');

        btnContainer1.appendChild(btn1);
        target.appendChild(btnContainer1);

        // Button 2: Sync Kelulusan
        const btnContainer2 = document.createElement('div');
        btnContainer2.className = 'col-md-2';
        btnContainer2.style.marginBottom = '5px';

        const btn2 = document.createElement('button');
        btn2.className = 'btn btn-success';
        btn2.style.width = '100%';
        btn2.innerHTML = '<i class="fa fa-graduation-cap"></i> Sync Kelulusan';
        btn2.onclick = () => startSync('lulus');

        btnContainer2.appendChild(btn2);
        target.appendChild(btnContainer2);
    }

    async function startSync(type) {
        const msg = type === 'berkas' ? 'Mulai sinkronisasi BERKAS FISIK?' : 'Mulai sinkronisasi HASIL KELULUSAN?';
        if (!confirm(msg)) return;

        const btn = event.target.tagName === 'I' ? event.target.parentElement : event.target;
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
                        await processData(result.data, btn, type);
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
                alert('Gagal terhubung ke SIDA Pasca. URL: ' + SIDA_PASCA_URL);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    const delay = ms => new Promise(res => setTimeout(res, ms));

    // Scrape current table data for comparison
    function getTableData() {
        const data = {};
        const rows = document.querySelectorAll('#tabel tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6) {
                const noUjian = cells[1].innerText.trim();
                const berkasStatus = cells[4].innerText.trim(); // "Ada" or "Belum"
                const hasilStatus = cells[5].innerText.trim();  // "Lulus", "Gagal", or "-"

                data[noUjian] = {
                    berkas: berkasStatus,
                    hasil: hasilStatus
                };
            }
        });
        return data;
    }

    async function processData(data, btn, type) {
        const total = data.length;
        let successCount = 0;
        let skippedCount = 0;

        // Get current table state
        const currentTable = getTableData();

        for (let i = 0; i < total; i++) {
            const item = data[i];
            btn.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${i + 1}/${total}`;

            try {
                const existing = currentTable[item.nomor_peserta];

                if (type === 'berkas') {
                    // 1. Sync Physical Verification
                    // Logic: Only 'lengkap' in SIDA Pasca is considered '1' (Ada) in Admisi
                    const isLengkap = item.status_verifikasi_fisik && item.status_verifikasi_fisik.toLowerCase() === 'lengkap';
                    const statusBerkas = isLengkap ? '1' : '0';
                    const prodi = statusBerkas === '1' ? item.kode_prodi : 'null';

                    // Optimization Check
                    const currentStatus = existing ? existing.berkas : null;
                    const newStatusText = isLengkap ? 'Ada' : 'Belum';

                    if (currentStatus === newStatusText) {
                        console.log(`[SKIP] ${item.nomor_peserta} Berkas already ${currentStatus}`);
                        skippedCount++;
                        continue;
                    }

                    await new Promise((resolve) => {
                        $.ajax({
                            url: "https://admisipasca.ulm.ac.id/administrator/kartu/isberkas/" + statusBerkas + '/' + item.nomor_peserta + '/' + prodi,
                            type: 'POST',
                            success: function (msg) { successCount++; resolve(); },
                            error: function () { resolve(); }
                        });
                    });
                } else if (type === 'lulus') {
                    // 2. Sync Graduation Status
                    if (item.keputusan_akhir) {
                        const statusLulus = item.keputusan_akhir === 'lulus' ? '1' : '0';
                        const prodiLulus = statusLulus === '1' ? item.kode_prodi : 'null';

                        // Optimization Check
                        const currentStatus = existing ? existing.hasil : null;
                        const newStatusText = item.keputusan_akhir === 'lulus' ? 'Lulus' : 'Gagal';

                        if (currentStatus === newStatusText) {
                            console.log(`[SKIP] ${item.nomor_peserta} Hasil already ${currentStatus}`);
                            skippedCount++;
                            continue;
                        }

                        await new Promise((resolve) => {
                            $.ajax({
                                url: "https://admisipasca.ulm.ac.id/administrator/kartu/islulus/" + statusLulus + '/' + item.nomor_peserta + '/' + prodiLulus,
                                type: 'POST',
                                success: function (msg) { successCount++; resolve(); },
                                error: function () { resolve(); }
                            });
                        });
                    } else {
                        // Skip if no graduation status in SIDA
                        skippedCount++;
                        continue;
                    }
                }
            } catch (e) {
                console.error('Sync failed for ' + item.nomor_peserta, e);
            }

            await delay(100);
        }

        alert(`Sinkronisasi ${type.toUpperCase()} selesai!\n` +
            `Total Data: ${total}\n` +
            `Diperbarui: ${successCount}\n` +
            `Diskip (Sama): ${skippedCount}`);
        location.reload();
    }

    // Initialize
    setTimeout(injectUI, 1000);
})();
