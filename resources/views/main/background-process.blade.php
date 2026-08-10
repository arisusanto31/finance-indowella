<x-app-layout>
    <div class="mb-4 card shadow p-3">
        <h4 class="text-primary "> ⚙️ <strong>Background Process </strong> </h4>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center"><strong>Process</strong></th>
                        <th class="text-center"><strong>monitoring url </strong></th>
                        <th class="text-center"><strong>Resume</strong></th>
                        <th class="text-center"><strong>Stage</strong></th>
                        <th class="text-center"><strong>Progress</strong></th>
                        <th class="text-center"><strong>Action</strong></th>
                    </tr>
                </thead>
                <tbody id="">
                    @foreach($backgroundProcess as $process)
                    <tr id="tr-bg-process{{$process->id}}">
                        <td>{{$process->description_process}}</td>
                        <td><a href="{{url($process->monitoring_url)}}" target="_blank">{{$process->monitoring_url}}</a></td>
                        <td>
                            <i class="fas fa-box colorblack ms-2"></i>{{$process->total_task}}
                            <i class="fas fa-box text-success ms-2"></i>{{$process->success_task}}
                            <i class="fas fa-box text-danger ms-2"></i>{{$process->failed_task}}
                        </td>
                        <td id="" class="text-muted"> {{$process->stage_process}}</td>
                        <td>
                            <div class="progress progress-modern mb-3">
                                <div class="progress-bar" id="bg-process{{$process->id}}" role="progressbar" style="width: {{$process->progress}}%;">
                                    {{$process->progress}}%
                                </div>
                            </div>
                        </td>
                        <td id="" class="text-center">
                            @if($process->progress>=100)
                            <button style="width:100%" onclick="setConfirmBackgroundProcess('{{$process->id}}')"> confirm</button>
                            @else
                            <button style="width:100%" onclick="deleteBackgroundProcess('{{$process->id}}')"> delete </button>
                            @endif
                        </td>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteBackgroundProcess(id) {
            Swal.fire({
                title: "Apakah kamu yakin ?",
                text: "kamu akan menghapus background process ini",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                allowOutsideClick: false,
                didOpen: () => {
                    $('.swal2-container').css('z-index', 2000);
                },
                preConfirm: () => {
                    $.ajax({
                        url: '{{url("admin/delete-background-process")}}',
                        type: 'POST',
                        data: {
                            id: id,
                            _token: '{{csrf_token()}}'
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                notification('success', res.msg);
                                $(`#tr-bg-process${id}`).remove();
                            } else {
                                notification('error', res.msg);
                            }
                        },
                        error: function(res) {
                            console.error('Error deleting background process:', res);
                        }
                    });
                }
            });
        }
    </script>
    @endpush

</x-app-layout>