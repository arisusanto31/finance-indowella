<x-app-layout>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif



    <div class="card mb-4 shadow p-3">

        <div class="text-primary-dark "> 📁 <strong>Kebutuhan bahan transaksi marked </strong> </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="table-kebutuhan-produksi">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Stock Sisa</th>
                        <th>Kebutuhan</th>
                        <th>Kekurangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kebutuhanProduksi as $item)
                        @php

                            $sisa = array_key_exists($item['id'], $sisaStock) ? $sisaStock[$item['id']] : 0;
                            $kekurangan = $sisa - $item['quantity'];
                            if ($kekurangan > 0) {
                                $kekurangan = 0;
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $sisa }} {{ $item['unit'] }}</td>
                            <td>{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                            <td
                                @if ($kekurangan == 0) class="text-success"
                                @else class="text-danger" @endif>
                                {{ $kekurangan }} {{ $item['unit'] }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>


        </div>
    </div>








    @push('styles')
        <style>
            .btn-close-card {
                position: absolute;
                top: -12px;
                right: -12px;
                width: 30px;
                height: 30px !important;
                background-color: red;
                border: none;
                border-radius: 50%;
                font-size: 23px;
                font-weight: bold;
                cursor: pointer;
                color: #fff;
                z-index: 10;
            }

            .centered-flex {
                display: flex;
                justify-content: center;
                /* Horizontal */
                align-items: center;
                height: 30px;
                width: 30px;
                /* Vertical */
                /* Kalau mau vertikal tengah terhadap layar penuh */
            }
        </style>
    @endpush

    @push('scripts')
        <script></script>
    @endpush
</x-app-layout>
