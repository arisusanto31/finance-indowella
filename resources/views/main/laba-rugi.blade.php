<x-app-layout>

    <div class="card">

        <h5 class="text-primary-dark card-header"> 💰 <strong>LABA RUGI </strong> </h5>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <div id="container-laba-rugi" class="text-primary-dark"></div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        var res = @json($data);


        function _tampilkanBaris(kodePerk, string, amount, isStrong, prosen = "") {
            if (isStrong) {
                stringStrong = "<strong>";
                stringPenutupStrong = "</strong>";
            } else {
                stringStrong = "";
                stringPenutupStrong = "";
            }
            datahtml = `
                    <div class="row">
                     <div class="col-md-4 col-xs-12 " style="border-bottom:1px solid #ddd;">
                        <div class ="row"> 
                        ${
                                 kodePerk?
                                         `<div class="col-xs-3 col-md-3">
                                                 ${stringStrong} ${kodePerk} ${stringPenutupStrong}
                                           </div>
                                           <div class="col-xs-9 col-md-9">
                                                ${stringStrong} ${string} ${stringPenutupStrong}
                                           </div>`
                                   :
                                 `
                                           <div class="col-xs-12 col-md-12">
                                                 ${stringStrong} ${string} ${stringPenutupStrong}
                                           </div>`
                            
                        }
                        </div>
                     </div>
                     <div class="col-md-8 col-xs-12" style="border-bottom:1px solid #ddd;">
                        <div class ="row"> 
                           <div class="col-xs-3 col-md-3 textright">
                                 ${stringStrong}  ${amount}  ${stringPenutupStrong}
                           </div>
                           <div class="col-xs-3 col-md-1 textright">
                               
                                ${prosen!==""?"("+prosen+"%)":""}
                            </div>
                        </div>
                     </div>
                     </div>
                    <div class="clearfix"></div>`;

            return datahtml;
        }

        function tampilkan(res) {
            if (res.status == 1) {
                html = "";


                penjualan = collect(res.msg).where('code_group', '<', 600000).where('code_group', '>', 400000).all();
                totalPenjualan = collect(penjualan).sum('saldo_akhir');
                html += _tampilkanBaris("", "PENDAPATAN", "", true);
                penjualan.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);
                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                html += _tampilkanBaris("", "Pendapatan Netto", formatRupiah(totalPenjualan), true,100);

                html += '<div style="margin-top:20px"></div>';
                bebanPokok = collect(res.msg).where('code_group', '<', 700000).where('code_group', '>', 600000).all();
                totalBeban = collect(bebanPokok).sum('saldo_akhir');
                html += _tampilkanBaris("", "BEBAN POKOK", "", true);
                bebanPokok.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);

                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                prosen = getProsen(totalBeban, totalPenjualan);

                html += _tampilkanBaris("", "Total Beban Pokok", formatRupiah(totalBeban), true, prosen);

                html += '<div style="margin-top:20px"></div>';
                labaKotor = totalPenjualan + totalBeban;
                prosen = getProsen(labaKotor, totalPenjualan);
                html += _tampilkanBaris("", "LABA KOTOR", formatRupiah(labaKotor), true, prosen);

                html += '<div style="margin-top:20px"></div>';
                bebanPenjualan = collect(res.msg).where('code_group', '<', 800000).where('code_group', '>', 700000).all();
                totalBebanPenjualan = collect(bebanPenjualan).sum('saldo_akhir');
                html += _tampilkanBaris("", "BEBAN PENJUALAN", "", true);
                bebanPenjualan.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);

                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                html += _tampilkanBaris("", "Total Beban Penjualan", formatRupiah(totalBebanPenjualan), true);

                html += '<div style="margin-top:20px"></div>';
                bebanAdmin = collect(res.msg).where('code_group', '<', 900000).where('code_group', '>', 800000).all();
                totalBebanAdmin = collect(bebanAdmin).sum('saldo_akhir');
                html += _tampilkanBaris("", "BEBAN ADMINISTRASI DAN UMUM", "", true);
                bebanAdmin.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);

                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                prosen = getProsen(totalBebanAdmin, totalPenjualan);

                html += _tampilkanBaris("", "Total Beban Administrasi dan Umum", formatRupiah(totalBebanAdmin), true, prosen);

                html += '<div style="margin-top:20px"></div>';
                labaOperasional = totalPenjualan + totalBeban + totalBebanPenjualan + totalBebanAdmin;
                prosen = getProsen(labaOperasional, totalPenjualan);
                html += _tampilkanBaris("", "LABA OPERASIONAL", formatRupiah(labaOperasional), true, prosen);

                html += '<div style="margin-top:20px"></div>';
                pendapatanLain = collect(res.msg).where('code_group', '<', 902000).where('code_group', '>', 901000).all();
                totalPendapatanLain = collect(pendapatanLain).sum('saldo_akhir');
                bebanLain = collect(res.msg).where('code_group', '<', 905000).where('code_group', '>', 902000).all();
                totalBebanLain = collect(bebanLain).sum('saldo_akhir');
                html += _tampilkanBaris("", "PENDAPATAN DAN BEBAN LAIN", "", true);
                html += _tampilkanBaris("", "PENDAPATAN LAIN", "", true);
                pendapatanLain.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);

                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                prosen = getProsen(totalPendapatanLain, totalPenjualan);

                html += _tampilkanBaris("", "Total Pendapatan lain", formatRupiah(totalPendapatanLain), true, prosen);
                html += _tampilkanBaris("", "BEBAN LAIN", "", true);
                bebanLain.forEach(function eachNeraca(lajur) {
                    isStrong = lajur.level < 1;
                    prosen = getProsen(lajur.saldo_akhir, totalPenjualan);

                    html += _tampilkanBaris(lajur.code_group, lajur.name, formatRupiah(lajur.saldo_akhir), isStrong, prosen);
                });
                prosen = getProsen(totalBebanLain, totalPenjualan);

                html += _tampilkanBaris("", "Total Beban lain", formatRupiah(totalBebanLain), true, prosen);
                prosen = getProsen(res.laba_bulan, totalPenjualan);

                html += _tampilkanBaris("", "TOTAL Pendapatan dan Beban Lain", formatRupiah(res.laba_bulan), true, prosen);

                html += '<div style="margin-top:20px"></div>';
                html += _tampilkanBaris("", "TOTAL LABA BERSIH BERJALAN", formatRupiah(res.laba_bulan), true);

                $('#container-laba-rugi').html(html);
            } else {
                swal('ops', 'something error');
            }
        }
        tampilkan(res);
    </script>
    @endpush
</x-app-layout>