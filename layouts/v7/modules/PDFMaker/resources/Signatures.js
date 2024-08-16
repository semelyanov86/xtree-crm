Vtiger_Index_Js('PDFMaker_Signatures_Js', {
    registerEvents: function () {
        this._super();
        this.registerDelete();
    },
    registerDelete: function() {
        $('#SignaturesContainer').on('click', '.deleteSignature', function(e) {
            e.preventDefault();

            let href = $(this).attr('href');

            app.helper.showConfirmationBox({'message': app.vtranslate('JS_DELETE_CONFIRM')}).then(function (data) {
                app.helper.showProgress();
                app.request.post({'url': href}).then(function (error, data) {
                    app.helper.hideProgress();

                    if (!error) {
                        app.helper.showSuccessNotification({'message': app.vtranslate('JS_DELETE_SUCCESS')});
                        location.reload();
                    } else {
                        app.helper.showErrorNotification({'message': error.message});
                    }
                });
            });
        });
    },
})