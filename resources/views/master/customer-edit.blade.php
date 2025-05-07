<form action="{{ route('customer.main.update', $customer->id) }}" method="POST">
  @csrf
  @method('PUT')
<x-app-layout>

  <!-- Modal Edit untuk Customer -->
<div class="modal fade" id="editModal{{ $customer->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('customer.main.update', $customer->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel{{ $customer->id }}">Edit Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="name{{ $customer->id }}" class="form-label">Nama</label>
              <input type="text" class="form-control" id="name{{ $customer->id }}" name="name" value="{{ $customer->name }}" required>
            </div>
  
            <div class="mb-3">
              <label for="address{{ $customer->id }}" class="form-label">Alamat</label>
              <textarea class="form-control" id="address{{ $customer->id }}" name="address" rows="2">{{ $customer->address }}</textarea>
            </div>
  
            <div class="mb-3">
              <label for="phone{{ $customer->id }}" class="form-label">No HP</label>
              <input type="text" class="form-control" id="phone{{ $customer->id }}" name="phone" value="{{ $customer->phone }}">
            </div>
  
            <div class="mb-3">
              <label for="ktp{{ $customer->id }}" class="form-label">KTP</label>
              <input type="text" class="form-control" id="ktp{{ $customer->id }}" name="ktp" value="{{ $customer->ktp }}">
            </div>
  
            <div class="mb-3">
              <label for="npwp{{ $customer->id }}" class="form-label">NPWP</label>
              <input type="text" class="form-control" id="npwp{{ $customer->id }}" name="npwp" value="{{ $customer->npwp }}">
            </div>
  
            <div class="mb-3">
              <label for="purchase_info{{ $customer->id }}" class="form-label">Keterangan Pembelian</label>
              <input type="text" class="form-control" id="purchase_info{{ $customer->id }}" name="purchase_info" value="{{ $customer->purchase_info }}">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
 
              </x-app-layout>
        
