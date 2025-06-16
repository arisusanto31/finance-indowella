<x-app-layout>
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat datang {{ user()->name }}! 🎉</h5>
                            <p class="mb-4">
                                Semangaat! semoga harimu menyenangkan dan produktif.
                            </p>
                            <a href="javascript:;" class="btn btn-sm btn-outline-primary">NERACA : BALANCE</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="../assets/img/illustrations/man-with-laptop-light.png" height="140"
                                alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                data-app-light-img="illustrations/man-with-laptop-light.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success"
                                        class="rounded" />
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Laba</span>
                            <h3 id="saldo-laba" class="card-title fs-4 mb-2"><i class="fas fa-spin fa-spinner"></i></h3>
                            <small id="prosen-laba" class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i>
                                --%</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="../assets/img/icons/unicons/wallet-info.png" alt="Credit Card"
                                        class="rounded" />
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span>Penjualan</span>
                            <h3 id="saldo-penjualan" class="card-title fs-4 text-nowrap mb-1"><i
                                    class="fas fa-spin fa-spinner"></i></h3>
                            <small id="prosen-penjualan" class="text-success fw-semibold"><i
                                    class="bx bx-up-arrow-alt"></i> --%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Revenue -->
        <div class="col-12 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
            <div class="card">
                <div class="row row-bordered g-0">
                    <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Total Revenue</h5>
                        <div id="totalRevenueChart" class="px-2"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                        id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        2022
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                        <a class="dropdown-item" href="javascript:void(0);">2021</a>
                                        <a class="dropdown-item" href="javascript:void(0);">2020</a>
                                        <a class="dropdown-item" href="javascript:void(0);">2019</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="growthChart"></div>
                        <div class="text-center fw-semibold pt-3 mb-2">62% Company Growth</div>

                        <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
                            <div class="d-flex">
                                <div class="me-2">
                                    <span class="badge bg-label-primary p-2"><i
                                            class="bx bx-dollar text-primary"></i></span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>2022</small>
                                    <h6 class="mb-0">$32.5k</h6>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="me-2">
                                    <span class="badge bg-label-info p-2"><i
                                            class="bx bx-wallet text-info"></i></span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>2021</small>
                                    <h6 class="mb-0">$41.2k</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Total Revenue -->
        <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
            <div class="row">
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="../assets/img/icons/unicons/paypal.png" alt="Credit Card"
                                        class="rounded" />
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="d-block mb-1">Hutang</span>
                            <h3 id="saldo-hutang" class="card-title text-nowrap fs-4 mb-2"><i
                                    class="fas fa-spin fa-spinner"></i></h3>
                            <small id="prosen-hutang" class="text-success fw-semibold"><i
                                    class="bx bx-up-arrow-alt"></i> --%</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="../assets/img/icons/unicons/cc-primary.png" alt="Credit Card"
                                        class="rounded" />
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Piutang</span>
                            <h3 id="saldo-piutang" class="card-title mb-2 fs-4"> <i
                                    class="fas fa-spin fa-spinner"></i></h3>
                            <small id="prosen-piutang" class="text-success fw-semibold"><i
                                    class="bx bx-up-arrow-alt"></i> --%</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                    <div class="card-title">
                                        <h5 class="text-nowrap mb-2">Saldo Akun Jurnal</h5>
                                        <!-- <span class="badge bg-label-warning rounded-pill">Year 2021</span> -->
                                        <select id="select-codegroup-custom" class="select-coa"
                                            onchange="getSaldoCustom()"></select>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <!-- <small class="text-success text-nowrap fw-semibold"><i class="bx bx-chevron-up"></i> 68.2%</small> -->
                                        <h3 id="saldo-custom" class="mb-0"><i class="fas fa-spin fa-spinner"></i>
                                        </h3>
                                    </div>
                                </div>
                                <div id="profileReportChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Order Statistics -->
        <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">List Data Bermasalah</h5>
                        <small class="text-muted">data yang belum ada jurnal / belum ada kartu </small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
                            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                            <a class="dropdown-item" href="javascript:void(loadDataError());">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Share</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex flex-column align-items-center gap-1">
                            <h2 class="mb-2" id="total-data-valid"> <i class="fas fa-spin fa-refresh"> </i> </h2>
                            <span>Total data</span>
                        </div>
                        <div id="errorDataChart"></div>
                    </div>
                    <ul class="p-0 m-0" id="list-data-valid">

                    </ul>
                </div>
            </div>
        </div>
        <!--/ Order Statistics -->

        <!-- Expense Overview -->
        <div class="col-md-6 col-lg-4 order-1 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0 me-2">Kecocokan kartu vs jurnal</h5>
                    <div class="row">
                        <div class="col-md-8">
                            <input value="{{ now() }}" type="datetime-local" class="form-control"
                                id="date-cocok" onfocusout="onFocusOutDate()" onfocus="onFocusInDate()" />
                        </div>
                    </div>
                </div>
                <div class="card-body ">
                    <div class="">
                        <h5 id="" class="pb-0 mt-3 mb-0 text-primary"> <i class="fas fa-circle"></i> Kartu
                            Stock <span style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-stock" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 id="" class="pb-0 mt-3 mb-0 text-primary"> <i class="fas fa-circle"></i> Kartu
                            BDP <span style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-bdp" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 id="" class="pb-0 mt-3 mb-0  text-primary"> <i class="fas fa-circle"></i> Kartu
                            Bahan Jadi <span style="font-size:14px"> vs jurnal</span>
                        </h5>
                        <div id="cocok-kartu-bahan-jadi" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 class="pb-0 mt-3 mb-0 text-primary"> <i class="fas fa-circle"></i> Kartu Piutang <span
                                style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-piutang" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 class="pb-0 mt-3 mb-0 text-primary"> <i class="fas fa-circle"></i> Kartu Hutang <span
                                style="font-size:14px"> vs jurnal</span></h5>
                        <div id="cocok-kartu-hutang" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 class="pb-0 mt-3 mb-0 text-primary">
                             <i class="fas fa-circle"></i> Kartu DP Sales <span
                                style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-dp" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 class="pb-0 mt-3 mb-0 text-primary">
                             <i class="fas fa-circle"></i> Inventaris <span
                                style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-inventaris" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>
                        <h5 class="pb-0 mt-3 mb-0 text-primary">
                             <i class="fas fa-circle"></i> BDD<span
                                style="font-size:14px"> vs jurnal</span> </h5>
                        <div id="cocok-kartu-bdd" class="ps-4">Rp 12.000.000 = Rp 12.000.000</div>

                    </div>
                </div>
            </div>
        </div>
        <!--/ Expense Overview -->

        <!-- Transactions -->
        <div class="col-md-6 col-lg-4 order-2 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Transactions</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/paypal.png" alt="User" class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Paypal</small>
                                    <h6 class="mb-0">Send money </h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">+82.6</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Wallet</small>
                                    <h6 class="mb-0">Mac'D</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">+270.69</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/chart.png" alt="User" class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Transfer</small>
                                    <h6 class="mb-0">Refund</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">+637.91</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/cc-success.png" alt="User"
                                    class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Credit Card</small>
                                    <h6 class="mb-0">Ordered Food</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">-838.71</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Wallet</small>
                                    <h6 class="mb-0">Starbucks</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">+203.33</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="../assets/img/icons/unicons/cc-warning.png" alt="User"
                                    class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Mastercard</small>
                                    <h6 class="mb-0">Ordered Food</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">-92.45</h6>
                                    <span class="text-muted">USD</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Transactions -->
    </div>

    @push('styles')
        <style>
            .custom-dashboard-style {
                color: red;
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            // Custom JavaScript code can be added here
            console.log('Dashboard script loaded');
            $(document).ready(function() {
                // Initialize the donut chart
                initItemSelectManual('#select-codegroup-custom', '{{ route('chart-account.get-item-all') }}?',
                    '--Pilih Akun--');
                loadDataError();
                getDataSaldoHighlight();
                $('#select-codegroup-custom').html('<option value="140000" selected >Persediaan </option>');
                getSaldoCustom();
                getSummaryBalance();
            });

            var lastDate = null;

            function onFocusOutDate() {
                dateCocok = $('#date-cocok').val();
                if (dateCocok != lastDate) {
                    lastDate = dateCocok;
                    getSummaryBalance();
                }

            }

            function onFocusInDate() {
                dateCocok = $('#date-cocok').val();
                lastDate = dateCocok;
                console.log(lastDate);
            }

            const donutChartConfig = {
                chart: {
                    height: 165,
                    width: 130,
                    type: 'donut'
                },
                labels: ['Jurnal', 'kartuStock', 'KartuHutang', 'KartuPiutang', 'kartu Inventory', 'kartu Prepaid'],
                series: [0, 0, 0, 0, 0, 0],
                colors: ['#696cff', '#8592a3', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d'],
                stroke: {
                    width: 5,
                    colors: ['#fff']
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                grid: {
                    padding: {
                        top: 0,
                        bottom: 0,
                        right: 15
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                value: {
                                    fontSize: '1.5rem',
                                    fontFamily: 'Public Sans',
                                    color: '#566a7f',
                                    offsetY: -15
                                },
                                name: {
                                    offsetY: 20,
                                    fontFamily: 'Public Sans'
                                },
                                total: {
                                    show: true,
                                    fontSize: '0.8125rem',
                                    color: '#a1acb8',
                                    label: 'Error'
                                }
                            }
                        }
                    }
                },
                yaxis: [ /* ❌ ini akan dibuang otomatis */ ],
                annotations: {
                    yaxis: [],
                    xaxis: [],
                    points: []
                },
                xaxis: {
                    convertedCatToNumeric: false
                }
            };
            console.log(donutChartConfig)
            // ✅ Function untuk bikin donut chart aman tanpa properti yang tidak perlu
            function createSafeDonutChart(elId, config) {
                const chartEl = document.querySelector(elId);
                if (!chartEl) return console.error('Element chart tidak ditemukan:', elId);

                const safeConfig = JSON.parse(JSON.stringify(config));

                // Hapus properti yang bisa memicu error
                delete safeConfig.yaxis;
                delete safeConfig.xaxis;
                delete safeConfig.annotations;

                const chart = new ApexCharts(chartEl, safeConfig);
                chart.render();
            }

            // 🔥 Panggil dengan ID chart kamu
            function loadDataError() {
                $.ajax({
                    url: '{{ route('dashboard.inspect-jurnal') }}',
                    method: 'get',
                    success: function(res) {
                        console.log(res);

                        if (res.status == 1) {
                            $('#total-data-valid').html(res.total);
                            // Handle success
                            html = `
                        <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded " style="background-color:#696cff"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Jurnal</h6>
                                    <small class="text-muted">data yang belum ada kartunya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_journal}</small>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color:#8592a3"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Kartu Stock</h6>
                                    <small class="text-muted">data yang belum ada jurnalnya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_kartu_stock}</small>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color:#03c3ec"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Kartu Hutang</h6>
                                    <small class="text-muted">data yang belum ada jurnalnya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_kartu_hutang}</small>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color:#71dd37"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Kartu Piutang</h6>
                                    <small class="text-muted">data yang belum ada jurnalnya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_kartu_piutang}</small>
                                </div>
                            </div>
                        </li> 
                         <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color:#ffab00"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Kartu Inventory</h6>
                                    <small class="text-muted">data yang belum ada jurnalnya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_kartu_inventory}</small>
                                </div>
                            </div>
                        </li> 
                         <li class="d-flex mb-3 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color:#ff3e1d"><i class="bx bx-book"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Kartu Prepaid</h6>
                                    <small class="text-muted">data yang belum ada jurnalnya</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${res.problem_kartu_prepaid}</small>
                                </div>
                            </div>
                        </li> 
                        `;
                            $('#list-data-valid').html(html);

                            donutChartConfig.series = [res.problem_journal, res.problem_kartu_stock, res
                                .problem_kartu_hutang, res.problem_kartu_piutang, res.problem_kartu_inventory,
                                res.problem_kartu_prepaid
                            ];
                            createSafeDonutChart('#errorDataChart', donutChartConfig);

                        } else {
                            // Handle error
                            Swal.fire('Error', 'Failed to load data: ' + res.msg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'AJAX request failed: ' + error, 'error');
                    }
                });
            }

            function getDataSaldoHighlight() {
                $.ajax({
                    url: '{{ route('jurnal.get-saldo-highlight') }}?date={{ Date('Y-m-d H:i:s') }}',
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            $('#saldo-hutang').html("Rp." + formatRupiahSimple(res.msg.saldo_hutang.msg));
                            $('#saldo-piutang').html("Rp." + formatRupiahSimple(res.msg.saldo_piutang.msg));
                            $('#saldo-laba').html("Rp." + formatRupiahSimple(res.msg.saldo_laba));
                            $('#saldo-penjualan').html("Rp." + formatRupiahSimple(res.msg.saldo_penjualan.msg));
                            // $('#prosen-hutang').html(res.prosen_hutang);
                            // $('#prosen-piutang').html(res.prosen_piutang);
                            // $('#prosen-penjualan').html(res.prosen_penjualan);
                        } else {
                            // Handle error
                            Swal.fire('Error', 'Failed to load data: ' + res.msg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'AJAX request failed: ' + error, 'error');
                    }
                });
            }

            function getSaldoCustom() {
                var code = $('#select-codegroup-custom option:selected').val();
                $.ajax({
                    url: '{{ url('admin/jurnal/get-saldo-custom') }}/' + code + '?date={{ Date('Y-m-d H:i:s') }}',
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {

                            $('#saldo-custom').html("Rp " + formatRupiahSimple(res.msg.msg));
                            // $('#prosen-hutang').html(res.prosen_hutang);
                            // $('#prosen-piutang').html(res.prosen_piutang);
                            // $('#prosen-penjualan').html(res.prosen_penjualan);
                        } else {
                            // Handle error
                            Swal.fire('Error', 'Failed to load data: ' + res.msg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        Swal.fire('Error', 'AJAX request failed: ' + error, 'error');
                    }
                });
            }

            function getSummaryBalance() {
                dateCocok = $('#date-cocok').val();
                url = '{{ url('admin/get-summary-balance') }}?date=' + dateCocok;
                console.log('get summary balance :'+url);
                $.ajax({
                    url: url,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            $('#cocok-kartu-stock').html("Rp " + formatRupiah(res.kartu_stock.saldo) + ' vs ' +
                                formatRupiah(res.kartu_stock.journal));
                            $('#cocok-kartu-bdp').html("Rp " + formatRupiah(res.kartu_bdp.saldo) + ' vs ' +
                                formatRupiah(res.kartu_bdp.journal));
                            $('#cocok-kartu-bahan-jadi').html("Rp " + formatRupiah(res.kartu_bahan_jadi.saldo) +
                                ' vs ' +
                                formatRupiah(res.kartu_bahan_jadi.journal));
                            $('#cocok-kartu-piutang').html("Rp " + formatRupiah(res.kartu_piutang.saldo) + ' vs ' +
                                formatRupiah(res.kartu_piutang.journal));
                            $('#cocok-kartu-hutang').html("Rp " + formatRupiah(res.kartu_hutang.saldo) + ' vs ' +
                                formatRupiah(res.kartu_hutang.journal));
                            $('#cocok-kartu-dp').html("Rp " + formatRupiah(res.kartu_dp.saldo) + ' vs ' +
                                formatRupiah(res.kartu_dp.journal));
                            $('#cocok-kartu-inventaris').html("Rp " + formatRupiah(res.kartu_inventaris.saldo) +
                                ' vs ' +
                                formatRupiah(res.kartu_inventaris.journal));
                            $('#cocok-kartu-bdd').html("Rp " + formatRupiah(res.kartu_bdd.saldo) + ' vs ' +
                                formatRupiah(res.kartu_bdd.journal));

                        } else {
                            swalInfo('opps', 'something error' + res.msg, 'error');
                        }
                    },
                    error: function(res) {
                        swalInfo('opps', 'something error', 'error');
                    }

                });
            }

        </script>
    @endpush
</x-app-layout>
