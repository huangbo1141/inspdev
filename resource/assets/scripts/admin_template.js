function sendFile(file) {
    data = new FormData();
    data.append("file", file);//You can append as many data as you want. Check mozilla docs for this
    $.ajax({
        data: data,
        dataType: 'json',
        type: "POST",
        url: $("#basePath").val() + 'api/upload/template',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.code == 0) {
                $('#template').summernote('editor.insertImage', data.url, data.path);
            }
        }
    });
}

function submit_data() {
    var t = $('#template').summernote('code');
    if (t=='') {
        showAlert("Please input template!");
        return false;
    }
    
    showLoading();

    $.ajax({
        type: "POST",
        url: 'update_template.html',
        data: {
            template: t
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.err_code == 0) {
                showAlert("Successfully Updated!");
            } else {
                showAlert("Failed to Update!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });            
}

jQuery(document).ready(function () {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }
    
    $('#template').summernote({
        toolbar: [
            ['style', ['style']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['picture']],
        ],        
        
        minHeight: 400,
        focus: false,
        disableDragAndDrop: true,
        
        callbacks: {
            onImageUpload: function (files) {
                sendFile(files[0]);
            }
        }
    });
    
    $(".btn-submit").on('click', function(e) {
        e.preventDefault();
        
        submit_data();
    });

});
