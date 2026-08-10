<x-app-layout>
    <div class="mb-4 card shadow p-3">
        <h4 class="text-primary "> 🗃️<strong> FILE ARSIP </strong> </h4>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>key file</th>
                        <th>bulan </th>
                        <th>tahun</th>
                        <th>file</th>

                    </tr>
                </thead>
                <tbody id="">
                    @foreach($files as $i=>$file)
                    <tr id="">
                        <td>{{$i+1}}</td>
                        <td>{{$file->key_file}}</td>
                        <td>{{$file->month}}</td>
                        <td>{{$file->year}}</td>
                        <td><a href="{{asset('storage/'.$file->file_path)}}" target="_blank"><i class="fas fa-file-excel"></i> {{$file->file_path}}</a></td>

                    </tr>
                    @endforeach
            </table>
        </div>
    </div>



    @push('scripts')
    <script>

    </script>
    @endpush

</x-app-layout>