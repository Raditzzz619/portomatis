(() => {
    const initialized = new WeakSet();
    const distanceKm = (latitude, longitude, point) => {
        const radians = value => value * Math.PI / 180;
        const latitudeDelta = radians(point.lat - latitude);
        const longitudeDelta = radians(point.lng - longitude);
        const a = Math.sin(latitudeDelta / 2) ** 2 + Math.cos(radians(latitude)) * Math.cos(radians(point.lat)) * Math.sin(longitudeDelta / 2) ** 2;
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(Math.max(0, 1 - a)));
    };

    function init(root = document) {
        root.querySelectorAll('[data-domicile]').forEach(picker => {
            if (initialized.has(picker)) return;
            initialized.add(picker);
            let provinces;
            try {
                provinces = JSON.parse(picker.querySelector('[data-domicile-data]').textContent);
                if (!Array.isArray(provinces) || !provinces.length) return;
            } catch {
                // The server-rendered list remains usable if enhancement fails.
                return;
            }

            const provinceSelect = picker.querySelector('[data-domicile-province]');
            const regencySelect = picker.querySelector('[data-domicile-regency]');
            const legacy = regencySelect.querySelector('[data-legacy-domicile]')?.cloneNode(true);
            const initialProvince = regencySelect.selectedOptions[0]?.closest('optgroup')?.dataset.province;
            const status = picker.querySelector('[data-domicile-status]');
            const suggestion = picker.querySelector('[data-domicile-suggestion]');
            const candidates = picker.querySelector('[data-domicile-candidates]');
            const detect = picker.querySelector('[data-domicile-detect]');
            const accept = picker.querySelector('[data-domicile-accept]');
            const label = (province, regency) => `${regency.name}, ${province.name}, Indonesia`;
            const notify = () => {
                regencySelect.dispatchEvent(new Event('input', { bubbles: true }));
                regencySelect.dispatchEvent(new Event('change', { bubbles: true }));
            };
            const dismissSuggestion = () => {
                suggestion.hidden = true;
                candidates.replaceChildren();
                accept.disabled = true;
            };
            const fillRegencies = (provinceId, selectedValue = '', keepLegacy = false) => {
                const province = provinces.find(item => item.id === provinceId);
                regencySelect.replaceChildren(new Option(province ? 'Tidak ditampilkan' : 'Pilih provinsi dahulu', ''));
                if (keepLegacy && legacy) regencySelect.appendChild(legacy.cloneNode(true));
                province?.regencies.forEach(regency => regencySelect.add(new Option(regency.name, label(province, regency))));
                regencySelect.value = selectedValue;
                // Keep the empty value enabled so it is submitted and can clear an old domicile.
                regencySelect.disabled = false;
            };

            picker.querySelector('[data-domicile-province-field]').hidden = false;
            picker.querySelector('[data-domicile-gps]').hidden = false;
            provinceSelect.value = initialProvince || '';
            fillRegencies(initialProvince || '', regencySelect.value, true);

            provinceSelect.addEventListener('change', () => {
                fillRegencies(provinceSelect.value);
                dismissSuggestion();
                status.textContent = '';
                picker.querySelector('[data-domicile-legacy]')?.remove();
                notify();
            });
            regencySelect.addEventListener('change', () => {
                dismissSuggestion();
                if (!legacy || regencySelect.value !== legacy.value) picker.querySelector('[data-domicile-legacy]')?.remove();
            });
            candidates.addEventListener('change', () => { accept.disabled = !candidates.value; });
            picker.querySelector('[data-domicile-dismiss]').addEventListener('click', () => {
                dismissSuggestion();
                status.textContent = 'Perkiraan diabaikan. Domisili Anda tidak diubah.';
                detect.focus();
            });
            accept.addEventListener('click', () => {
                const provinceId = candidates.selectedOptions[0]?.dataset.province;
                const province = provinces.find(item => item.id === provinceId);
                const regency = province?.regencies.find(item => item.id === candidates.value);
                if (!province || !regency) return;
                provinceSelect.value = province.id;
                fillRegencies(province.id, label(province, regency));
                dismissSuggestion();
                picker.querySelector('[data-domicile-legacy]')?.remove();
                notify();
                status.textContent = 'Wilayah pilihan digunakan sebagai domisili. Anda dapat mengubahnya melalui daftar provinsi dan kabupaten/kota.';
                regencySelect.focus();
            });
            detect.addEventListener('click', () => {
                dismissSuggestion();
                if (!window.isSecureContext || !navigator.geolocation) {
                    status.textContent = 'GPS tidak tersedia. Gunakan HTTPS atau localhost untuk GPS, atau pilih provinsi dan kabupaten/kota dari daftar.';
                    return;
                }
                detect.disabled = true;
                status.textContent = 'Meminta izin GPS dan mencari perkiraan wilayah terdekat…';
                navigator.geolocation.getCurrentPosition(position => {
                    detect.disabled = false;
                    if (!picker.isConnected) return;
                    // Coordinates are used only in this callback, never sent to the server or stored.
                    const { latitude, longitude, accuracy } = position.coords;
                    if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || !Number.isFinite(accuracy) || accuracy > 25000) {
                        status.textContent = 'Posisi GPS belum cukup akurat. Silakan pilih provinsi dan kabupaten/kota dari daftar.';
                        return;
                    }
                    const nearby = provinces.flatMap(province => province.regencies
                        .filter(regency => Number.isFinite(regency.lat) && Number.isFinite(regency.lng))
                        .map(regency => ({ province, regency, distance: distanceKm(latitude, longitude, regency) })))
                        .sort((a, b) => a.distance - b.distance).slice(0, 5);
                    if (!nearby.length || nearby[0].distance > 150 || latitude < -11.5 || latitude > 6.5 || longitude < 94.5 || longitude > 141.5) {
                        status.textContent = 'Wilayah Indonesia di sekitar posisi ini belum dapat diperkirakan. Silakan pilih domisili dari daftar.';
                        return;
                    }
                    candidates.add(new Option('Pilih perkiraan yang sesuai domisili', ''));
                    nearby.forEach(({ province, regency }) => {
                        const option = new Option(`${regency.name}, ${province.name}`, regency.id);
                        option.dataset.province = province.id;
                        candidates.add(option);
                    });
                    suggestion.hidden = false;
                    status.textContent = 'Periksa perkiraan di bawah. Domisili baru berubah setelah Anda memilih wilayah dan menekan Gunakan wilayah pilihan.';
                    candidates.focus();
                }, error => {
                    detect.disabled = false;
                    if (!picker.isConnected) return;
                    status.textContent = error.code === 1
                        ? 'Izin GPS tidak diberikan. Anda tetap dapat memilih provinsi dan kabupaten/kota dari daftar.'
                        : error.code === 3
                            ? 'Pencarian GPS terlalu lama. Coba lagi atau pilih provinsi dan kabupaten/kota dari daftar.'
                            : 'Posisi GPS belum tersedia. Coba lagi atau pilih provinsi dan kabupaten/kota dari daftar.';
                }, { enableHighAccuracy: false, timeout: 12000, maximumAge: 60000 });
            });
        });
    }

    window.PortomatisDomicile = { init };
    document.addEventListener('DOMContentLoaded', () => init());
    document.addEventListener('portomatis:step-ready', () => init());
})();
