<x-app-layout>

    <style>
        btn-big-custom {
            padding: 20px 40px;
            font-size: 1.5rem;
            border-radius: 8px;
        }
    </style>

    <div class="card shadow-sm mb-4">
        <p class="text-primary-dark card-header" style="padding-bottom:0px;"> 📥 <strong>IMPORT DATA </strong> </p>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <input type="file" id="file-input" class="form-control" />
                <button class="btn btn-primary mt-2" onclick="importData()">Import Data</button>
            </div>
        </div>

        <div class="card-body mt-2" id="container-import-data">
        </div>
    </div>

    @push('scripts')
        <script>
            var kartuStocks = [];
            var stockMasuks = [];
            var stockKeluars = [];

            function importData() {
                let fileInput = document.getElementById('file-input');
                let file = fileInput.files[0];
                let formData = new FormData();
                formData.append('file', file);
                loading(1);
                $.ajax({
                    url: "{{ route('jurnal.import-data') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        loading(0);
                        if (response.status == 1) {
                            $('#container-import-data').html('');
                            // $('#container-import-data').html('<pre>' + JSON.stringify(response, null, 2) +
                            // '</pre>');
                            kartuStocks = response.msg.kartu_stock;
                            stockMasuks = response.msg.stock_masuk;
                            stockKeluars = response.msg.stock_keluar;
                            html = `
                                <p><strong>Kartu Stock:</strong></p>
                                   <div class="table-responsive mt-2">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Nama Barang</th>
                                            <th rowspan="2">Kode Barang</th>
                                            <th colspan="3">Awal </th>
                                            <th colspan="3">Masuk</th>
                                            <th colspan="3">Keluar</th>
                                            <th colspan="3">Akhir</th>
                                        </tr>
                                        <tr>
                                            <th>Qty</th>
                                            <th>Hpp</th>
                                            <th>Total</th>

                                            <th>Qty</th>
                                            <th>Hpp</th>
                                            <th>Total</th>

                                            <th>Qty</th>
                                            <th>Hpp</th>
                                            <th>Total</th>

                                             <th>Qty</th>
                                            <th>Hpp</th>
                                            <th>Total</th>


                                        </tr>
                                    </thead>
                                    <tbody>

                                        ${kartuStocks.map((item, index) => `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${item.nama_barang}</td>
                                                <td>${item.kode_barang}</td>
                                                <td>${formatRupiah(item.saldo_awal_quantity)}</td>
                                                <td>${formatRupiah(item.saldo_awal_hpp)}</td>
                                                <td>${formatRupiah(item.saldo_awal_total)}</td>   
                                                <td>${formatRupiah(item.mutasi_masuk_quantity)}</td>
                                                <td>${formatRupiah(item.mutasi_masuk_hpp)}</td>
                                                <td>${formatRupiah(item.mutasi_masuk_total)}</td>
                                                <td>${formatRupiah(item.mutasi_keluar_quantity)}</td>
                                                <td>${formatRupiah(item.mutasi_keluar_hpp)}</td>
                                                <td>${formatRupiah(item.mutasi_keluar_total)}</td>
                                                <td>${formatRupiah(item.saldo_akhir_quantity)}</td>
                                                <td>${formatRupiah(item.saldo_akhir_hpp)}</td>
                                                <td>${formatRupiah(item.saldo_akhir_total)}</td>
                                            </tr>
                                            `).join('')}
                                </tbody>
                                </table>
                                </div>
                            `;


                            html+=`
                                <p class="mt-5"><strong>Stock Masuk:</strong></p>
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Supplier </th>
                                            <th>No PO </th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Quantity</th>
                                            <th>Hpp</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${stockMasuks.map((item, index) => `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${item.tanggal}</td>
                                                <td>${item.supplier}</td>
                                                <td>${item.no_po}</td>
                                                <td>${item.kode_barang}</td>
                                                <td>${item.nama_barang}</td>
                                                <td>${formatRupiah(item.quantity)}</td>
                                                <td>${formatRupiah(item.hpp)}</td>
                                                <td>${formatRupiah(item.total)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>

                                </div>  

                            `; 
                            html+=`
                                <p class="mt-5"><strong>Stock Keluar:</strong></p>
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Quantity</th>
                                            <th>Hpp</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${stockKeluars.map((item, index) => `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${item.tanggal}</td>
                                                <td>${item.kode_barang}</td>
                                                <td>${item.nama_barang}</td>
                                                <td>${formatRupiah(item.quantity)}</td>
                                                <td>${formatRupiah(item.hpp)}</td>
                                                <td>${formatRupiah(item.total)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>

                                </div>  

                            `;
                            $('#container-import-data').html(html);
                        } else {
                            Swal.fire('error', 'Import failed: ' + response.msg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        loading(0);
                        console.error("Import failed:", error);
                        Swal.fire('error', 'Import failed: ' + error, 'error');
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
