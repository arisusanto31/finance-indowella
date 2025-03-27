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