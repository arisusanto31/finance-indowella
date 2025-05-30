<x-app-layout>

    <div class="card">
        <h5 class="text-primary-dark card-header">⚖️ <strong>NERACA </strong>
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
                <div id="container-neraca" class=""></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var res = @json($jsdata);
            let totalAktiva = collect(res.msg['Aset']).sum('saldo');
            let totalPassiva = parseFloat(collect(res.msg['Ekuitas']).sum('saldo')) +
                parseFloat(collect(res.msg['Kewajiban']).sum('saldo')) +
                parseFloat(res.laba_bulan);
            let selisihBalance = totalAktiva.toFixed(2) - totalPassiva.toFixed(2);
            let stringBalance = selisihBalance != 0 ?
                `😢 TIDAK BALANCE (${selisihBalance})` :
                `🎉 BALANCE`;

            let html = `
                <div class="row text-primary-dark">
                  <div class="col-xs-12 col-md-6">
                    <p class="text-primary-dark" style="font-size:16px;"><strong>ASET</strong></p>
                    ${res.msg['Aset'].map(data => `
                                              <div class="row">
                                                <div class="col-xs-5 col-md-5" style="border-bottom:1px solid #ddd">
                                                  <p>${data.name}</p>
                                                </div>
                                                <div class="col-xs-5 col-md-5 textright" style="border-bottom:1px solid #ddd">
                                                  <p>${formatRupiah(data.saldo)}</p>
                                                </div>
                                              </div>
                                            `).join('')}
                    <div class="row">
                      <div class="col-xs-5 col-md-5" style="border-top:2px solid black;">
                        <p><strong>Total ASET</strong></p>
                      </div>
                      <div class="col-xs-5 col-md-5 textright" style="border-top:2px solid black;">
                        <p>${formatRupiah(totalAktiva.toFixed(2))}</p>
                      </div>
                    </div>
                  </div>

                  <div class="col-xs-12 col-md-6 mt-20">
                    <p class="text-primary-dark" style="font-size:16px;"><strong>KEWAJIBAN</strong></p>
                    ${res.msg['Kewajiban'].map(data => `
                                              <div class="row">
                                                <div class="col-xs-5 col-md-5" style="border-bottom:1px solid #ddd">
                                                  <p>${data.name}</p>
                                                </div>
                                                <div class="col-xs-5 col-md-5 textright" style="border-bottom:1px solid #ddd">
                                                  <p>${formatRupiah(data.saldo)}</p>
                                                </div>
                                              </div>
                                            `).join('')}

                    <p class="text-primary-dark" style="font-size:16px;"><strong>EKUITAS</strong></p>
                    <div class="row">
                      <div class="col-xs-5 col-md-5" style="border-bottom:1px solid #ddd">
                        <p>Laba Bulan Berjalan</p>
                      </div>
                      <div class="col-xs-5 col-md-5 textright" style="border-bottom:1px solid #ddd">
                        <p>${formatRupiah(res.laba_bulan)}</p>
                      </div>
                    </div>

                    ${res.msg['Ekuitas'].map(data => `
                                              <div class="row">
                                                <div class="col-xs-5 col-md-5" style="border-bottom:1px solid #ddd">
                                                  <p>${data.name}</p>
                                                </div>
                                                <div class="col-xs-5 col-md-5 textright" style="border-bottom:1px solid #ddd">
                                                  <p>${formatRupiah(data.saldo)}</p>
                                                </div>
                                              </div>
                                            `).join('')}

                    <div class="row">
                      <div class="col-xs-5 col-md-5" style="border-top:2px solid black;">
                        <p><strong>Total KEWAJIBAN + EKUITAS</strong></p>
                      </div>
                      <div class="col-xs-5 col-md-5 textright" style="border-top:2px solid black;">
                        <p>${formatRupiah(totalPassiva.toFixed(2))}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <p style="font-size:20px;" class="mt-20 text-primary-dark">
                  <strong>${stringBalance}</strong>
                </p>
                `;

            $('#container-neraca').html(html);

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/neraca') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/neraca') }}?month=' + month + '&year=' + year;
            }
        </script>
    @endpush
</x-app-layout>
