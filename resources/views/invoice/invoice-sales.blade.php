<x-app-layout>
    <div class="card shadow-sm rounded-3 ">

        <h5 class="text-primary-dark card-header"> <a href="javascript:void(openCardCreate())">⚒️ <strong>BUAT MUTASI JURNAL
            </strong>
            <i id="icon-create" class="bx bx-caret-down toggle-icon"></i> </a>
        </h5>
    
        <div id="card-create" class="tree-toggle">
    
          <input type="hidden" value="{{Date('Y-m-d H:i:s')}}" id="date-mutasi" />
          <div class="card-body" style="padding-top: 0px;">
            <div>
              Debit
              <button type="button" class="btn btn-sm btn-success ms-2" id="addDebit">+tambah</button>
            </div>
            <div id="div-debet" class="debet-wrapper">
              <div id="debet1" class="row rowdebet g-2 mb-2 ">
                <div class="col-md-4">
                  <select id="dcodegroup1" class="form-control select-coa">
                  </select>
                </div>
                <div class="col-md-4">
                  <input id="dnote1" type="text" class="form-control" placeholder="Note">
                </div>
                <div class="col-md-4">
                  <input id="damount1" type="text" class="form-control currency-input" placeholder="Amount">
                </div>
              </div>
            </div>

    @push('scripts')
    @if(session('success'))
    @endif
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        function ShowModalInvoice(){
            showDetailOnModal("{{ route('supplier.main.create') }}");
        }
    
        $(document).ready(function () {
            $('#supplier-table').DataTable();
        });
    
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil lurrr!',
                text: '{{ session("success")}}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
    @endpush
    
   
</x-app-layout>




