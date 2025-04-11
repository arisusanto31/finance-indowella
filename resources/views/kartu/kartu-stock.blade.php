<x-app-layout>
    @push('styles')
    <style>
        .text-center th {
            vertical-align: middle;
        }
    </style>
    @endpush

    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>KARTU STOCK</strong> </h5>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <div class="row">
                <div class="col-xl-12 col-md-12">

                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button
                                    type="button"
                                    class="nav-link active"
                                    role="tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-home"
                                    aria-controls="navs-pills-top-home"
                                    aria-selected="true"
                                    onclick="getSummary()">
                                    🗃 KARTU
                                </button>
                            </li>
                            <li class="nav-item">
                                <button
                                    type="button"
                                    class="nav-link"
                                    role="tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-profile"
                                    aria-controls="navs-pills-top-profile"
                                    aria-selected="false"
                                    onclick="getMutasiMasuk()">
                                    📥 Masuk
                                </button>
                            </li>
                            <li class="nav-item">
                                <button
                                    type="button"
                                    class="nav-link"
                                    role="tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-messages"
                                    aria-controls="navs-pills-top-messages"
                                    aria-selected="false"
                                    onclick="getMutasiKeluar()">
                                    📤 Keluar
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" style="background-color: #f8f9fa;">
                            <div class="tab-pane fade show active" id="navs-pills-top-home" role="tabpanel">
                                <div class="table-responsive mt-2">
                                    <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
                                        <thead class="bg-white text-dark text-center">
                                            <tr>
                                                <th rowspan=2>No</th>
                                                <th rowspan=2> Nama Barang</th>
                                                <th rowspan=2> Kategori</th>
                                                <th colspan=3>Saldo Awal</th>
                                                <th colspan=3>Masuk</th>
                                                <th colspan=3>Keluar</th>
                                                <th colspan=3>Saldo Akhir</th>
                                            </tr>
                                            <tr>
                                                <th>Qty</th>
                                                <th>Rp/unit</th>
                                                <th>Total</th>
                                                <th>Qty</th>
                                                <th>Rp/unit</th>
                                                <th>Total</th>
                                                <th>Qty</th>
                                                <th>Rp/unit</th>
                                                <th>Total</th>
                                                <th>Qty</th>
                                                <th>Rp/unit</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-summary">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-profile" role="tabpanel">
                                <div class="row mt-1">
                                    <div class="col-md-2">
                                        <button type="button" class=" btn-primary" onclick="showModalMasuk()"> 🔃 buat mutasi</button>
                                    </div>
                                </div>
                                <div class="table-responsive mt-2">

                                    <table id="kartuMasuk" class="table table-bordered table-striped table-hover align-middle">
                                        <thead class="bg-white text-dark text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>📅Tanggal</th>
                                                <th> Kode barang</th>
                                                <th> Nama Barang</th>
                                                <th>Qty</th>
                                                <th>🔢 Satuan</th>
                                                <th>Rp/Unit</th>
                                                <th>Total</th>
                                            </tr>

                                        </thead>
                                        <tbody id="body-mutasi-masuk">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-messages" role="tabpanel">
                                <div class="col-md-2">
                                    <button type="button" class=" btn-primary" onclick="showModalOut()"> 🔃 buat mutasi</button>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table id="kartuKeluar" class="table table-bordered table-striped table-hover align-middle">
                                        <thead class="bg-white text-dark text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>📅Tanggal</th>
                                                <th> Kode barang</th>
                                                <th> Nama Barang</th>
                                                <th>Qty</th>
                                                <th>🔢 Satuan</th>
                                                <th>Rp/Unit</th>
                                                <th>Total</th>
                                            </tr>

                                        </thead>
                                        <tbody id="body-mutasi-keluar">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
    @push('scripts')
    <script>
        setTimeout(function() {
            getSummary();
        }, 200);

        function getSummary() {
            $.ajax({
                url: "{{ route('kartu-stock.get-summary') }}",
                method: "GET",
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        res.msg.forEach(function(item, i) {
                            rupiahUnitAwal = item.awal_qty > 0 ? item.awal_rupiah / item.awal_qty : 0;
                            rupiahUnitAkhir = item.akhir_qty > 0 ? item.akhir_rupiah / item.akhir_qty : 0;
                            masuk = [0, 0, 0];
                            keluar = [0, 0, 0];
                            if (array_key_exists(item.id, res.mutasi_masuk)) {
                                masuk[0] = res.mutasi_masuk[item.id].qty;
                                masuk[1] = res.mutasi_masuk[item.id].rupiah_unit;
                                masuk[2] = res.mutasi_masuk[item.id].total;
                            }
                            if (array_key_exists(item.id, res.mutasi_keluar)) {
                                keluar[0] = res.mutasi_keluar[item.id].qty;
                                keluar[1] = res.mutasi_keluar[item.id].rupiah_unit;
                                keluar[2] = res.mutasi_keluar[item.id].total;
                            }
                            html += `
                                <tr>
                                <td>${i+1}</td>
                                <td>${item.name} [${item.id}]</td>
                                <td>${item.category_name}</td>
                                <td>${formatRupiah(item.awal_qty/item.konversi)} ${item.unit_default}</td>
                                <td>${formatRupiah(rupiahUnitAwal*item.konversi)}</td>
                                <td>${formatRupiah(item.awal_rupiah)}</td>
                                <td>${formatRupiah(masuk[0]/item.konversi)} ${item.unit_default}</td>
                                <td>${formatRupiah(masuk[1]*item.konversi)}</td>
                                <td>${formatRupiah(masuk[2])}</td>
                                <td>${formatRupiah(keluar[0]/item.konversi)} ${item.unit_default}</td>
                                <td>${formatRupiah(keluar[1]*item.konversi)}</td>
                                <td>${formatRupiah(keluar[2])}</td>
                                <td>${formatRupiah(item.akhir_qty/item.konversi)} ${item.unit_default}</td>
                                <td>${formatRupiah(rupiahUnitAkhir*item.konversi)}</td>
                                <td>${formatRupiah(item.akhir_rupiah)}</td>
                                </tr>`;

                        });
                        $('#body-summary').html(html);
                        // $('#kartuKasTable').DataTable({
                        //     "destroy": true,
                        //     "order": [
                        //         [0, "asc"]
                        //     ],
                        //     "pageLength": 10,
                        //     "lengthMenu": [
                        //         [10, 25, 50, -1],
                        //         [10, 25, 50, "All"]
                        //     ],
                        // });
                    } else {

                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function showModalMasuk() {
            showDetailOnModal("{{ route('kartu-stock.create-mutasi-masuk') }}");
        }

        function showModalOut() {
            showDetailOnModal("{{ route('kartu-stock.create-mutasi-keluar') }}");
        }

        function getMutasiMasuk() {
            $.ajax({
                url: "{{ route('kartu-stock.get-mutasi-masuk') }}",
                method: "GET",
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        res.msg.forEach(function(item, i) {
                            html += `
                                <tr>
                                <td>${i+1}</td>
                                <td>${formatNormalDateTime(new Date(item.created_at))}</td>
                                <td>${item.stock_id}</td>
                                <td>${item.stock_name}</td>
                                <td>${formatRupiah(item.mutasi_quantity)}</td>
                                <td>${item.unit}</td>
                                <td>${formatRupiah(item.mutasi_rupiah_on_unit*(item.mutasi_qty_backend/item.mutasi_quantity))}</td>
                                <td>${formatRupiah(item.mutasi_rupiah_total)}</td>
                                </tr>`;
                        });
                        $('#body-mutasi-masuk').html(html);
                    } else {

                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function getMutasiKeluar() {
            $.ajax({
                url: "{{ route('kartu-stock.get-mutasi-keluar') }}",
                method: "GET",
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        res.msg.forEach(function(item, i) {
                            html += `
                                <tr>
                                <td>${i+1}</td>
                                <td>${formatNormalDateTime(new Date(item.created_at))}</td>
                                <td>${item.stock_id}</td>
                                <td>${item.stock_name}</td>
                                <td>${formatRupiah(item.mutasi_quantity)}</td>
                                <td>${item.unit}</td>
                                <td>${formatRupiah(item.mutasi_rupiah_on_unit*(item.mutasi_qty_backend/item.mutasi_quantity))}</td>
                                <td>${formatRupiah(item.mutasi_rupiah_total)}</td>
                                </tr>`;
                        });
                        $('#body-mutasi-keluar').html(html);
                    } else {

                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }
    </script>
    @endpush
</x-app-layout>