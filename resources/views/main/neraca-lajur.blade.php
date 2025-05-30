<x-app-layout>
    <div class="card">

        <h5 class="text-primary-dark card-header">⚖️ <strong>NERACA LAJUR </strong>

            <div class="d-flex justify-content pe-4 mt-2 mb-3">
                <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                    << </button>
                        <span class="badge bg-primary d-flex justify-content-center align-items-center">
                            {{ getListMonth()[$month] }} {{ $year }}</span>
                        <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()">
                            >></button>

            </div>
        </h5>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <div id="container-neraca" class="text-primary-dark"></div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            res = @json($data);

            function loadTampilan(data) {
                html = "";
                stringStrong = "<strong>";
                stringPenutupStrong = "</strong>";
                html += '<div class="row">';
                html += ' <div class="col-md-4 col-xs-12 " style="border-bottom:1px solid #ddd;">';
                html += '    <div class ="row"> ';
                html += '       <div class="col-xs-3 col-md-3">';
                html += '            <p>' + stringStrong + "KODE PERK" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '       <div class="col-xs-9  col-md-9">';
                html += '            <p>' + stringStrong + "NAMA PERKIRAAN" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '    </div>';
                html += ' </div>';
                html += ' <div class="col-md-8 col-xs-12" style="border-bottom:1px solid #ddd;">';
                html += '    <div class ="row"> ';
                html += '       <div class="col-xs-3  col-md-3 textright">';
                html += '            <p>' + stringStrong + "SALDO AWAL" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '       <div class="col-xs-3  col-md-3 textright">';
                html += '            <p>' + stringStrong + "MUTASI DEBIT" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '       <div class="col-xs-3  col-md-3 textright" >';
                html += '            <p>' + stringStrong + "MUTASI KREDIT" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '       <div class="col-xs-3  col-md-3 textright">';
                html += '            <p>' + stringStrong + "SALDO AKHIR" + stringPenutupStrong + '</p>';
                html += '       </div>';
                html += '    </div>';
                html += ' </div>';
                html += ' <div class="clearfix"></div>';
                res.msg.forEach(function eachNeraca(lajur) {
                    // mutasiDebet = lajur.mutation ? lajur.mutation['total_debet'] > 0 ? lajur.mutation['total_debet'] : 0 : 0;
                    // mutasiKredit = lajur.mutation ? lajur.mutation['total_kredit'] > 0 ? lajur.mutation['total_kredit'] : 0 : 0;
                    mutasiDebet = '<span id="mutasi-debet' + lajur.id +
                        '"> menghitung <i class="fa fa-spinner fa-spin"></i> </span>';
                    mutasiKredit = '<span id="mutasi-kredit' + lajur.id +
                        '"> menghitung <i class="fa fa-spinner fa-spin"></i> </span>';
                    if (lajur.level < 2) {
                        stringStrong = "<strong>";
                        stringPenutupStrong = "</strong>";
                    } else {
                        stringStrong = "";
                        stringPenutupStrong = "";
                    }
                    html += '<div class="col-md-4 col-xs-12 " style="border-bottom:1px solid #ddd;">';
                    html += '   <div class ="row"> ';
                    html += '      <div class="col-xs-3  col-md-3">';
                    html += '           <p>' + stringStrong + lajur.code_group + stringPenutupStrong + '</p>';
                    html += '      </div>';
                    html += '      <div class="col-xs-9  col-md-9">';
                    html += '           <p>' + stringStrong + lajur.name + stringPenutupStrong + '</p>';
                    html += '      </div>';
                    html += '   </div>';
                    html += '</div>';
                    html += '<div class="col-md-8 col-xs-12" style="border-bottom:1px solid #ddd;">';
                    html += '   <div class ="row"> ';
                    html += '      <div class="col-xs-3  col-md-3 textright">';
                    html += '           <p>' + stringStrong + formatRupiah(lajur.saldo_awal) + stringPenutupStrong +
                        '</p>';
                    html += '      </div>';
                    html += '      <div class="col-xs-3  col-md-3 textright">';
                    html += '           <p >' + stringStrong + (mutasiDebet) + stringPenutupStrong + '</p>';
                    html += '      </div>';
                    html += '      <div class="col-xs-3  col-md-3 textright" >';
                    html += '           <p>' + stringStrong + (mutasiKredit) + stringPenutupStrong + '</p>';
                    html += '      </div>';
                    html += '      <div class="col-xs-3  col-md-3 textright">';
                    html += '           <p>' + stringStrong + formatRupiah(lajur.saldo_akhir) + stringPenutupStrong +
                        '</p>';
                    html += '      </div>';
                    html += '   </div>';
                    html += '</div>';
                    html += '<div class="clearfix"></div>';
                });
                html += '</div>';
                $('#container-neraca').html(html);
                loadMutasiNeracaLajur();
            }

            loadTampilan(res);


            function loadMutasiNeracaLajur() {
                $.ajax({
                    url: "{{ url('admin/get-mutasi-neraca-lajur') }}?month={{ getInput('month') }}&year={{ getInput('year') }}",
                    method: 'get',
                    success: function(res) {
                        if (res.status == 1) {
                            Object.keys(res.msg).forEach(function eachKey(key) {
                                thedata = res.msg[key];
                                $('#mutasi-kredit' + key).html(formatRupiah(thedata.total_kredit));
                                $('#mutasi-debet' + key).html(formatRupiah(thedata.total_debet));
                            });
                        } else {
                            swal('opps', 'load mutasi lajur error :' + res.msg);
                        }
                    },
                    error: function(res) {
                        swal('opss', 'load mutasi lajur error');
                    }
                });
            }

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/neraca-lajur') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/neraca-lajur') }}?month=' + month + '&year=' + year;
            }
        </script>
    @endpush
</x-app-layout>
