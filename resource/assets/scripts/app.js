/**
 * App.
 *
 * @module app
 */

/**
 * @class App
 */
var App = {
    LOADING_ACTION: '',
    init: function () {
    },
    scrollTo: function (offset) {
        $('html, body').animate({scrollTop: offset}, 400);
    },
    /**
     * Show loading element.
     *  
     * @method showLoading
     * @param {string} message - Message
     * @returns {void}
     */
    showLoading: function (message) {
        this.LOADING_ACTION = "";
        $.blockUI({
            message: '<img class="loading" alt=""src="' + $("#resPath").val() + 'images/progress.gif">' +
                    '<span class="message">' + message + '</span>',
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
    },
    showLoadingWithButton: function (message, button) {
        this.LOADING_ACTION = "";
        $.blockUI({
            message: '<img class="loading" alt=""src="' + $("#resPath").val() + 'images/progress.gif">' +
                    '<span class="message">' + message + '</span>' +
                    '<a class="btn btn-text">' + button + '</a>' +
                    '',
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
    },
    /**
     * Set message to Loading element.
     *  
     * @method setLoading
     * @returns {void}
     */
    setLoading: function (message) {
        $(".blockUI span.message").html(message);
    },
    setButton: function (button) {
        $(".blockUI a.btn").html(button);
    },
    /**
     * Hide loading element.
     *  
     * @method hideLoading
     * @returns {void}
     */
    hideLoading: function () {
        $.unblockUI();
    },
    reload: function () {
        document.location.reload();
    },
    /**
     * Init toast setting.
     *  
     * @method initToast
     * @returns {void}
     */
    initToast: function () {
        toastr.options = {
            "tapToDismiss": false,
            "closeButton": false,
            "debug": false,
            "newestOnTop": true,
            "progressBar": false,
            "positionClass": "toast-bottom-center",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "2000",
            "extendedTimeOut": "500",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    },
    /**
     * Show alarm toast.
     *  
     * @method showMessage
     * @param {string} message - Message
     * @returns {void}
     */
    showMessage: function (message) {
        this.initToast();
        toastr.info(message);
    },
    /**
     * Show success toast.
     *  
     * @method showSuccessMessage
     * @param {string} message - Message
     * @returns {void}
     */
    showSuccessMessage: function (message) {
        this.initToast();
        toastr.success(message);
    },
    /**
     * Show error toast.
     *  
     * @method showFailedMessage
     * @param {string} message - Message
     * @returns {void}
     */
    showFailedMessage: function (message) {
        this.initToast();
        toastr.error(message);
    },
    /**
     * Hide toast.
     *  
     * @method hideMessage
     * @returns {void}
     */
    hideMessage: function () {
        toastr.clear();
    },
    /**
     * Get current date. ( e.g: 2010-02-33 )
     *  
     * @method get_formatted_date
     * @returns {void}
     */
    get_formatted_date: function () {

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (dd < 10) {
            dd = '0' + dd
        }

        if (mm < 10) {
            mm = '0' + mm
        }

        today = yyyy + "-" + mm + '-' + dd;
        return today;
    },
    get_formatted_date_2: function () {

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (dd < 10) {
            dd = '0' + dd
        }

        if (mm < 10) {
            mm = '0' + mm
        }

        today = mm + "/" + dd + "/" + yyyy;
        return today;
    },
    /**
     * Get current time. ( e.g: 2009-10-22 22:33 )
     *  
     * @method get_formatted_date_time
     * @returns {void}
     */
    get_formatted_date_time: function () {

        var today = new Date();
        var mm = today.getMinutes();
        var hh = today.getHours();
        var dd = today.getDate();
        var MM = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (mm < 10) {
            mm = '0' + mm
        }

        if (hh < 10) {
            hh = '0' + hh
        }

        if (dd < 10) {
            dd = '0' + dd
        }

        if (MM < 10) {
            MM = '0' + MM
        }

        today = yyyy + "-" + MM + '-' + dd + ' ' + hh + ":" + mm;
        return today;
    },
    /**
     * Refresh select box.
     *  
     * @method refreshSelectBox
     * @returns {void}
     */
    refreshSelectBox: function () {
        $('.selectpicker').selectpicker('refresh');
    },
    showPromptDialog: function (placeholder, def, prompt, func_ok, func_cancel) {
        alertify.theme('bootstrap')
                .placeholder(placeholder)
                .defaultValue(def)
                .prompt(prompt, function (val, ev) {
                    ev.preventDefault();
                    func_ok(val);
                }, function (ev) {
                    ev.preventDefault();
                    func_cancel();
                });
    },
    showConfirmDialog: function (message, func_yes, func_no) {
        alertify.theme('bootstrap')
                .confirm(message, function (ev) {
                    ev.preventDefault();
                    func_yes();
                }, function (ev) {
                    ev.preventDefault();
                    func_no();
                });
    }
};
