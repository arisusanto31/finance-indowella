console.log("own-helper.js loaded");



function formatDB(angka, language = "id") {
    negatif = 0;
    if (angka == null)
        angka = "0";
    else {
        angka = angka.toString();
        negatif = check_char(angka, '-');
        if (angka == "") {
            angka = "0";
        }
    }
    if (language == "eng")
        split = angka.split('.');
    else {
        split = angka.split(',');
    }
    split[0] = (split[0].replace(/[^0-9]/g, ''));
    angka = split[0];
    if (split[1] != undefined) {
        split[1] = (split[1].replace(/[^0-9]/g, ''));
        strkoma = "0." + split[1];
        koma = parseFloat(strkoma);

        koma *= 100;
        koma = Math.round(koma);
        angka += '.' + koma;
    }
    if (angka == null || angka == "") angka = 0;
    angka = parseFloat(angka);

    if (negatif)
        angka *= -1;
    return angka;
}


// function formatRupiah(angkaString, prefix = "", language = "id") {
//     try {
//         var number_string = "";
//         angkaString = angkaString.toString();
//         negatif = check_char(angkaString, '-');
//         split = angkaString.split('.'),
//             split[0] = split[0].replace(/[^0-9]/g, '').toString();
//         if (split[1] != undefined)
//             split[1] = split[1].replace(/[^0-9]/g, '').toString();
//         sisa = split[0].length % 3,
//             rupiah = split[0].substr(0, sisa),
//             ribuan = split[0].substr(sisa).match(/\d{3}/gi);
//         // tambahkan titik jika yang di input sudah menjadi angka ribuan

//         if (ribuan) {
//             if (language == "eng") {
//                 separator = sisa ? ',' : '';
//                 rupiah += separator + ribuan.join(',');
//             } else {
//                 separator = sisa ? '.' : '';
//                 rupiah += separator + ribuan.join('.');

//             }
//         }
//         if (language == "eng")
//             rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
//         else
//             rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

//         if (negatif) {
//             return prefix == undefined ? '-' + rupiah : (rupiah ? '-' + rupiah : '');
//         }
//         return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
//     } catch (err) {
//         console.log(err);
//         return angkaString;
//     }


// }

function detectFormat(input) {
    // 1. Format database: angka murni dengan titik desimal
    console.log(input);
    const dbPattern = /^-?\d+(\.\d+)?$/;

    // 2. Format rupiah: bisa mengandung Rp, titik ribuan, koma desimal
    const rupiahPattern = /^\s?[\d\.]+(,\d{1,2})?$/;

    if (dbPattern.test(input)) {
        return 'database';
    } else if (rupiahPattern.test(input)) {
        return 'rupiah';
    } else {
        return 'unknown';
    }
}
function formatRupiah(number, language = "id") {


    //yang bingung disini adalah inputnya ,inputnya itu formatDB atau stringIndo atau stringEng
    //harusnya kalau kita pake language id , barti asumsinya cuma angka dengan format DB dan string Indo
    //buat mastikan angkanya itu beneran angka dulu
    let isTyping = false;
    if (number == null || number == undefined || number == "") return 0;
    awal = number;
    console.log(detectFormat(number));
    if (detectFormat(number) != 'database') {
        //kalo inputnya ternyata format rupiah
        if (language == "eng") {
            //asumsi format rupiah eng ya
            number = number.toString();
            if (number.match(/\.$/)) isTyping = true;

            number = (number.replace(/[^0-9.]/g, ''));
        } else {
            number = number.toString();
            //asumsi rupiah format indo ya
            if (number.match(/\,$/)) isTyping = true;
            number = (number.replace(/[^0-9,]/g, '').replace(',', '.'));

        }
    }
    number = parseFloat(number).toString();

    if (isTyping) return awal;
    console.log("number=" + number);
    decPoint = language == "eng" ? '.' : ',';
    thousandsSep = language == "eng" ? ',' : '.';
    parts = number.toString().split('.');
    console.log(parts);
    if (parts.length > 1) {
        if (parts[1].length > 2) {
            numberKoma = parseFloat("0." + parts[1]);
            parts[1] = numberKoma.toFixed(2).split('.')[1];
        }
    }
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

    return parts.join(decPoint);
}

function formatRupiahSimple(number, language = "id") {
    //yang bingung disini adalah inputnya ,inputnya itu formatDB atau stringIndo atau stringEng
    //harusnya kalau kita pake language id , barti asumsinya cuma angka dengan format DB dan string Indo
    //buat mastikan angkanya itu beneran angka dulu
    let isTyping = false;
    awal = number;


    if (detectFormat(number) != 'database') {
        //kalo inputnya ternyata format rupiah
        if (language == "eng") {
            //asumsi format rupiah eng ya
            if (number.match(/\.$/)) isTyping = true;

            number = (number.replace(/[^0-9.]/g, ''));
        } else {
            //asumsi rupiah format indo ya
            if (number.match(/\,$/)) isTyping = true;
            number = (number.replace(/[^0-9,]/g, '').replace(',', '.'));

        }
    }
    if (isTyping) return awal;

    //dari sini mestinya semuanya sudah formatdb
    number = parseFloat(number);
    tail = "";

    if (number > 999999999) {
        tail = "M";
        number = number / 1000000000;
    }
    else if (number > 999999) {
        tail = "jt";
        number = number / 1000000;
    } else if (number > 999) {
        tail = "rb";
        number = number / 1000;
    }
    decPoint = language == "eng" ? '.' : ',';
    thousandsSep = language == "eng" ? ',' : '.';
    if (language == 'id')
        parts = number.toString().split('.');
    else
        parts = number.toString().split(',');
    console.log(parts);
    if (parts.length > 1) {
        if (parts[1].length > 2) {
            numberKoma = parseFloat("0." + parts[1]);
            parts[1] = numberKoma.toFixed(2).split('.')[1];
        }
    }
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

    return parts.join(",") + ' ' + tail;
}

function check_char(str, char) {
    ctr = 0;
    for (let i = 0; i < str.length; i++) {
        if (str.charAt(i) == char) {
            ctr++;
        }
    }
    return (ctr > 0) ? 1 : 0;
}


function getNumID(data) {
    var numid = data.replace(/^\D+/g, '');
    return numid;
}

function initItemSelectManual(el, url, placeholder = "", parent = null) {

    if (placeholder == "")
        placeholder = "Cari berdasarkan nama ..."
    if (parent == null) {
        parent = 'body';
    }
    $(el).select2({
        placeholder: placeholder,
        width: '100%', // agar responsive di Sneat
        theme: 'bootstrap-5', // tambahkan ini agar tampilannya menyatu
        dropdownParent: $(parent), // jika di modal/tab/card
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

function normalizeDate(input) {
    // Ganti separator jadi '-'
    const clean = input.replace(/[\/\.]/g, '-'); // handle / atau .
    const parts = clean.split('-');

    if (parts.length !== 3) return null;

    let [a, b, c] = parts;

    // deteksi posisi tahun (asumsi tahun selalu 4 digit)
    if (a.length === 4) {
        // format: yyyy-mm-dd
        return `${a}-${b.padStart(2, '0')}-${c.padStart(2, '0')}`;
    } else if (c.length === 4) {
        // format: dd-mm-yyyy atau mm-dd-yyyy
        // heuristik: jika a > 12 → anggap dd-mm-yyyy
        if (parseInt(b) > 12) {
            return `${c}-${a.padStart(2, '0')}-${b.padStart(2, '0')}`; // mm-dd-yyyy
        } else {
            return `${c}-${b.padStart(2, '0')}-${a.padStart(2, '0')}`; // dd-mm-yyyy
        }
    }

    return null; // format tidak diketahui
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

function formatNormalDate(date) {
    const pad = (n) => n.toString().padStart(2, '0');

    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // bulan dimulai dari 0
    const day = pad(date.getDate());

    return `${year}-${month}-${day}`;
}



function getProsen($data, $total) {
    if ($total == 0) {
        return 0;
    } else {
        return Math.round(($data * 100 / $total) * 100) / 100;
    }
}

function initCurrencyInput(elem) {
    $(elem).on('input', function () {
        let value = $(this).val().replace(/[^\d,]/g, '');
        console.log('value:' + value);
        // Cek apakah input adalah format database atau format rupiah
        // Format angka dengan locale 'id-ID' → hasilnya: 50.000
        let formatted = formatRupiah(value);
        $(this).val(formatted);
    });
}

function array_key_exists(key, obj) {
    return Object.prototype.hasOwnProperty.call(obj, key);
}


function swalInfo(title, text, icon = "info") {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        didOpen: () => {
            $('.swal2-container').css('z-index', 2000);
        }
    });
}
function swalConfirmAndSubmit({ url, data, onSuccess = null, successText = "Berhasil!", confirmText = "Yes", cancelText = "No" }) {
    Swal.fire({
        title: "Apakah kamu yakin?",
        text: "Data akan diproses!",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        didOpen: () => {
            $('.swal2-container').css('z-index', 2000);
        },
        preConfirm: () => {
            return $.ajax({
                url: url,
                method: 'post',
                data: data
            }).then(res => {
                console.log(res);
                if (res.status == 1) {
                    return Swal.fire({
                        title: "Sukses!",
                        text: successText,
                        icon: 'success',
                        allowOutsideClick: false
                    }).then(() => {
                        if (typeof onSuccess === "function") onSuccess(res);
                    });
                } else {

                    return Promise.reject(res.msg || "hehe error ini lur");
                }
            }).catch(err => {
                console.log("something error");
                if (typeof err == "object") {
                    err = "error di server";
                }
                Swal.showValidationMessage(err || "Terjadi kesalahan!");
            });
        }
    });
}

function swalDelete({ url, onSuccess = null, successText = "Berhasil!", confirmText = "Dihapus", cancelText = "Cancel" }) {
    Swal.fire({
        title: "Apakah kamu yakin?",
        text: "Data akan dihapus!",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        didOpen: () => {
            $('.swal2-container').css('z-index', 2000);
        },
        preConfirm: () => {
            return $.ajax({
                url: url,
                type: "DELETE",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(res => {
                console.log(res);
                if (res.status == 1) {
                    return Swal.fire({
                        title: "Sukses!",
                        text: successText,
                        icon: 'success',
                        allowOutsideClick: false
                    }).then(() => {
                        if (typeof onSuccess === "function") onSuccess(res);
                    });
                } else {
                    return Promise.reject(res.msg || "Gagal menyimpan data");
                }
            }).catch(err => {
                Swal.showValidationMessage(err || "Terjadi kesalahan!");
            });
        }
    });
}