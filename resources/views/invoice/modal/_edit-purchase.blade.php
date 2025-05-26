<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Invoice {{$data->invoice_number}} - {{$data->person->name}} <span class="fs-8 px-2 rounded-1 bg-primary text-white"> {{getModel($data->person_type)}} </span></h5>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <h5>List Detail</h5>
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga </th>
                        <th>Diskon</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="body-detail-invoice">
                    @foreach($data['details'] as $key => $item)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$item->stock->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{format_price($item->price)}}</td>
                        <td>{{format_price($item->discount)}}</td>
                        <td>{{format_price($item->total_price)}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center">Total</td>
                        <td>{{format_price($data->total_price)}}</td>
                    </tr>
            </table>
        </div>
