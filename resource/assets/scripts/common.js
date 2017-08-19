function hideAlert() {
    $("#msg_alert").html('');
}

function showAlert(msg) {
    $('html, body').animate({scrollTop: 0}, 400);
    $("#msg_alert").html(msg);
    setTimeout(hideAlert, 2000);
}

function getCurrentYear() {
    var today = new Date();
    var dd = today.getDate();
    var yyyy = today.getFullYear();
    return yyyy;
}

function getCurrentMonth() {
    var today = new Date();
    var mm = today.getMonth() + 1;//January is 0!`
    return mm;
}

function lpad(value){
    var str = "" + value;
    var pad = "00";
    var ans = pad.substring(0, pad.length - str.length) + str;
    return ans;
}

function showLoading() {
    var message = "LOADING.....";
//    Metronic.blockUI({target: 'body'});
    $.blockUI({message: '<img class="loading" alt="" src="' + $("#resPath").val() + 'assets/images/loading.gif">' + '<span class="message">' + message + '</span>',
        css: {
            backgroundColor: 'transparent',
            border: 'none',
        },
        baseZ: 9999,
        allowBodyStretch: false,
        bindEvents: false,
        focusInput: false,
        ignoreIfBlocked: true
    });
    
}

function hideLoading() {
    $.unblockUI();
}

function toCommaNumber(nStr) {
    nStr += '';

    if (!isNaN(nStr)) {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;        
    }
    
    return "";
}
