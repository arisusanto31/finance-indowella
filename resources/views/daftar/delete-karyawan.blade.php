<x-app-layout>
   

    <div class="container py-4 p-3 mb-4 card shadow-sm">
        <h2>data tehapus luurr</h2>

                      {{-- <div  class="btn btn-primary btn-big-custom rounded- mt-2">Create Karyawan</a> --}}

                        {{-- @section('content')
                        <h3>Data Karyawan yang Dihapus</h3> --}}
                    
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th class = "border px-4 py-2" >Nama</th>
                                    <th class = "border px-4 py-2" >NIK</th>
                                    <th class = "border px-4 py-2" >NPWP</th>
                                    <th class = "border px-4 py-2" >Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($karyawans as $karyawan)
                                    <tr>
                                        <td>{{ $karyawan->nama }}</td>
                                        <td>{{ $karyawan->nik }}</td>
                                        <td>{{ $karyawan->npwp }}</td>
                                        <td>{{ $karyawan->jabatan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                     
                </div>
            </div>
    
</x-app-layout>