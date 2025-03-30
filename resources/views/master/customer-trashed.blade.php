<x-app-layout>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>KTP</th>
                <th>NPWP</th>
                <th>Keterangan Pembelian</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $index => $customer)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->address }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->ktp }}</td>
                <td>{{ $customer->npwp }}</td>
                <td>{{ $customer->purchase_info }}</td>
                <td>
                    <form action="{{ route('customers.restore', $customer->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-success">Pulihkan</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada customer yang terhapus.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    

</x-app-layout>