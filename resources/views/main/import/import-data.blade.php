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
                        $('#container-import-data').html('');
                        $('#container-import-data').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
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
