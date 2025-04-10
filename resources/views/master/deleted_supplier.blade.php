<x-app-layout>
    <div class="container mt-4">
        <h4 class="mb-3">📦 Supplier yang Terhapus</h4>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>KTP</th>
                    <th>NPWP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deletedSuppliers as $supplier)

                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->address }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ $supplier->ktp }}</td>
                        <td>{{ $supplier->npwp }}</td>
                        <td>
                            {{-- <form action="{{ route('supplier.main.restore', $supplier->id) }}" method="POST" onsubmit="return confirm('Pulihkan supplier ini?')">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm">Pulihkan</button>
                            </form> --}}
                            <form action="{{ route('supplier.main.restore', $supplier->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Pulihkan</button>
                            </form>
                            
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada supplier yang dihapus.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('supplier.main.index') }}" class="btn btn-secondary mt-3">← Kembali ke Data Aktif</a>
    </div>
</x-app-layout>
