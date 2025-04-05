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
                                    aria-selected="true">
                                    🗃  KARTU
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
                                    aria-selected="false">
                                    📥  Masuk
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
                                    aria-selected="false">
                                    📤  Keluar
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
                                                <th rowspan=2>🔢 Kode barang</th>
                                                <th rowspan=2> Nama Barang</th>
                                                <th rowspan=2> Kategori</th>
                                                <th colspan=3>Saldo Awal</th>
                                                <th colspan=3>Masuk</th>
                                                <th colspan=3>Keluar</th>
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
                                            </tr>
                                        </thead>
                                        <tbody id="body-mutasi-bukubesar">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-profile" role="tabpanel">
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
</x-app-layout>