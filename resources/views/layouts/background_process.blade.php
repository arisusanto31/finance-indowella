<div id="div-progress-background" class="bg-light p-3 rounded hidden" style="position:fixed; top: 100px; right:20px; z-index:1000; width:300px;">

</div>



<script>
    function showAllBackgroundProcess() {
        allexistID = [];
        $('.div-bg-process').each(function(i, elem) {
            id = $(elem).attr('id').replace('div-bg-process', '');
            allexistID.push(id);
        });
        url='{{pathUrl()}}';
        console.log('path url:',url);
        allexistID = allexistID.join(',');
        $.ajax({
            url: '{{url("admin/get-background-process")}}',
            method: 'get',
            data: {
                followed_ids: allexistID,
                path_url: url
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    if(res.msg.length>0){
                        $('#div-progress-background').removeClass('hidden');
                    }else{
                        $('#div-progress-background').addClass('hidden');
                    }
                    res.msg.forEach(process => {
                        if ($('#div-bg-process' + process.id).length == 0) {
                            html = `
                                <div style="max-width:300px; width:100%;" class="mt-2 div-bg-process" id="div-bg-process${process.id}">
                                    <p>${process.description_process}</p>
                                    <span id="span-resume-bg${process.id}"> 
                                            <i class="fas fa-box colorblack ms-2" ></i>${process.total_task} 
                                            <i class="fas fa-box text-success ms-2" ></i>${process.success_task} 
                                            <i class="fas fa-box text-danger ms-2" ></i>${process.failed_task}
                                            <i class="fas fa-spinner fa-spin text-primary" style="margin-left: 20px;"></i> ${process.progress}%
                                    </span>
                                    <br>
                                    <span id="span-stage-bg${process.id}" class="text-muted"> ${process.stage_process}</span>
                                    <div class="progress progress-modern mb-3">
                                        <div class="progress-bar" id="bg-process${process.id}" role="progressbar" style="width: ${process.progress}%;">
                                            ${process.progress}%
                                        </div>
                                    </div>
                                    <div id="div-btn-confirm-bg${process.id}" class="text-center">
                                    ${process.progress>=100?`<button style="width:100%" onclick="setConfirmBackgroundProcess(${process.id})"> confirm</button>`:''}
                                    </div>
                                </div>
                            `;
                            $('#div-progress-background').append(html);
                        } else {
                            $(`#bg-process${process.id}`).css('width', process.progress + '%').text(process.progress + '%');
                            $('#span-resume-bg' + process.id).html(
                                `<i class="fas fa-box colorblack ms-2" ></i>${process.total_task} 
                                <i class="fas fa-box text-success ms-2" ></i>${process.success_task} 
                                <i class="fas fa-box text-danger ms-2" ></i>${process.failed_task}
                                <i class="fas fa-spinner fa-spin text-primary" style="margin-left: 20px;"></i> ${process.progress}%
                            `);
                            $('#span-stage-bg' + process.id).text(process.stage_process);
                            if(process.progress>=100){
                                $(`#div-btn-confirm-bg${process.id}`).html(`<button style="width:100%" onclick="setConfirmBackgroundProcess(${process.id})"> confirm</button>`);
                            }
                            // if (process.is_confirmed==true) {
                            //     $(`#bg-process${process.id}`).removeClass('bg-primary').addClass('bg-success');
                            //     setTimeout(() => {
                            //         $(`#div-bg-process${process.id}`).remove();
                            //     }, 2000);
                            // }
                        }

                    });
                } else {
                    notification('error', 'Error fetching background process: ' + res.msg);

                }
            },
            error: function(res) {
                console.error('Error fetching background process:', res);
            }
        });
    }

    function setConfirmBackgroundProcess(id){
        $.ajax({
            url: '{{url("admin/set-confirm-background-process")}}',
            method: 'post',
            data: {
                id: id,
                _token: '{{csrf_token()}}'
            },
            success: function(res) {
                if (res.status == 1) {
                    notification('success', 'success berhasil confirm','success');
                    $(`#div-bg-process${id}`).remove();
                } else {
                    notification('error', res.msg);
                }
            },
            error: function(res) {
                console.error('Error confirming background process:', res);
            }
        });
    }
    setInterval(showAllBackgroundProcess, 5000);
</script>