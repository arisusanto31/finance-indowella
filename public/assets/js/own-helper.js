console.log("own-helper.js loaded");
function formatRupiahSimple(angkaString) {
    angka = parseInt(angkaString);
    if (Math.abs(angka) >= 1000000) {
        angka = Math.floor(angka / 100000) / 10;
        return angka + "M";
    } else if (Math.abs(angka) >= 1000) {
        angka = Math.floor(angka / 100) / 10;
        return angka + "K";
    } else {
        return angka;
    }
}

function formatRupiah(angkaString, prefix = "", language = "eng") {
    try {
        var number_string = "";
        angkaString = angkaString.toString();
        negatif = check_char(angkaString, '-');
        split = angkaString.split('.'),
            split[0] = split[0].replace(/[^0-9]/g, '').toString();
        if (split[1] != undefined)
            split[1] = split[1].replace(/[^0-9]/g, '').toString();
        sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        // tambahkan titik jika yang di input sudah menjadi angka ribuan

        if (ribuan) {
            if (language == "eng") {
                separator = sisa ? ',' : '';
                rupiah += separator + ribuan.join(',');
            } else {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');

            }
        }
        if (language == "eng")
            rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
        else
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

        if (negatif) {
            return prefix == undefined ? '-' + rupiah : (rupiah ? '-' + rupiah : '');
        }
        return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
    } catch (err) {
        return angkaString;
    }
}


function getNumID(data) {
    var numid = data.replace(/^\D+/g, '');
    return numid;
}

function initItemSelectManual(el, url, placeholder = "") {

    if (placeholder == "")
        placeholder = "Cari berdasarkan nama ..."
    $(el).select2({
        placeholder: placeholder,
        width: '100%', // agar responsive di Sneat
        theme: 'bootstrap-5', // tambahkan ini agar tampilannya menyatu
        // dropdownParent: $('#your-modal-or-card'), // jika di modal/tab/card
        allowClear: true,
        ajax: {
            url: url,
            dataType: 'json',
            cache: true,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            success: function (result) {
                console.log(result.responseText);
            },
            error: function (result) {
                console.log(result.responseText);
            }
        }
    });

}

function formatNormalDateTime(date) {
    const pad = (n) => n.toString().padStart(2, '0');

    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // bulan dimulai dari 0
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    const seconds = pad(date.getSeconds());

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}