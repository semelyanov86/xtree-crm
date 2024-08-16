/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_Index_Js("PDFMaker_Extensions_Js", {
    licenseInstance: false,
    getInstance: function () {
        if (PDFMaker_License_Js.licenseInstance == false) {
            var instance = new window["PDFMaker_Extensions_Js"]();
            PDFMaker_License_Js.licenseInstance = instance;
            return instance;
        }
        return PDFMaker_License_Js.licenseInstance;
    }
}, {
	registerActions : function() {
            jQuery('#install_Workflow_btn').click(function(e) {
                    var extname = jQuery(e.currentTarget).data('extname');
                    app.helper.showProgress();
                    var params = {
                        'module': 'PDFMaker',
                        'action': 'IndexAjax',
                        'mode': 'installExtension',
                        'extname': extname
                    };
                app.request.get({'data' : params}).then(
                    function(err,response) {
                        app.helper.hideProgress();
                        if(err === null){
                            var result = response.success;
                            if(result == true) {
                                jQuery(e.currentTarget).hide();
                                jQuery('#install_' + extname + '_info').html(response['message']);
                                jQuery('#install_' + extname + '_info').removeClass('hide');

                                app.helper.showSuccessNotification({"message":response.message});
                            } else {
                                app.helper.showErrorNotification({"message":response.message});
                            }
                        }
                    }
                );
            });
            jQuery('.ext_btn').click(function(e) {
                    var extname = jQuery(e.currentTarget).data('extname');
                    app.helper.showProgress();
                    var url = jQuery(e.currentTarget).data('url');
                    window.location.href = url;
            });
	},
	registerEvents: function() {
        this._super();
		this.registerActions();
	}
});