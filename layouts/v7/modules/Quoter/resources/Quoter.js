/* ********************************************************************************
 * The content of this file is subject to the Quoter ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
Vtiger_Edit_Js("Quoter_Js", {
    zeroDiscountType: 'zero',
    percentageDiscountType: 'percentage',
    directAmountDiscountType: 'amount',

    individualTaxType: 'individual',
    groupTaxType: 'group',
    loadLineItemsDetail: function (targetRecord, targetModule) {
        params = {
            module: 'Quoter',
            view: 'MassActionAjax',
            mode: 'getItemsDetail',
            record: targetRecord,
            current_module: targetModule
        };
        app.helper.showProgress();
        AppConnector.request(params).then(
            function (response) {
                app.helper.hideProgress();
                jQuery('.lineItemTableDiv').html(response.result.html);
                jQuery('.lineItemTableDiv').css({'background-color':'#fff'});
                jQuery("form#detailView .details > table.lineItemsTable").html(response.result.html1);
                Quoter_Js.registerEventForProductImages();
                Quoter_Js.registerItemTaxPercentShowEvent();
            }
        );
    },
    registerItemTaxPercentShowEvent : function(){
        jQuery(document).ajaxComplete(function(event, request, settings){
            if(settings.data != undefined && settings.data.indexOf("module=Quoter") != -1 && settings.data.indexOf("view=MassActionAjax") != -1 && settings.data.indexOf("mode=getItemsDetail") != -1){
                var itemTaxPercent = $('.itemTaxPercent');
                itemTaxPercent.each(function(k,item){
                    $(item).popover();
                    $(item).on('click',function(){
                        $(this).popover('toggle');
                    });
                });
            }
        });
    },
    registerEventForSelectSubProducts: function () {
        jQuery('button.selectRelationNew').on('click', function () {
            var thisInstance = new Vtiger_Detail_Js();
            var selectedTabElement = thisInstance.getSelectedTab();
            var relatedModuleName = thisInstance.getRelatedModuleName();
            var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
            var aDeferred = jQuery.Deferred();
            var popupInstance = Vtiger_Popup_Js.getInstance();
            var parameters = {
                'module': 'Quoter',
                'src_module': 'Products',
                'src_record': thisInstance.getRecordId(),
                'multi_select': true
            };
            popupInstance.show(parameters, function (responseString) {
                    var responseData = JSON.parse(responseString);
                    var relatedIdList = Object.keys(responseData);
                    relatedController.addRelations(relatedIdList).then(
                        function (data) {
                            var relatedCurrentPage = relatedController.getCurrentPageNum();
                            var params = {'page': relatedCurrentPage};
                            relatedController.loadRelatedList(params).then(function (data) {
                                aDeferred.resolve(data);
                            });
                        }
                    );
                }
            );
            return aDeferred.promise();
        });
    },
    fixWidthInput: function (row) {
        row.find('textarea').width('90%');
        jQuery('.divLineItemContainer').width("100%");
        jQuery('#lineItemTab .lineItemContainer textarea').attr('rows', '2');

        //fix for first column
        var productEle = row.find('.productName');
        var containerWidth = productEle.closest('td').width();
        var siblingWidth = 0;
        productEle.siblings().each(function () {
            siblingWidth += jQuery(this).outerWidth();
        });
        productEle.outerWidth(containerWidth - siblingWidth - 20);

        //fix for default columns
        row.find('.qty,.listPrice,.discount_amount,.discount_percent').each(function () {
            var tdContainer = jQuery(this).closest('td');
            var tdContainerWidth = tdContainer.width();
            if (jQuery(this).is('.listPrice')) {
                var imgIconWidth = tdContainer.find('i').outerWidth();
                jQuery(this).outerWidth(tdContainerWidth - imgIconWidth - 5)
            } else {
                jQuery(this).outerWidth(tdContainerWidth);
            }
        });

        //fix for custom columns and tax total column
        jQuery('.input-group', row).css('min-width', '50px');
        row.find('div[data-rowid^="cf_"],.tax_total').each(function () {
            var tdWidth = jQuery(this).closest('td').outerWidth();
            var customField = jQuery(this).find('input[name^="cf_"]');
            jQuery('.input-group', jQuery(this)).outerWidth(50);
            if (customField.closest('.referencefield-wrapper').length > 0) {
                var addOnWidth = jQuery(this).find('.input-group-addon').outerWidth();
                var clearButtonWidth = jQuery('.clearReferenceSelection', jQuery(this)).outerWidth();
                customField.outerWidth(tdWidth - addOnWidth - clearButtonWidth - 17);
            } else if (customField.next().is('.input-group-addon') || customField.prev().is('.input-group-addon')) {
                var addOnWidth = jQuery(this).find('.input-group-addon').outerWidth();
                customField.outerWidth(tdWidth - addOnWidth - 17);
            } else if (jQuery(this).is('.tax_total')) {
                var addOnWidth = jQuery(this).next().outerWidth();
                jQuery(this).outerWidth(tdWidth - addOnWidth - 17);
            } else {
                customField.outerWidth(tdWidth - 17);
            }
            jQuery(this).find('select[name^="cf_"]').outerWidth(tdWidth - 17);
            jQuery(this).find('textarea[name^="cf_"]').css('resize', 'vertical');
        });
    },
    addSectionDropDown: function (sectionSettings) {
        var dropDownEle = '<span class="btn-group section_container"> ' +
            '<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"> ' +
            '<i class="fa fa-plus"></i> &nbsp;' +
            '<strong>Add Section</strong>&nbsp;&nbsp; ' +
            '<i class="caret"></i> ' +
            '</button> ' +
            '<ul class="dropdown-menu">';
        if(sectionSettings.length > 0){
            jQuery.each(sectionSettings, function (index, val) {
                var li = '<li><a href="javascript:void(0)" data-subsection="true" class="section_item" data-section = "' + val + '">' + val + '</a></li>';
                dropDownEle += li;
            });
        }else{
            var li = '<li><a href="javascript:void(0)" data-subsection="false" class="section_item">No sections available. Please go to extension settings to configure.</a></li>';
            dropDownEle += li;
        }
        dropDownEle += '</ul></span>';
        jQuery('#addService').closest('.btn-toolbar').append(dropDownEle);
    },
    addRunningSubTotalDropDown: function (totalSetting) {
        var dropDownEle = '<span class="btn-group addRunningSubTotal"> ' +
            '<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"> ' +
            '<i class="fa fa-plus"></i>&nbsp;' +
            '<strong>Add Running Sub Total</strong>&nbsp;&nbsp; ' +
            '<i class="caret"></i> ' +
            '</button> ' +
            '<ul class="dropdown-menu">';
        var isRunningSubTotalExists = false;
        jQuery.each(totalSetting, function (index, val) {
            var newKey = index.split('__');
            index = newKey[1];
            if (val.isRunningSubTotal == '1') {
                isRunningSubTotalExists = true;
                var li = '<li><a href="javascript:void(0)" data-subtotal="true" class="runningSubTotalItem" data-item-name = "' + index + '" data-running-label ="'+val.label_running+'">' + val.fieldLabel + '</a></li>';
                dropDownEle += li;
            }
        });
        if(isRunningSubTotalExists == false){
            var li = '<li><a href="javascript:void(0)" data-subtotal="false" class="runningSubTotalItem">No running subtotals available. Please go to extension settings to configure.</a></li>';
            dropDownEle += li;
        }
        dropDownEle += '</ul></span>';
        jQuery('#addService').closest('.btn-toolbar').append(dropDownEle);
    },
    registerEventForProductImages: function () {
        var thisInstance = this;
        var lineItemContainer = jQuery('#lineItemTable tbody.listItem');
        jQuery('.product_image').on('click', lineItemContainer, function () {
            var productId = jQuery(this).data('productid');
            if (!isNaN(productId)) {
                var params = {
                    module: 'Quoter',
                    view: 'MassActionAjax',
                    mode: 'getProductImages',
                    productid: productId
                };
                var progressInstance = jQuery.progressIndicator();
                AppConnector.request(params).then(
                    function (data) {
                        progressInstance.hide();
                        app.showModalWindow(data, {'height': '600px'});
                        var imageContainer = jQuery('.imageContainer');
                        imageContainer.cycle({
                            fx: 'fade',
                            speed: 'fast',
                            timeout: 0,
                            next: '.next',
                            prev: '.prev',
                            slideResize: 1,
                            fit: 1,
                            width: 900
                        });
                        imageContainer.find('img').on('mouseenter', function () {
                            imageContainer.cycle('pause');
                        }).on('mouseout', function () {
                            imageContainer.cycle('resume');
                        })
                    },
                    function (error) {
                        progressInstance.hide();
                        var progressInstance = jQuery.progressIndicator();
                        console.log(error);
                    }
                );
            }

        });
    }

}, {
    arrActivityid : [],
    maxLevel: 4,
    //Container which stores the line item elements
    lineItemContentsContainer: false,
    //Container which stores line item result details
    lineItemResultContainer: false,
    //contains edit view form element
    editViewForm: false,

    //a variable which will be used to hold the sequence of the row
    rowSequenceHolder: false,

    //holds the element which has basic hidden row which we can clone to add rows
    basicRow: false,

    //will be having class which is used to identify the rows
    rowClass: 'lineItemRow',
    columnSetting: {},
    regionElement: false,

    prevSelectedCurrencyConversionRate: false,
    getVtigerVersion: function(){
        var version = '';
        var scripts = document.getElementsByTagName("script")
        for (var i = 0; i < scripts.length; ++i) {
            var src = scripts[i].src;
            if(src.indexOf('.js?v=')>-1){
                var versionTmp = src.split('js?v=');
                version = versionTmp[1];
                break;
            }else if(src.indexOf('.js?&v=')>-1){
                var versionTmp = src.split('.js?&v=');
                version = versionTmp[1];
                break;
            }
        }
        return version;
    },
    numberFormat: function (number) {

        number += '';
        x = number.split('.');
        x1 = x[0];
        var decimalseparator = $('body').data('user-decimalseparator');
        var groupingseparator = $('body').data('user-groupingseparator');

        if(typeof this.separator !='undefined') {
            decimalseparator = this.separator.currency_decimal_separator;
        }
        x2 = x.length > 1 ? decimalseparator + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        if(typeof this.separator !='undefined') {
            groupingseparator = this.separator.currency_grouping_separator;
        }
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + groupingseparator + '$2');
        }
        return x1 + x2;
    },

    getRegionElement: function () {
        if (this.regionElement == false) {
            this.regionElement = jQuery('#region_id');
        }
        return this.regionElement;
    },

    searchModuleNames: function (params) {
        var aDeferred = jQuery.Deferred();
        if (typeof ProductServiceLookup_Js !== 'undefined') {
            // params.module = 'ProductServiceLookup';
            params.action = 'ActionAjax';
        } else {
            params.source_module = app.getModuleName();
            params.action = 'BasicAjax';
            params.module = 'Quoter';
        }

        AppConnector.request(params).then(
            function (data) {
                aDeferred.resolve(data);
            },
            function (error) {
                //TODO : Handle error
                aDeferred.reject();
            }
        );
        return aDeferred.promise();
    },

    /**
     * Function that is used to get the line item container
     * @return : jQuery object
     */
    getLineItemContentsContainer: function () {
        if (this.lineItemContentsContainer == false) {
            this.setLineItemContainer(jQuery('#lineItemTab .lineItemContainer'));
        }
        return this.lineItemContentsContainer;
    },

    /**
     * Function to set line item container
     * @params : element - jQuery object which represents line item container
     * @return : current instance ;
     */
    setLineItemContainer: function (element) {
        this.lineItemContentsContainer = element;
        return this;
    },

    /**
     * Function to get the line item result container
     * @result : jQuery object which represent line item result container
     */
    getLineItemResultContainer: function () {
        if (this.lineItemResultContainer == false) {
            this.setLinteItemResultContainer(jQuery('#lineItemResult'));
        }
        return this.lineItemResultContainer;
    },

    /**
     * Function to set line item result container
     * @param : element - jQuery object which represents line item result container
     * @result : current instance
     */
    setLinteItemResultContainer: function (element) {
        this.lineItemResultContainer = element;
        return this;
    },

    /**
     * Function which will give the closest line item row element
     * @return : jQuery object
     */
    getClosestLineItemRow: function (element) {
        return element.closest('tr.' + this.rowClass);
    },

    getShippingAndHandlingControlElement: function () {
        return jQuery('#shipping_handling_charge');
    },

    getAdjustmentTypeElement: function () {
        return jQuery('input:radio[name="adjustmentType"]');
    },

    getAdjustmentTextElement: function () {
        return jQuery('#adjustment');
    },

    getTaxTypeSelectElement: function () {
        return jQuery('#taxtype');
    },

    isIndividualTaxMode: function () {
        var taxTypeElement = this.getTaxTypeSelectElement();
        var selectedOption = taxTypeElement.find('option:selected');
        if (selectedOption.val() == Inventory_Edit_Js.individualTaxType) {
            return true;
        }
        return false;
    },

    isGroupTaxMode: function () {
        var taxTypeElement = this.getTaxTypeSelectElement();
        var selectedOption = taxTypeElement.find('option:selected');
        if (selectedOption.val() == Inventory_Edit_Js.groupTaxType) {
            return true;
        }
        return false;
    },

    /**
     * Function which gives edit view form
     * @return : jQuery object which represents the form element
     */
    getForm: function () {
        if (this.editViewForm == false) {
            this.editViewForm = jQuery('#EditView');
        }
        return this.editViewForm;
    },

    /**
     * Function which gives quantity value
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getQuantityValue: function (lineItemRow) {
        return parseFloat(jQuery('.qty', lineItemRow).val());
    },

    /**
     * Function which will give me list price value
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getListPriceValue: function (lineItemRow) {
        return parseFloat(jQuery('.listPrice', lineItemRow).val());
    },

    setListPriceValue: function (lineItemRow, listPriceValue) {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var listPrice = parseFloat(listPriceValue).toFixed(numberOfDecimal);
        lineItemRow.find('.listPrice').val(listPrice);
        if(lineItemRow.find('.td_listprice') != undefined){
            lineItemRow.find('.td_listprice').html(listPrice);
        }
        return this;
    },


    /**
     * Function which will set the line item total value excluding tax and discount
     * @params : lineItemRow - row which represents the line item
     *             lineItemTotalValue - value which has line item total  (qty*listprice)
     * @return : current instance;
     */
    setLineItemTotal: function (lineItemRow, lineItemTotalValue) {
        jQuery('.productTotal', lineItemRow).text(lineItemTotalValue);
        return this;
    },

    /**
     * Function which will get the value of line item total (qty*listprice)
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getLineItemTotal: function (lineItemRow) {
        return parseFloat(this.getLineItemTotalElement(lineItemRow).text());
    },

    /**
     * Function which will get the line item total element
     * @params : lineItemRow - row which represents the line item
     * @return : jQuery element
     */
    getLineItemTotalElement: function (lineItemRow) {
        return jQuery('.productTotal', lineItemRow);
    },

    /**
     * Function which will set the discount total value for line item
     * @params : lineItemRow - row which represents the line item
     *             discountValue - discount value
     * @return : current instance;
     */
    setDiscountTotal: function (lineItemRow, discountValue) {
        jQuery('.discountTotal', lineItemRow).text(discountValue);
        return this;
    },

    /**
     * Function which will get the value of total discount
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getDiscountTotal: function (lineItemRow) {
        return parseFloat(jQuery('.discountTotal', lineItemRow).text());
    },

    /**
     * Function which will set the total after discount value
     * @params : lineItemRow - row which represents the line item
     *             totalAfterDiscountValue - total after discount value
     * @return : current instance;
     */
    setTotalAfterDiscount: function (lineItemRow, totalAfterDiscountValue) {
        jQuery('.totalAfterDiscount', lineItemRow).text(totalAfterDiscountValue);
        return this;
    },

    /**
     * Function which will get the value of total after discount
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getTotalAfterDiscount: function (lineItemRow) {
        return parseFloat(jQuery('.totalAfterDiscount', lineItemRow).text());
    },

    /**
     * Function which will set the tax total
     * @params : lineItemRow - row which represents the line item
     *             taxTotal -  tax total
     * @return : current instance;
     */
    setLineItemTaxTotal: function (lineItemRow, taxTotal) {
        jQuery('.tax_total', lineItemRow).val(taxTotal);
        jQuery('.individualTax1', lineItemRow).html('(<span style="font-style: italic">'+taxTotal+'%</span>)');
        return this;
    },

    /**
     * Function which will get the value of total tax
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getLineItemTaxTotal: function (lineItemRow) {
        return parseFloat(jQuery('.lbl_tax_total', lineItemRow).text());
    },

    /**
     * Function which will set the line item net price
     * @params : lineItemRow - row which represents the line item
     *             lineItemNetPriceValue -  line item net price value
     * @return : current instance;
     */
    setLineItemNetPrice: function (lineItemRow, lineItemNetPriceValue) {
        jQuery('.netPrice', lineItemRow).text(lineItemNetPriceValue);
        return this;
    },

    /**
     * Function which will get the value of net price
     * @params : lineItemRow - row which represents the line item
     * @return : string
     */
    getLineItemNetPrice: function (lineItemRow) {
        return parseFloat(jQuery('.netPrice', lineItemRow).text());
    },

    setNetTotal: function (netTotalValue) {
        jQuery('#netTotal').text(netTotalValue);
        return this;
    },

    getNetTotal: function () {
        return parseFloat(jQuery('#netTotal').text());
    },

    /**
     * Function to set the final discount total
     */
    setFinalDiscountTotal: function (finalDiscountValue) {
        jQuery('#discountTotal_final').text(finalDiscountValue);
        return this;
    },

    getFinalDiscountTotal: function () {
        return parseFloat(jQuery('#discountTotal_final').text());
    },

    setGroupTaxTotal: function (groupTaxTotalValue) {
        if(groupTaxTotalValue == '')
            groupTaxTotalValue = 0;
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var taxPercent = groupTaxTotalValue;
        var preTaxTotal = parseFloat($('#pre_tax_total').val());
        if(preTaxTotal==0 || isNaN(preTaxTotal)){
            var decimalseparator = $('body').data('user-decimalseparator');
            var groupingseparator = $('body').data('user-groupingseparator');
            $('#pre_tax_total').closest('.pre_tax_total').find('span').text($('#pre_tax_total').closest('.pre_tax_total').find('span').text().replace(groupingseparator, ''));
            preTaxTotal = parseFloat($('#pre_tax_total').closest('.pre_tax_total').find('span').text($('#pre_tax_total').closest('.pre_tax_total').find('span').text().replace(decimalseparator, '.')).text());
            // we don't need this because we only need replace decimalseparator = "." and groupingseparator = null
            // if(decimalseparator=='.') {
            //     preTaxTotal = parseFloat($('#pre_tax_total').closest('.pre_tax_total').find('span').text().replace(',', ''));
            // }else {
            //     var strPreTaxTotal = $('#pre_tax_total').closest('.pre_tax_total').find('span').text().replace('.', '');
            //     preTaxTotal = parseFloat(strPreTaxTotal.replace(',', ''));
            // }
        }
        // Group tax have own it tax
        var s_h_amount = $('input[name="s_h_amount"]').val();
        if(s_h_amount == undefined || s_h_amount == ''){
            s_h_amount = 0;
        }
        // pre tax total sub shipping amount to caculate for it own tax
        preTaxTotal=preTaxTotal-parseFloat(s_h_amount);
        var taxValue = parseFloat(groupTaxTotalValue) * preTaxTotal / 100;
        if(taxValue > 0){
            taxValue = taxValue.toFixed(numberOfDecimal);
            jQuery('#tax').val(taxValue);
        }else{
            jQuery('#tax').val(0);
        }

        jQuery('.running_tax').val(groupTaxTotalValue);
    },

    getGroupTaxTotal: function () {
        return parseFloat(jQuery('#tax_final').text());
    },

    getShippingAndHandling: function () {
        return parseFloat(this.getShippingAndHandlingControlElement().val());
    },

    setShippingAndHandlingTaxTotal: function () {
        var shippingTotal = jQuery('.shippingTaxTotal');
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var shippingFinalTaxTotal = 0;
        jQuery.each(shippingTotal, function (index, domElement) {
            var totalVal = parseFloat(jQuery(domElement).val());
            shippingFinalTaxTotal += totalVal;
        });
        shippingFinalTaxTotal = shippingFinalTaxTotal.toFixed(numberOfDecimal);
        jQuery('#shipping_handling_tax').text(shippingFinalTaxTotal);
        return this;
    },

    getShippingAndHandlingTaxTotal: function () {
        return parseFloat(jQuery('#shipping_handling_tax').text());
    },

    getAdjustmentValue: function () {
        return parseFloat(this.getAdjustmentTextElement().val());
    },

    isAdjustMentAddType: function () {
        var adjustmentSelectElement = this.getAdjustmentTypeElement();
        var selectionOption;
        adjustmentSelectElement.each(function () {
            if (jQuery(this).is(':checked')) {
                selectionOption = jQuery(this);
            }
        });
        if (typeof selectionOption != "undefined") {
            if (selectionOption.val() == '+') {
                return true;
            }
        }
        return false;
    },

    isAdjustMentDeductType: function () {
        var adjustmentSelectElement = this.getAdjustmentTypeElement();
        var selectionOption;
        adjustmentSelectElement.each(function () {
            if (jQuery(this).is(':checked')) {
                selectionOption = jQuery(this);
            }
        });
        if (typeof selectionOption != "undefined") {
            if (selectionOption.val() == '-') {
                return true;
            }
        }
        return false;
    },

    setGrandTotal: function (grandTotalValue) {
        jQuery('#grandTotal').text(grandTotalValue);
        return this;
    },

    getGrandTotal: function () {
        return parseFloat(jQuery('#grandTotal').text());
    },

    loadRowSequenceNumber: function () {
        if (this.rowSequenceHolder == false) {
            this.rowSequenceHolder = jQuery('.' + this.rowClass, this.getLineItemContentsContainer()).length;
        }
        return this;
    },

    getNextLineItemRowNumber: function () {
        if (this.rowSequenceHolder == false) {
            this.loadRowSequenceNumber();
        }
        return ++this.rowSequenceHolder;
    },

    /**
     * Function which will return the basic row which can be used to add new rows
     * @return jQuery object which you can use to
     */
    getBasicRow: function (baseType) {
        var lineItemTable = this.getLineItemContentsContainer();
        this.basicRow = jQuery('.lineItemCloneCopyFor' + baseType, lineItemTable);
        var newRow = this.basicRow.clone(true,true);
        var individualTax = this.isIndividualTaxMode();
        if (individualTax) {
            newRow.find('.individualTaxContainer').removeClass('hide');
        }
        return newRow.removeClass('hide lineItemCloneCopyFor' + baseType);
    },
    /**
     * Function which will return the basic row which can be used to add new rows
     * @return jQuery object which you can use to
     */
    getSubBasicRow: function (baseType) {
        var lineItemTable = this.getLineItemContentsContainer();
        this.subBasicRow = jQuery('.lineItemCloneCopyFor' + baseType, lineItemTable);
        var newRow = this.subBasicRow.clone(true,true);
        var individualTax = this.isIndividualTaxMode();
        if (individualTax) {
            newRow.find('.individualTaxContainer').removeClass('hide');
        }
        return newRow.removeClass('hide lineItemCloneCopyFor' + baseType);
    },

    registerAddingNewProductsAndServices: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        jQuery('#addProduct').off('click').on('click', function () {
            var newRow = thisInstance.getBasicRow('Product');
            newRow.addClass(thisInstance.rowClass);
            jQuery('.quoterLineItemPopup[data-module-name="Services"]', newRow).remove();
            var sequenceNumber = thisInstance.getNextLineItemRowNumber();
            newRow.appendTo(lineItemTable.find('.listItem'));
            thisInstance.checkLineItemRow();
            newRow.find('input.rowNumber').val(sequenceNumber);
            thisInstance.updateLineItemsElementWithSequenceNumber(newRow, sequenceNumber);
            thisInstance.updateLineItemElementByOrder();
            newRow.find('input.productName').addClass('autoComplete');
            thisInstance.registerLineItemAutoComplete(newRow);
            Quoter_Js.fixWidthInput(newRow);
            thisInstance.registerAutoCompleteFields(newRow);
            vtUtils.applyFieldElementsView(newRow);
        });
        jQuery('#addService').off('click').on('click', function () {
            thisInstance.registerEventAddService();
        });
        jQuery('#addSection').off('click').on('click',function(){
            var columnCount = jQuery('.lineItemContainer tr:eq(0)>th.cellItem:visible').length;
            var lineItemContainer = jQuery('.lineItemContainer tbody.listItem');
            var lastLineItemRow = lineItemContainer.find('.lineItemRow').last();
            var rownoSections = 1;
            if(lastLineItemRow.length > 0) {
                rownoSections = parseInt(lastLineItemRow.attr('rowname')) + 1;
            }
            if (jQuery('#taxtype').val() == 'group') {
                var tdSection = '<td class="fieldLabel hide" style="border-left:0;">&nbsp;</td>';
            } else {
                var tdSection = '<td class="fieldLabel" style="border-left:0;">&nbsp;</td>';
            }
            var minusColumn = 0;
            if(jQuery('#taxtype').val() != 'group') minusColumn = 1;
            var trElement = '<tr class="section" style="background-color: #f9f9f9;font-size: 12px;">' +
                '<td class="fieldLabel" colspan="' + (parseInt(columnCount) - minusColumn) + '" style = "font-size: 12px;">' +
                '<span class="section_tool" style="display: inline-block; width:100%; text-align: left; position: relative;">' +
                '<img class="section_move_icon" src="layouts/v7/skins/images/drag.png" border="0" title="Drag">&nbsp;' +
                '<i class="fa fa-trash deleteSection cursorPointer" title="Delete" style="font-size: 13px; color: black;"></i>&nbsp;'+
                '<input type="text" class="section_value" name="section0" value="Section value" data-rowno="'+rownoSections+'" />'+
                '</span>' +
                '</td>' + tdSection +
                '</tr>';
            lineItemContainer.append(trElement);
        });
    },
    registerEventAddService : function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        var newRow = thisInstance.getBasicRow('Service');
        newRow.addClass(thisInstance.rowClass);
        jQuery('.quoterLineItemPopup[data-module-name="Products"]', newRow).remove();
        var sequenceNumber = thisInstance.getNextLineItemRowNumber();
        newRow.appendTo(lineItemTable.find('.listItem'));
        thisInstance.checkLineItemRow();
        newRow.find('input.rowNumber').val(sequenceNumber);
        thisInstance.updateLineItemsElementWithSequenceNumber(newRow, sequenceNumber);
        thisInstance.updateLineItemElementByOrder();
        newRow.find('input.productName').addClass('autoComplete');
        thisInstance.registerLineItemAutoComplete(newRow);
        Quoter_Js.fixWidthInput(newRow);
        thisInstance.registerAutoCompleteFields(newRow);
        vtUtils.applyFieldElementsView(newRow);
        return newRow;
    },


    loadSubProducts: function (lineItemRow) {
        var thisIntance = this;
        var recordId = jQuery('input.selectedModuleId', lineItemRow).val();
        var subProrductParams = {
            'module': "Products",
            'action': "SubProducts",
            'record': recordId
        };
        var progressInstace = jQuery.progressIndicator();
        AppConnector.request(subProrductParams).then(
            function (data) {
                var responseData = data.result.values;
                progressInstace.hide();
                if (Object.keys(responseData).length > 0) {
                    var columnsCount = lineItemRow.find('.cellItem').length;
                    var level = lineItemRow.find('.level').val();
                    if (level < 4) {
                        level++;
                        var strDisplay = "<i class='level_display'>";
                        for (var i = 1; i < level; i++) {
                            strDisplay += "&#8594; &nbsp; ";
                        }
                        strDisplay += "</i>";
                        var rowName = lineItemRow.attr('rowName');
                        var addItem = '<tr class="lineItemAction" rowName = "' + rowName + '"><td><i class="muted addSubProduct " data-level = "' + level + '" data-parent-id = "' + recordId + '">' + strDisplay + 'Add Item</i></td>';
                        for (var i = 1; i < columnsCount; i++) {
                            if (i == (columnsCount - 1) && thisIntance.isGroupTaxMode()) {
                                addItem += '<td class="hide">&nbsp;</td>';
                            } else {
                                addItem += '<td>&nbsp;</td>';
                            }
                        }
                        addItem += '</tr>';
                        lineItemRow.after(addItem);
                        thisIntance.registerEventAddSubProductButton();
                    }
                }
            },
            function (error, err) {
                //TODO : handle the error case
            }
        );
    },
    getTaxDiv: function (taxObj, parentRow) {
        var rowNumber = jQuery('input.rowNumber', parentRow).val();
        var loopIterator = 1;
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var contentDiv = '';
        if (!jQuery.isEmptyObject(taxObj)) {
            var tax_total = 0;
            for (var taxName in taxObj) {
                var taxInfo = taxObj[taxName];
                var taxId = taxObj[taxName]['taxid'];
                var regionId = $('#region_id').val();
                if (regionId != undefined && regionId > 0) {
                    var selectedRegion = $('#region_id').find('option:selected');
                    var info = selectedRegion.data('info');
                    tax_total += parseFloat(info.taxes[taxId]['value']);
                } else {
                    tax_total += parseFloat(taxInfo.taxpercentage);
                }
                contentDiv += '<tr>' +
                    '<td><div class="textOverflowEllipsis">' + taxInfo.taxlabel + '</div></td> ' +
                    '<td>' +
                    '<input style="width: 70px;" type="text" name="' + taxName + '_percentage' + rowNumber + '" data-rule-positive=true data-rule-inventory_percentage=true  id="' + taxName + '_percentage' + rowNumber + '" value="' + taxInfo.taxpercentage + '" class="taxPercentage inputElement" data-compound-on='+taxInfo.compoundOn+' data-regions-list="'+taxInfo.regionsList+'">&nbsp;%' +
                    '</td>' +
                    '<td>' +
                    '<input readonly style="width: 70px;" type="text" name="' + taxName + '_value' + rowNumber + '" data-rule-positive=true data-rule-inventory_percentage=true  id="' + taxName + '_value' + rowNumber + '" value="" class="taxValue inputElement">' +
                    '</td>' +
                    '</tr>';
                loopIterator++;
            }
            tax_total = tax_total.toFixed(numberOfDecimal);
            parentRow.find('.tax_total').val(tax_total);
            var tax_total_display = parseFloat(tax_total);
            parentRow.find('.individualTax1').html('(<span style="font-style: italic">'+tax_total_display+'%</span>)');
        } else {
            contentDiv += '<tr>' +
                '<td>' + app.vtranslate("No Taxes") + '</td>' +
                '</tr>';
        }
        var headerDiv = '<div class="taxUI validCheck hide" id="tax_div' + rowNumber + '">' +
            '<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table' + rowNumber + '">' +
            '<tr>' +
            '<th colspan="2" id="tax_div_title' + rowNumber + '" align="left" ><b>Tax Total :&nbsp;<span class="lbl_tax_total">' + tax_total + ' %</span> = <span class="lbl_tax_value"></span></b></th>' +
            '<th ><button aria-hidden="true" data-dismiss="modal" class="close closeDiv" type="button">x</button>' +
            '</th>' +
            '</tr>';
        var taxDiv = headerDiv + contentDiv;
        taxDiv += '</table></div>';
        return jQuery(taxDiv);
    },
    mapResultsToFields: function (referenceModule, element, responseData, customValues, thisInstance, isPStemplate) {
        var thisInstance = this;
        var parentRow = jQuery(element).closest('tr.' + this.rowClass);
        var lineItemNameElment = jQuery('input.productName', parentRow);
        jQuery.each(responseData, function (id, recordData) {
            var recordId = id;
            var selectedName = recordData.name;
            var unitPrice = recordData.listprice;
            var listPriceValues = recordData.listpricevalues;
            var discount_amount = recordData.discount_amount;
            var discount_percent = recordData.discount_percent;
            var currentRowId = parentRow.attr('id');
            var arrRowNumber = currentRowId.split("row");
            var rowNumber = arrRowNumber[1];
            var taxes = recordData.taxes;
            var taxUI = thisInstance.getTaxDiv(taxes, parentRow);
            var quantity = recordData.quantity;
            jQuery('.taxDivContainer', parentRow).html(taxUI);
            jQuery.each(customValues, function (i, customValue) {
                if (i == id) {
                    jQuery.each(customValue, function (index, value) {
                        var customFields = parentRow.find('[name="' + index + rowNumber + '"],[name="' + index + rowNumber + '[]"]');
                        customFields.each(function () {
                            var customField = jQuery(this);
                            var span = customField.closest('td').find('span.custom-column-field-value');
                            if (span.length == 0) {
                                span = customField.closest('td').find('span[name^="'+index+'"]')
                            }
                            if (value) {
                                if (typeof value == 'object' && value.fieldvalue) {
                                    var td = customField.closest('td');
                                    customField.val(value.fieldvalue);
                                    td.find('[name="' + index + rowNumber + '_display"]').val(value.display_value).attr('disabled', true);
                                    td.find('span[name="' + index + rowNumber + '_display"]').text(value.display_value);
                                    td.find('span[name="' + index + rowNumber +'"]').text(value.display_value);
                                    td.find('.clearReferenceSelection').removeClass('hide');
                                    Quoter_Js.fixWidthInput(td);
                                } else {
                                    customField.val(value);
                                    if (customField.is('select')) {
                                        var selectedValues = value.split(" |##| ");
                                        customField.select2('destroy');
                                        jQuery.each(selectedValues, function (i, v) {
                                            customField.find('option[value="' + v + '"]').attr('selected', true);
                                        });
                                        vtUtils.showSelect2ElementView(customField);
                                    }else if (customField.attr('type') == 'checkbox ') {
                                        customField.attr('checked','checked');
                                        customField.prop('checked',true);
                                    }else{
                                        span.html(value);
                                    }
                                }
                            }else if(value == 0) {
                                customField.val(value);
                                span.html(value);
                            }
                        });

                    });
                    jQuery.each(thisInstance.columnSetting, function (key, setting) {
                        if (setting.productField == 'imagename' && customValue[setting.columnName] != '') {
                            var img = ' <img class="product_image" src="layouts\\v7\\modules\\Quoter\\images\\images_icon.png" data-productid = "' + id + '" width="32" height="32" style="cursor: pointer">';
                            parentRow.find('.' + setting.columnName + ' img').remove();
                            parentRow.find('.' + setting.columnName).append(img);

                        }
                    });

                }
            });
            if (referenceModule == 'Products') {
                parentRow.data('quantity-in-stock', recordData.quantityInStock);
            }
            var description = recordData.description;
            jQuery('input.selectedModuleId', parentRow).val(recordId);
            jQuery('input.lineItemType', parentRow).val(referenceModule);
            lineItemNameElment.val(selectedName);
            lineItemNameElment.attr('disabled', 'disabled');
            jQuery('input.listPrice', parentRow).val(unitPrice);
            if(jQuery('span.td_listprice', parentRow) != undefined){
                var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
                var unitPrice_parseFloat = parseFloat(unitPrice).toFixed(numberOfDecimal);
                jQuery('span.td_listprice', parentRow).html(unitPrice_parseFloat);
            }
            jQuery('input.discount_amount',parentRow).val(discount_amount);
            jQuery('input.discount_percent',parentRow).val(discount_percent);
            jQuery('input.qty',parentRow).val(quantity);
            var currencyId = jQuery("#currency_id").val();
            var listPriceValuesJson = JSON.stringify(listPriceValues);
            if (typeof listPriceValues[currencyId] != 'undefined' && isPStemplate != true) {
                thisInstance.setListPriceValue(parentRow, listPriceValues[currencyId]);
            }
            jQuery('input.listPrice', parentRow).attr('list-info', listPriceValuesJson);
            jQuery('textarea.lineItemCommentBox', parentRow).val(description);
            var selectedRegionId = thisInstance.regionElement.val();
            var taxPercentages = jQuery('.taxPercentage', parentRow);
            jQuery.each(taxPercentages,function(index1, taxDomElement){
                var taxPercentage = jQuery(taxDomElement);
                var regionsList = taxPercentage.data('regionsList');
                var value = regionsList['default'];
                if (selectedRegionId && regionsList[selectedRegionId]) {
                    value = regionsList[selectedRegionId];
                }
                taxPercentage.val(parseFloat(value));
            });
        });

        if(thisInstance.isIndividualTaxMode()) {
            thisInstance.calculateTaxForLineItem(parentRow);
        }

        if (referenceModule == 'Products') {
            this.loadSubProducts(parentRow);
        }

        // Calculate custom value by formula
        if(isPStemplate != true) {
            jQuery('.qty', parentRow).val(1);
        }
        jQuery('.qty', parentRow).focus();
        jQuery('.qty', parentRow).trigger('change');
        // this.calculateValueByFormula(parentRow, '');
        Quoter_Js.registerEventForProductImages();
    },


    // Calculate field value by formula
    calculateValueByFormula: function (currentRow, currentElementName) {
        var thisIntance = this;
        var objValue = {};
        var currentRowId = currentRow.attr('id');
        var arrRowNumber = currentRowId.split("row");
        var rowNumber = arrRowNumber[1];
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        jQuery.each(thisIntance.columnSetting, function (column, setting) {
            var value = currentRow.find('[name="' + setting.columnName + rowNumber + '"]').val();
            if (value && value != undefined && value != null && value != '' && !isNaN(value)) {
                objValue[setting.columnName] = parseFloat(value);
            } else {
                objValue[setting.columnName] = value;
            }
        });
        if (thisIntance.isGroupTaxMode()) {
            objValue['tax_total'] = 0;
        }
        //caculate value for default field
        jQuery.each(thisIntance.columnSetting, function (index, column) {
            if (column.formula) {
                var formula = column.formula;
                jQuery.each(objValue, function (fieldName, fieldValue) {
                    var pattern = '\\$' + fieldName + '\\$';
                    var regex = new RegExp(pattern, "gi");
                    if (fieldValue === '' || fieldValue == null || fieldValue == undefined) {
                        //#1052480
                        formula = formula.replace(/\s+/g, '');
                        var pattern = '\\*'+'\\$' + fieldName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '*0');
                        var pattern = '\\$' + fieldName + '\\$'+'\\*';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '0*');
                        var pattern = '\\/'+'\\$' + fieldName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '/1');
                        var pattern = '\\$' + fieldName + '\\$'+'\\/';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '0/');
                        var pattern = '\\+'+'\\$' + fieldName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '+0');
                        var pattern = '\\$' + fieldName + '\\$'+'\\+';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '0+');
                        var pattern = '\\-'+'\\$' + fieldName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '-0');
                        var pattern = '\\$' + fieldName + '\\$'+'\\-';
                        var regex = new RegExp(pattern, "gi");
                        formula = formula.replace(regex, '0-');
                    } else {
                        formula = formula.replace(regex, fieldValue);
                    }
                });
                // fix bug field disable is in the formula
                var fieldDisable = formula.match(/\$[^\$]+\$/g);
                if(fieldDisable != null){
                    $.each(fieldDisable, function (idx, val) {
                        var fieldName = val.replace(/\$/g, "");
                        var value = $('[name="'+fieldName+'"]').val();
                        if(value != undefined){
                            formula = formula.replace(val, value);
                        }else{
                            formula = formula.replace(val, 0);
                        }
                    });
                }
                formula = thisIntance.billOtherValueToFormula(formula);
                formula = thisIntance.checkIfCondition(formula);
                try {
                    var result = eval(formula);
                } catch (e) {
                    if (e instanceof SyntaxError) {
                        var result = 0;
                    }
                }
                if (!isNaN(column.formula)) {
                    var currentFieldValue = currentRow.find('[name^="' + column.columnName + '"]').val();
                    if ((currentFieldValue && currentFieldValue != undefined && currentFieldValue != 0 && currentFieldValue != '') || (currentElementName == column.columnName)) {
                        result = currentFieldValue;
                        result = parseFloat(result);
                    }
                }
                if(result != undefined){
                    result = result.toFixed(numberOfDecimal);
                    objValue[column.columnName] = result;
                }
            }
        });
        //fill value
        jQuery.each(objValue, function (name, value) {
            var fieldName = name + rowNumber;
            if (fieldName != currentElementName && name != 'comment') {
                if (currentRow.find('[name="' + fieldName + '"]').length > 0) {
                    if(thisIntance.isGroupTaxMode() && name == 'tax_total'){
                        return true;
                    }else{
                        currentRow.find('[name="' + fieldName + '"]').val(value);
                        var hideInput = currentRow.find('[name="' + fieldName + '"]');
                        var span = hideInput.closest('td').find('span.custom-column-field-value');
                        span.html(value);
                    }
                }
                if (name == "total" || name == "net_price" || name == "tax_totalamount") {
                    currentRow.find('#' + fieldName).html(thisIntance.numberFormat(value));
                    if(name == "tax_totalamount"){
                        currentRow.find('span.individualTax1_Amount').html(thisIntance.numberFormat(value));
                    }
                }
            }
        });
        //caluclate value for parent fields
        var level = parseInt(currentRow.attr('level'));
        if (level > 1) {
            var rowName = currentRow.attr("rowName");
            thisIntance.calculateFieldValueForParentProduct(rowName);
        }
    },
    checkIfCondition:function(formula){
        var thisInstance = this;
        var ifValue = 0;
        if(formula.indexOf("IF") >= 0 || formula.indexOf("if") >= 0){
            formula = jQuery('<div />').html(formula).text().toLowerCase();
            var ifContent = /if(\{[\'$\w\>\<\(\)\,+\-*\/=!.\s]*\})/g;
            ifContent = ifContent.exec(formula);
            if(ifContent){
                var allCondition = ifContent[0];
                var regExp = /\{(.*)\}/g;
                ifContent = regExp.exec(ifContent[1]);
                if(ifContent){
                    var formulaCondition = ifContent[1].split(",");
                    if(ifContent[1] && eval(formulaCondition[0]) == true ){
                        ifValue = eval(formulaCondition[1]);
                    }else if(ifContent[1] && eval(formulaCondition[0]) == false){
                        ifValue = eval(formulaCondition[2]);
                    }
                }
                formula = formula.replace(allCondition, ifValue);
                return thisInstance.checkIfCondition(formula)
            }else{
                return formula;
            }
        }else{
            return formula;
        }
    },
    calculateFieldValueForParentProduct: function (rowName) {
        var thisIntance = this;
        if (rowName != undefined) {
            var arrRowName = rowName.split("-");
            arrRowName.pop();
            arrRowName.reverse();
            jQuery.each(arrRowName, function (index, item) {
                var parentRow = jQuery("#row" + item);
                var lineItemTab = thisIntance.getLineItemContentsContainer();
                var parentRowId = parentRow.attr('id');
                var arrParentRowNumber = parentRowId.split("row");
                var parentRowNumber = arrParentRowNumber[1];
                var rowName = parentRow.attr('rowName');
                var level = parseInt(parentRow.attr('level'));
                jQuery.each(thisIntance.columnSetting, function (index, value) {
                    if (value.columnName != 'quantity') {
                        var parentFieldValue = 0;
                        lineItemTab.find(".lineItemRow[rowName=" + rowName + "]").each(function () {
                            if (jQuery(this).attr('id') != parentRowId && jQuery(this).attr('level') == level + 1) {
                                var childId = jQuery(this).attr('id');
                                var arrChildRowNumber = childId.split("row");
                                var childRowNumber = arrChildRowNumber[1];
                                var fieldValue = jQuery(this).find('[name="' + value.columnName + childRowNumber + '"]').val();
                                if(!isNaN(fieldValue)){
                                    fieldValue = parseFloat(fieldValue);
                                    parentFieldValue += fieldValue;
                                }
                            }
                        });
                        if (isNaN(parentFieldValue) || parentFieldValue == 0) {
                            return;
                        }
                        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
                        parentFieldValue = parentFieldValue.toFixed(numberOfDecimal);
                        if (value.columnName != 'tax_total') {
                            parentRow.find('[name="' + value.columnName + parentRowNumber + '"]').val(parentFieldValue);
                        }
                        if (value.columnName == 'total' || value.columnName == 'net_price') {
                            parentRow.find('#' + value.columnName + parentRowNumber).html(thisIntance.numberFormat(parentFieldValue));
                        }
                    }
                });
            });
        }
    },

    /**
     * Function which will be used to handle price book popup
     * @params :  popupImageElement - popup image element
     */
    pricebooksPopupHandler: function (popupImageElement) {
        var thisInstance = this;
        var lineItemRow = popupImageElement.closest('tr.' + this.rowClass);
        var lineItemProductOrServiceElement = lineItemRow.find('input.productName').closest('td');
        var params = {};
        params.module = 'PriceBooks';
        params.src_module = jQuery('.lineItemPopup', lineItemProductOrServiceElement).data('moduleName');
        params.src_field = jQuery('.lineItemPopup', lineItemProductOrServiceElement).data('fieldName');
        params.src_record = jQuery('input.selectedModuleId', lineItemProductOrServiceElement).val();
        params.get_url = 'getProductListPriceURL';
        params.currency_id = jQuery('#currency_id').val();

        this.showPopup(params).then(function (data) {
            var responseData = JSON.parse(data);
            for (var id in responseData) {
                thisInstance.setListPriceValue(lineItemRow, responseData[id]);
            }
            var currentFieldName = popupImageElement.prev().attr('id');
            thisInstance.calculateValueByFormula(lineItemRow, currentFieldName);
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula(currentFieldName);
        });
    },

    /**
     * Function which will calculate line item total excluding discount and tax
     * @params : lineItemRow - element which will represent lineItemRow
     */
    calculateLineItemTotal: function (lineItemRow) {
        var quantity = this.getQuantityValue(lineItemRow);
        var listPrice = this.getListPriceValue(lineItemRow);
        var lineItemTotal = parseFloat(quantity) * parseFloat(listPrice);
        this.setLineItemTotal(lineItemRow, lineItemTotal);
    },

    /**
     * Function which will calculate discount for the line item
     * @params : lineItemRow - element which will represent lineItemRow
     */
    calculateDiscountForLineItem: function (lineItemRow) {
        var discountContianer = lineItemRow.find('div.discountUI');
        var element = discountContianer.find('input.discounts').filter(':checked');
        var discountType = element.data('discountType');
        var discountRow = element.closest('tr');

        jQuery('input.discount_type', discountContianer).val(discountType);
        var rowPercentageField = jQuery('input.discount_percentage', discountContianer);
        var rowAmountField = jQuery('input.discount_amount', discountContianer);

        //intially making percentage and amount discount fields as hidden
        rowPercentageField.addClass('hide');
        rowAmountField.addClass('hide');

        var discountValue = discountRow.find('.discountVal').val();
        if (discountValue == "") {
            discountValue = 0;
        }
        if (isNaN(discountValue) || discountValue < 0) {
            discountValue = 0;
        }
        if (discountType == Inventory_Edit_Js.percentageDiscountType) {
            rowPercentageField.removeClass('hide').focus();
            //since it is percentage
            var productTotal = this.getLineItemTotal(lineItemRow);
            discountValue = (productTotal * discountValue) / 100;
        } else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
            rowAmountField.removeClass('hide').focus();
        }
        this.setDiscountTotal(lineItemRow, discountValue)
            .calculateTotalAfterDiscount(lineItemRow);
    },

    /**
     * Function which will calculate line item total after discount
     * @params : lineItemRow - element which will represent lineItemRow
     */
    calculateTotalAfterDiscount: function (lineItemRow) {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var productTotal = this.getLineItemTotal(lineItemRow);
        var discountTotal = this.getDiscountTotal(lineItemRow);
        var totalAfterDiscount = productTotal - discountTotal;
        totalAfterDiscount = totalAfterDiscount.toFixed(numberOfDecimal);
        this.setTotalAfterDiscount(lineItemRow, totalAfterDiscount);
    },

    /**
     * Function which will calculate tax for the line item total after discount
     */
    calculateTaxForLineItem: function (lineItemRow) {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var taxPercentages = jQuery('.individualTaxForm .taxPercentage', lineItemRow);
        //intially make the tax as zero
        var taxTotal = 0;
        var selectedRegion = this.getRegionElement().find('option:selected');
        var selectedRegionId = selectedRegion.val();
        jQuery.each(taxPercentages, function (index, domElement) {
            var taxPercentage = jQuery(domElement);
            var regionsList = taxPercentage.data('regionsList');
            var name = taxPercentage.attr('name');
            var individualTaxPercentage = taxPercentage.val();
            if (selectedRegionId && regionsList[selectedRegionId]) {
                individualTaxPercentage = regionsList[selectedRegionId];
            }
            jQuery('.taxDivContainer [name = "'+name+'"]').val(individualTaxPercentage);
            if (individualTaxPercentage == "") {
                individualTaxPercentage = "0";
            }
            if (!isNaN(individualTaxPercentage)) {
                var individualTaxPercentage = parseFloat(individualTaxPercentage);
            }
            taxTotal += parseFloat(individualTaxPercentage);
        });
        taxTotal = parseFloat(taxTotal.toFixed(numberOfDecimal));
        this.setLineItemTaxTotal(lineItemRow, taxTotal);
        return taxTotal;
    },

    /**
     * Function which will calculate net price for the line item
     */
    calculateLineItemNetPrice: function (lineItemRow) {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
        var netPrice = parseFloat(totalAfterDiscount);
        if (this.isIndividualTaxMode()) {
            var productTaxTotal = this.getLineItemTaxTotal(lineItemRow);
            netPrice += parseFloat(productTaxTotal)
        }
        netPrice = netPrice.toFixed(numberOfDecimal);
        this.setLineItemNetPrice(lineItemRow, netPrice);
    },

    /**
     * Function which will caliculate the total net price for all the line items
     */
    calculateNetTotal: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        var netTotalValue = 0;
        lineItemTable.find('tr.' + this.rowClass).each(function (index, domElement) {
            var lineItemRow = jQuery(domElement);
            netTotalValue += thisInstance.getLineItemNetPrice(lineItemRow);
        });
        this.setNetTotal(netTotalValue);
    },

    calculateFinalDiscount: function () {
        var thisInstance = this;
        var discountContainer = jQuery('#finalDiscountUI');
        var element = discountContainer.find('input.finalDiscounts').filter(':checked');
        var discountType = element.data('discountType');
        var discountRow = element.closest('tr');
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());

        jQuery('#discount_type_final').val(discountType);
        var rowPercentageField = discountContainer.find('input.discount_percentage_final');
        var rowAmountField = discountContainer.find('input.discount_amount_final');

        //intially making percentage and amount discount fields as hidden
        rowPercentageField.addClass('hide');
        rowAmountField.addClass('hide');

        var discountValue = discountRow.find('.discountVal').val();
        if (discountValue == "") {
            discountValue = 0;
        }
        if (isNaN(discountValue) || discountValue < 0) {
            discountValue = 0;
        }
        if (discountType == Inventory_Edit_Js.percentageDiscountType) {
            rowPercentageField.removeClass('hide').focus();
            //since it is percentage
            var productTotal = this.getNetTotal();
            discountValue = (productTotal * discountValue) / 100;
        } else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
            if (thisInstance.prevSelectedCurrencyConversionRate) {
                var conversionRate = jQuery('#conversion_rate').val();
                conversionRate = conversionRate / thisInstance.prevSelectedCurrencyConversionRate;
                discountValue = discountValue * conversionRate;
                discountRow.find('.discountVal').val(discountValue);
            }
            rowAmountField.removeClass('hide').focus();
        }
        discountValue = parseFloat(discountValue).toFixed(numberOfDecimal);
        this.setFinalDiscountTotal(discountValue);
        this.calculatePreTaxTotal();
    },

    calculateGroupTax: function () {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var groupTaxContainer = jQuery('#group_tax_div');
        var groupTaxTotal = 0;
        groupTaxContainer.find('.groupTaxPercentage').each(function (index, domElement) {
            var groupTaxPercentageElement = jQuery(domElement);
            var groupTaxRow = groupTaxPercentageElement.closest('tr');
            if (!isNaN(groupTaxPercentageElement.val())) {
                groupTaxTotal += parseFloat(groupTaxPercentageElement.val());
            }
        });
        groupTaxTotal = groupTaxTotal.toFixed(numberOfDecimal);
        this.setGroupTaxTotal(groupTaxTotal);
    },

    calculateShippingAndHandlingTaxCharges: function () {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var shippingTaxDiv = jQuery('#chargeTaxesBlock');
        var shippingTaxPercentage = shippingTaxDiv.find('.chargeTaxPercentage');
        var currentTaxTotal = 0;
        jQuery.each(shippingTaxPercentage, function (index, domElement) {
            var currentTaxPer = jQuery(domElement);
            var currentTaxPerValue = currentTaxPer.val();
            if (currentTaxPerValue == "") {
                currentTaxPerValue = "0";
            }
            if (!isNaN(currentTaxPerValue)) {
                currentTaxTotal += parseFloat(currentTaxPerValue);
            }
        });
        currentTaxTotal = currentTaxTotal.toFixed(numberOfDecimal);
        var taxPercent = currentTaxTotal;
        var s_h_amount = $('#s_h_amount').val();
        var taxValue = taxPercent * s_h_amount / 100;
        taxValue = taxValue.toFixed(2);
        jQuery('#s_h_percent').val(taxValue);
        jQuery('.running_s_h_percent').val(currentTaxTotal);
    },

    calculateGrandTotal: function () {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var netTotal = this.getNetTotal();
        var discountTotal = this.getFinalDiscountTotal();
        var shippingHandlingCharge = this.getShippingAndHandling();
        var shippingHandlingTax = this.getShippingAndHandlingTaxTotal();
        var adjustment = this.getAdjustmentValue();
        var grandTotal = parseFloat(netTotal) - parseFloat(discountTotal) + parseFloat(shippingHandlingCharge) + parseFloat(shippingHandlingTax);

        if (this.isGroupTaxMode()) {
            grandTotal += this.getGroupTaxTotal();
        }

        if (this.isAdjustMentAddType()) {
            grandTotal += parseFloat(adjustment);
        } else if (this.isAdjustMentDeductType()) {
            grandTotal -= parseFloat(adjustment);
        }

        grandTotal = grandTotal.toFixed(numberOfDecimal);
        this.setGrandTotal(grandTotal);
    },

    setShippingAndHandlingAmountForTax: function () {
        shippingAndHandlingValue = this.getShippingAndHandling();
        jQuery('#shAmountForTax').text(shippingAndHandlingValue);
        return this;
    },

    registerFinalDiscountShowEvent: function () {
        var thisInstance = this;
        jQuery('#finalDiscount').on('click', function (e) {
            var finalDiscountUI = jQuery('#finalDiscountUI');
            thisInstance.hideLineItemPopup();
            finalDiscountUI.removeClass('hide');
        });
    },

    registerFinalDiscountChangeEvent: function () {
        var lineItemResultTab = this.getLineItemResultContainer();
        var thisInstance = this;

        lineItemResultTab.on('change', '.finalDiscounts', function (e) {
            thisInstance.finalDiscountChangeActions();
        });
    },

    registerFinalDiscountValueChangeEvent: function () {
        var thisInstance = this;
        jQuery('.finalDiscountSave').on('click', function (e) {
            thisInstance.finalDiscountChangeActions();
        });
    },

    registerLineItemActionSaveEvent: function () {
        var thisInstance = this;
        var editForm = this.getForm();
        editForm.on('click', 'button[name="lineItemActionSave"]', function () {
            var match = true;
            var formError = jQuery('#EditView').data('jqv').InvalidFields;
            var closestDiv = jQuery('button[name="lineItemActionSave"]').closest('.validCheck').find('input[data-validation-engine]').not('.hide');
            jQuery(closestDiv).each(function (key, value) {
                if (jQuery.inArray(value, formError) != -1) {
                    match = false;
                }
            });
            if (!match) {
                editForm.removeData('submit');
                return false;
            } else {
                jQuery('.closeDiv').trigger('click');
                thisInstance.calculateAllRunningSubTotalValue();
                thisInstance.calculateTotalValueByFormula();
            }
        });
    },

    registerGroupTaxShowEvent: function () {
        var self = this;
        var finalTaxTriggerer = jQuery('#finalTax');
        var finalTaxUI = jQuery('#group_tax_row').find('.finalTaxUI').removeClass('hide');

        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
        var vtversion=self.getVtigerVersion();
        if(vtversion.indexOf('8.2') != -1){
            finalTaxTriggerer.popover({
                'content': finalTaxUI,
                'html': true,
                'placement': 'left',
                'animation': true,
                'title': finalTaxUI.find('.popover_title').val(),
                'trigger': 'manual',
                'template' : popOverTemplate[0].outerHTML,
                'sanitize' : false, /* to allow button / anchor */

            });
        }else{
            finalTaxTriggerer.popover({
                'content': finalTaxUI,
                'html': true,
                'placement': 'left',
                'animation': true,
                'title': finalTaxUI.find('.popover_title').val(),
                'trigger': 'manual',
                'template': popOverTemplate

            });
        }


        finalTaxTriggerer.on('shown.bs.popover', function () {
            var finalTaxForm = jQuery('#group_tax_row').find('.finalTaxUI').closest('.lineItemPopover');
            if (finalTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
                app.helper.showScroll(finalTaxTriggerer.next('.popover').find('.popover-content'), {'height': '300px'});
            }

            finalTaxForm.find('.popoverButton').on('click', function (e) {
                var validate = finalTaxForm.find('input').valid();
                if (validate) {
                    finalTaxForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
                    self.calculateAllRunningSubTotalValue();
                    self.calculateTotalValueByFormula();
                }
            });
        });

        finalTaxTriggerer.popover('show');
        var popOverId = finalTaxTriggerer.attr('aria-describedby');
        var popOverEle = jQuery('#' + popOverId);

        finalTaxTriggerer.on('click', function (e) {
            self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

            if (popOverEle.css('opacity') == '0') {
                finalTaxTriggerer.popover('show');
                popOverEle.css('opacity', 1).css('z-index', '');
            } else {
                finalTaxTriggerer.popover('hide');
                popOverEle.css('opacity', 0).css('z-index', '-1');
            }
        });
    },
    registerGroupTaxChangeEvent: function () {
        var thisInstance = this;
        var groupTaxContainer = jQuery('#group_tax_row');

        groupTaxContainer.on('focusout', '.groupTaxPercentage', function (e) {
            thisInstance.calculateGroupTax();
        });
    },

    registerShippingAndHandlingTaxShowEvent: function () {
        var self = this;
        var chargeTaxTriggerer = jQuery('#chargeTaxes');
        var chargeTaxesUI = jQuery('#chargeTaxesBlock').removeClass('hide');

        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
        var vtversion=self.getVtigerVersion();
        if(vtversion.indexOf('8.2') != -1){
            chargeTaxTriggerer.popover({
                'content': chargeTaxesUI,
                'html': true,
                'placement': 'left',
                'animation': true,
                'title': 'Discount',
                'trigger': 'manual',
                'template' : popOverTemplate[0].outerHTML,
                'sanitize' : false, /* to allow button / anchor */
            });
        }else{
            chargeTaxTriggerer.popover({
                'content': chargeTaxesUI,
                'html': true,
                'placement': 'left',
                'animation': true,
                'title': 'Discount',
                'trigger': 'manual',
                'template': popOverTemplate
            });
        }


        chargeTaxTriggerer.on('shown.bs.popover', function () {
            if (chargeTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
                app.helper.showScroll(chargeTaxTriggerer.next('.popover').find('.popover-content'), {'height': '300px'});
            }
            var chargesTaxForm = jQuery('#chargeTaxesBlock').closest('.lineItemPopover');
            chargesTaxForm.find('.popoverButton').on('click', function (e) {
                var validate = chargesTaxForm.find('input').valid();
                if (validate) {
                    chargesTaxForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
                    self.calculateAllRunningSubTotalValue();
                    self.calculateTotalValueByFormula();
                }
            });
        });

        chargeTaxTriggerer.popover('show');
        var popOverId = chargeTaxTriggerer.attr('aria-describedby');
        var popOverEle = jQuery('#' + popOverId);

        chargeTaxTriggerer.off('click').on('click', function (e) {
            self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

            if (popOverEle.css('opacity') == '0') {
                chargeTaxTriggerer.popover('show');
                popOverEle.find('.popover-title').text(popOverEle.find('.popover_title').text());
                popOverEle.css('opacity', 1).css('z-index', '');
            } else {
                chargeTaxTriggerer.popover('hide');
                popOverEle.css('opacity', 0).css('z-index', '-1');
            }
        });
    },
    registerPopoverCancelEvent: function () {
        this.getForm().off('click', '.popover .popoverCancel').on('click', '.popover .popoverCancel', function (e) {
            e.preventDefault();
            var element = jQuery(e.currentTarget);
            var popOverEle = element.closest('.popover');
            var validate = popOverEle.find('input').valid();
            if (!validate) {
                popOverEle.find('.input-error').val(0).valid();
            }
            popOverEle.css('opacity', 0).css('z-index', '-1');

        });
    },
    registerShippingAndHandlingChargesChange: function () {
        var thisInstance = this;
        this.getShippingAndHandlingControlElement().on('focusout', function (e) {
            var value = jQuery(e.currentTarget).val();
            if (value == "") {
                jQuery(e.currentTarget).val("0.00");
            }
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula();
        });
        jQuery('.shippingTaxPercentage').on('change', function () {
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula();
        });
    },
    registerAdjustmentTypeChange: function () {
        var thisInstance = this;
        this.getAdjustmentTypeElement().on('change', function (e) {
            thisInstance.calculateGrandTotal();
        });
    },

    registerAdjustmentValueChange: function () {
        var thisInstance = this;
        this.getAdjustmentTextElement().on('focusout', function (e) {
            var value = jQuery(e.currentTarget).val();
            if (value == "") {
                jQuery(e.currentTarget).val("0");
            }
            thisInstance.calculateGrandTotal();
        });
    },


    registerLineItemsPopUpCancelClickEvent: function () {
        var editForm = this.getForm();
        editForm.on('click', '.cancelLink', function () {
            jQuery('.closeDiv').trigger('click')
        })
    },

    lineItemResultActions: function () {
        this.registerLineItemActionSaveEvent();
        this.registerGroupTaxShowEvent();
        this.registerGroupTaxChangeEvent();
        this.registerShippingAndHandlingChargesChange();
        this.registerShippingAndHandlingTaxShowEvent();
    },


    lineItemRowCalculations: function (lineItemRow) {
        this.calculateLineItemTotal(lineItemRow);
        this.calculateDiscountForLineItem(lineItemRow);
        this.calculateTaxForLineItem(lineItemRow);
        this.calculateLineItemNetPrice(lineItemRow);
    },

    lineItemToTalResultCalculations: function () {
        this.calculateNetTotal();
        this.calculateFinalDiscount();
        if (this.isGroupTaxMode()) {
            this.calculateGroupTax();
        }
        //this.calculateShippingAndHandlingTaxCharges();
        this.calculateGrandTotal();
    },

    /**
     * Function which will handle the actions that need to be preformed once the qty is changed like below
     *  - calculate line item total -> discount and tax -> net price of line item -> grand total
     * @params : lineItemRow - element which will represent lineItemRow
     */
    quantityChangeActions: function (lineItemRow) {
        this.lineItemRowCalculations(lineItemRow);
        this.lineItemToTalResultCalculations();
    },

    lineItemDiscountChangeActions: function (lineItemRow) {
        this.calculateDiscountForLineItem(lineItemRow);
        this.calculateTaxForLineItem(lineItemRow);
        this.calculateLineItemNetPrice(lineItemRow);

        this.lineItemToTalResultCalculations();
    },


    /**
     * Function which will handle the actions that need to be performed once the tax percentage is change for a line item
     * @params : lineItemRow - element which will represent lineItemRow
     */

    taxPercentageChangeActions: function (lineItemRow) {
        this.calculateLineItemNetPrice(lineItemRow);
        this.calculateNetTotal();
        this.calculateFinalDiscount();
        if (this.isGroupTaxMode()) {
            this.calculateGroupTax();
        }
        //this.calculateShippingAndHandlingTaxCharges();
        this.calculateGrandTotal();
    },

    lineItemDeleteActions: function () {
        this.lineItemToTalResultCalculations();
    },

    shippingAndHandlingChargesChangeActions: function () {
        this.calculateAllRunningSubTotalValue();
        this.calculateTotalValueByFormula();
    },

    finalDiscountChangeActions: function () {
        this.calculateFinalDiscount();
        if (this.isGroupTaxMode()) {
            this.calculateGroupTax();
        }
        this.calculateGrandTotal();
    },

    /**
     * Function which will register change event for discounts radio buttons
     */
    registerDisountChangeEvent: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        lineItemTable.on('change', '.discounts', function (e) {
            var lineItemRow = jQuery(e.currentTarget).closest('tr.' + thisInstance.rowClass);
            thisInstance.lineItemDiscountChangeActions(lineItemRow);
        });
    },

    /**
     * Function which will register event for focusout of discount input fields like percentage and amount
     */
    registerDisountValueChange: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        lineItemTable.on('click', '.discountSave', function (e) {
            var element = jQuery(e.currentTarget);
            //if the element is not hidden then we need to handle the focus out
            if (!app.isHidden(element)) {
                var lineItemRow = jQuery(e.currentTarget).closest('tr.' + thisInstance.rowClass);
                thisInstance.lineItemDiscountChangeActions(lineItemRow);
            }

        });
    },

    hideLineItemPopup: function () {
        var editForm = this.getForm();
        var popUpElementContainer = jQuery('.popupTable', editForm).closest('div');
        if (popUpElementContainer.length > 0) {
            popUpElementContainer.addClass('hide');
        }
    },

    registerLineItemDiscountShowEvent: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();

        lineItemTable.on('click', '.individualDiscount', function (e) {
            var element = jQuery(e.currentTarget);
            var response = thisInstance.isProductSelected(element);
            if (response == true) {
                return;
            }
            var parentElem = jQuery(e.currentTarget).closest('td');
            thisInstance.hideLineItemPopup();
            parentElem.find('div.discountUI').removeClass('hide');
        });
    },

    /**
     * Function which will regiser events for product and service popup
     */
    registerProductAndServicePopup: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        jQuery('.quoterLineItemPopup', lineItemTable).off('click').on('click', function (e) {
            var element = jQuery(e.currentTarget);
            var referenceModule = element.data('moduleName');
            var moduleName = app.getModuleName();

            var params = {};
            params.view = element.data('popup');
            params.module = moduleName;
            params.multi_select = true;
            params.parent_id = element.data('parent-id');
            params.currency_id = jQuery('#currency_id').val();
            if (element.data('parent-id')) {
                params.parent_id = element.data('parent-id');
            } else {
                params.parent_id = 0;
            }

            var popupInstance = Vtiger_Popup_Js.getInstance();
            popupInstance.showPopup(params, 'post.QuoterLineItemPopupSelection.click');

            var postPopupHandler = function (e, data) {
                var responseData = JSON.parse(data);
                var len = Object.keys(responseData).length;
                var newResponseData = [];
                ids = [];
                if (len > 0) {
                    var keyResponse = Object.keys(responseData);
                    for (record in responseData) {
                        for (id in responseData[record]) {
                            ids.push(id);
                        }
                    }
                    // fix bug when select checkbox record in 'popup' view and click button 'add'=> mappingresult and template incorrect.
                    if (keyResponse.length ==1 && keyResponse[0]!= 0) {
                        newResponseData[0] = responseData;
                        ids =keyResponse[0];
                    }
                }
                if(newResponseData.length >0) {
                    responseData = newResponseData;
                }
                if (moduleName == 'PSTemplates') {
                    moduleName = jQuery('[name="target_module"]').val();
                }
                var newParams = {
                    module: 'Quoter',
                    action: 'ActionAjax',
                    mode: 'getCustomFieldValue',
                    targetModule: moduleName,
                    currency_id: jQuery('#currency_id').val(),
                    records: ids,
                    viewType: params.view
                };
                AppConnector.request(newParams).then(
                    function (data) {
                        var parentRow = element.closest('.lineItemRow');
                        var lineItemTab = thisInstance.getLineItemContentsContainer();
                        var rowname = parentRow.attr('rowname');
                        parentRow.siblings('[rowname="' + rowname + '"]').remove();
                        var customValue = data.result;
                        for (var i in responseData) {
                            if (i == 0) {
                                thisInstance.mapResultsToFields(referenceModule, element, responseData[i], customValue, thisInstance);
                            } else if (i >= 1 && (referenceModule == 'Products' || referenceModule == 'Services')) {
                                if (referenceModule == 'Products') {
                                    var row = jQuery('#addProduct').trigger('click');
                                } else if (referenceModule == 'Services') {
                                    var row1 = jQuery('#addService').trigger('click');
                                }
                                var newRow = jQuery('#lineItemTab .lineItemContainer tr.lineItemRow:last');
                                var targetElem = jQuery('.quoterLineItemPopup', newRow);
                                thisInstance.mapResultsToFields(referenceModule, targetElem, responseData[i], customValue, thisInstance);
                            }
                        }
                        //thisInstance.registerEventForChangeFieldValue();
                        thisInstance.calculateAllRunningSubTotalValue();
                        thisInstance.calculateTotalValueByFormula(null);
                        var parent = element.closest('tr');
                        var deletedItemInfo = parent.find('.deletedItem');
                        if (deletedItemInfo.length > 0) {
                            deletedItemInfo.remove();
                        }
                    }
                );

            };
            app.event.off('post.QuoterLineItemPopupSelection.click');
            app.event.one('post.QuoterLineItemPopupSelection.click', postPopupHandler);
        });
    },

    /**
     * Function which will regisrer price book popup
     */
    registerPriceBookPopUp: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        jQuery('.quoterPriceBookPopup', lineItemTable).off('click').on('click', function (e) {
            var element = jQuery(e.currentTarget);
            var response = thisInstance.isProductSelected(element);
            if (response == false) {
                return;
            }
            var lineItemRow = element.closest('tr.lineItemRow');
            var lineItemProductOrServiceElement = lineItemRow.find('input.productName').closest('td');
            var params = {};
            params.module = 'PriceBooks';
            params.src_module = lineItemProductOrServiceElement.find('i.quoterLineItemPopup').data('moduleName');
            params.src_field = lineItemProductOrServiceElement.find('i.quoterLineItemPopup').data('fieldName');
            params.src_record = lineItemProductOrServiceElement.find('input.selectedModuleId').val();
            params.get_url = 'getProductListPriceURL';
            params.currency_id = jQuery('#currency_id option:selected').val();
            params.view = 'Popup';
            var popupInstance = Vtiger_Popup_Js.getInstance();
            popupInstance.showPopup(params, 'post.quoterLineItemPriceBookSelect.click');
            var postPriceBookPopupHandler = function (e, data) {
                var element = jQuery(e.currentTarget);
                var responseData = JSON.parse(data);
                for (var id in responseData) {
                    thisInstance.setListPriceValue(lineItemRow, responseData[id]);
                }
                thisInstance.quantityChangeActions(lineItemRow);
                var currentFieldName = lineItemRow.prev().attr('id');
                thisInstance.calculateValueByFormula(lineItemRow, currentFieldName);
                thisInstance.calculateAllRunningSubTotalValue();
                thisInstance.calculateTotalValueByFormula(currentFieldName);
            };
            app.event.off('post.quoterLineItemPriceBookSelect.click');
            app.event.one('post.quoterLineItemPriceBookSelect.click', postPriceBookPopupHandler);
        });
    },

    /*
     * Function which will register event for quantity change (focusout event)
     */
    registerQuantityChangeEventHandler: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();

        lineItemTable.on('focusout', '.qty', function (e) {
            var element = jQuery(e.currentTarget);
            var lineItemRow = element.closest('tr.' + thisInstance.rowClass);
            var quantityInStock = lineItemRow.data('quantityInStock');
            if (typeof quantityInStock != 'undefined') {
                if (parseFloat(element.val()) > parseFloat(quantityInStock)) {
                    lineItemRow.find('.stockAlert').removeClass('hide').find('.maxQuantity').text(quantityInStock);
                } else {
                    lineItemRow.find('.stockAlert').addClass('hide');
                }
            }
            thisInstance.quantityChangeActions(lineItemRow);
        });
    },

    /**
     * Function which will register event for list price event change
     */
    registerListPriceChangeEvent: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();

        lineItemTable.on('focusout', 'input.listPrice', function (e) {
            var element = jQuery(e.currentTarget);
            var lineItemRow = thisInstance.getClosestLineItemRow(element);
            thisInstance.quantityChangeActions(lineItemRow);
        });
    },

    registerTaxPercentageChange: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();

        lineItemTable.on('focusout', '.taxPercentage', function (e) {
            var element = jQuery(e.currentTarget);
            var lineItemRow = thisInstance.getClosestLineItemRow(element);
            thisInstance.calculateTaxForLineItem(lineItemRow);
        });

        lineItemTable.on('click', '.taxSave', function (e) {
            var element = jQuery(e.currentTarget);
            var lineItemRow = thisInstance.getClosestLineItemRow(element);
            var tax_total = thisInstance.calculateTaxForLineItem(lineItemRow);
            lineItemRow.find('.tax_total').val(tax_total);
            thisInstance.calculateValueByFormula(lineItemRow);
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula();
        });
    },

    isProductSelected: function (element) {
        var parentRow = element.closest('tr');
        var productField = parentRow.find('.productName');
        var response = productField.valid();
        return response;
    },

    registerLineItemTaxShowEvent: function () {
        var self = this;
        var lineItemTable = this.getLineItemContentsContainer();
        lineItemTable.on('click', '.individualTax1', function (e) {
            var element = jQuery(e.currentTarget);
            var response = self.isProductSelected(element);
            if(response == false){
                return;
            }
            element.popover('destroy');
            var lineItemRow = self.getClosestLineItemRow(element);
            self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');
            var callBackFunction = function(element, data) {
                var currentRowId = lineItemRow.attr('id');
                var rowNumber = currentRowId.split("row")[1];
                var taxValueFields = data.find('input.taxValue');
                var total_tax_value = 0;
                var total_tax_percent = 0;
                taxValueFields.each(function(k,item){
                    var total = lineItemRow.find('input[name="total'+rowNumber+'"]').val();
                    var tr = $(item).closest('tr');
                    var taxPercentageField = tr.find('input.taxPercentage');
                    var taxpercentage = taxPercentageField.val();
                    var tax_value = taxpercentage * total / 100;
                    $(item).val(tax_value);
                    total_tax_value = total_tax_value + tax_value;
                    total_tax_percent = total_tax_percent + Number(taxpercentage);
                    taxPercentageField.on('change keyup',function(){
                        var tax_value = $(this).val() * total / 100;
                        $(item).val(tax_value);
                    });
                });
                $('span.lbl_tax_value').html(total_tax_value);
                $('span.lbl_tax_total').html(total_tax_percent+' %');
                data.find('.popoverButton').on('click', function(e){
                    //var validate = data.find('input').valid();
                    //if (validate) {
                    //
                    //}
                    element.popover('destroy');
                    self.calculateTaxForLineItem(lineItemRow);
                    self.calculateValueByFormula(lineItemRow);
                    self.calculateAllRunningSubTotalValue();
                    self.calculateTotalValueByFormula();
                });

                data.find('.popoverCancel').on('click', function(e) {
                    self.getForm().find("div[id^=qtip-]").qtip('destroy');
                    element.popover('destroy');
                });
            };

            var parentElem = jQuery(e.currentTarget).closest('td');

            var taxUI = parentElem.find('div.taxUI').clone(true, true).removeClass('hide').addClass('show');
            taxUI.find('div.individualTaxDiv').removeClass('hide').addClass('show');
            var popOverTitle = taxUI.find('.popover_title').find('.variable').text(self.getTotalAfterDiscount(lineItemRow)).closest('.popover_title').text();
            var template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
            template.addClass('individualTaxForm');
            element.popover({
                'content' : taxUI,
                'html' : true,
                'placement' : 'top',
                'animation' : true,
                'title' : popOverTitle,
                'trigger' : 'manual',
                'template' : template,
                'container' : self.lineItemsHolder

            });
            element.one('shown.bs.popover', function(e) {
                callBackFunction(element, jQuery('.individualTaxForm'));
                if(element.next('.popover').find('.popover-content').height() > 300) {
                    app.helper.showScroll(element.next('.popover').find('.popover-content'), {'height': '300px'});
                }
            });
            element.popover('toggle');
        });
    },


    registerDeleteLineItemEvent: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer().find('tbody.listItem');

        lineItemTable.on('click', '.deleteRow', function (e) {

            var element = jQuery(e.currentTarget);
            //removing the row
            var row = element.closest('tr.' + thisInstance.rowClass);
            var rowName = row.attr('rowName');
            if (rowName != undefined) {
                lineItemTable.find('[rowName=' + rowName + ']').remove();
                thisInstance.calculateFieldValueForParentProduct(rowName);
            } else {
                row.remove();
            }

            thisInstance.checkLineItemRow();
            thisInstance.lineItemDeleteActions();
            thisInstance.updateLineItemElementByOrder();
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula(null);
        });
    },

    registerTaxTypeChange: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();

        this.getTaxTypeSelectElement().on('change', function (e) {
            if (thisInstance.isIndividualTaxMode()) {
                jQuery('.tax_column').removeClass('hide');
                lineItemTable.find('.lineItemAction td:last-child').removeClass('hide');
                lineItemTable.find('.section td:last-child').removeClass('hide');
                lineItemTable.find('.running_item td.tdSpace').removeClass('hide');
                jQuery('#group_tax_row').addClass('hide');
                var colspan = $('tr.section td.fieldLabel').attr('colspan');
                $('tr.section td.fieldLabel').attr('colspan',colspan +1);
            } else {
                jQuery('#group_tax_row').removeClass('hide');
                jQuery('.tax_column').addClass('hide');
                jQuery('.tax_totalamount_column').addClass('hide');
                lineItemTable.find('.lineItemAction td:last-child').addClass('hide');
                lineItemTable.find('.section td:last-child').addClass('hide');
                lineItemTable.find('.running_item td.tdSpace').addClass('hide');
                thisInstance.calculateGroupTax();
                var colspan = $('tr.section td.fieldLabel').attr('colspan');
                $('tr.section td.fieldLabel').attr('colspan',colspan -1);
            }
            lineItemTable.find('.' + thisInstance.rowClass).each(function () {
                Quoter_Js.fixWidthInput(jQuery(this));
                thisInstance.lineItemRowCalculations(jQuery(this));
                thisInstance.calculateValueByFormula(jQuery(this), jQuery(this).find('.tax_total').attr('name'));
            });
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula();
        });
    },

    registerCurrencyChangeEvent: function () {
        var thisInstance = this;
        jQuery('#currency_id').unbind('change');
        jQuery('#currency_id').change(function (e) {
            var element = jQuery(e.currentTarget);
            var currencyId = element.val();
            var conversionRateElem = jQuery('#conversion_rate');
            var prevSelectedCurrencyConversionRate = conversionRateElem.val();
            thisInstance.prevSelectedCurrencyConversionRate = prevSelectedCurrencyConversionRate;
            var optionsSelected = element.find('option:selected');
            var conversionRate = optionsSelected.data('conversionRate');
            conversionRateElem.val(conversionRate);
            conversionRate = parseFloat(conversionRate) / parseFloat(prevSelectedCurrencyConversionRate);
            thisInstance.LineItemDirectDiscountCal(conversionRate);
            thisInstance.calculateItemValueByConversionRate(conversionRate);
            var lineItemTable = thisInstance.getLineItemContentsContainer();
            lineItemTable.find('tr.' + thisInstance.rowClass).each(function (index, domElement) {
                var lineItemRow = jQuery(domElement);
                var listPriceElement = jQuery(lineItemRow).find('[name^=listprice]');
                if (listPriceElement.attr('list-info') != '') {
                    var listPriceValues = JSON.parse(listPriceElement.attr('list-info'));
                }
                if (listPriceValues != undefined && typeof listPriceValues[currencyId] != 'undefined') {
                    thisInstance.setListPriceValue(lineItemRow, listPriceValues[currencyId]);
                } else {//nêú không thì
                    var listPriceVal = thisInstance.getListPriceValue(lineItemRow);
                    var convertedListPrice = listPriceVal * conversionRate;
                    thisInstance.setListPriceValue(lineItemRow, convertedListPrice);
                }
                thisInstance.calculateValueByFormula(lineItemRow);
            });
            thisInstance.calculateTotalValueByConversionRate(conversionRate);
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula();
            jQuery('#prev_selected_currency_id').val(optionsSelected.val())
        });
    },
    calculateItemValueByConversionRate: function (conversionRate) {
        var thisInstance = this;
        var lineItemRows = jQuery('.lineItemRow');
        var currencySymbol = jQuery('#currency_id option:selected').data('currency-symbol');
        jQuery(lineItemRows).each(function (index) {
            jQuery.each(thisInstance.columnSetting, function (i, v) {
                var lineItemRow = jQuery(lineItemRows[index]);
                var isCurrency = thisInstance.isCurrenyField(v.columnName, lineItemRow);
                var fieldEle = lineItemRow.find('[name^="' + v.columnName + '"]');
                if (isCurrency == true) {
                    fieldEle.prev().text(currencySymbol);
                }
                if (isCurrency == true && v.formula == '') {
                    var fieldValue = fieldEle.val();
                    if (fieldValue != '' && !isNaN(fieldValue)) {
                        var newFieldValue = conversionRate * parseFloat(fieldValue);
                        fieldEle.val(newFieldValue);
                    }
                }
            });
        });
        //set currency symbol for row base
        jQuery('.lineItemCloneCopyForProduct,.lineItemCloneCopyForService').each(function (index) {
            var linneItemBase = jQuery(this);
            jQuery.each(thisInstance.columnSetting, function (i, v) {
                var isCurrency = thisInstance.isCurrenyField(v.columnName, linneItemBase);
                var fieldEle = linneItemBase.find('[name^="' + v.columnName + '"]');
                if (isCurrency == true) {
                    fieldEle.prev().text(currencySymbol);
                }
            });
        });
    },
    calculateTotalValueByConversionRate: function (conversionRate) {
        var thisInstance = this;
        jQuery.each(thisInstance.totalSetting, function (fieldName, setting) {
            var newKey = fieldName.split('__');
            fieldName = newKey[1];
            var regex = /^ctf_/gi;
            if (setting.fieldFormula == '') {
                if (fieldName == 'adjustment' ||
                    fieldName == 'discount_amount' ||
                    fieldName == 's_h_amount' ||
                    fieldName == 'paid' ||
                    regex.exec(fieldName)) {
                    var currentValue = jQuery('#' + fieldName).val();
                    if (currentValue == '') {
                        currentValue = 0;
                    }
                    var newValue = currentValue * conversionRate;
                    jQuery('#' + fieldName).val(newValue);
                }
            }
        });
    },
    isCurrenyField: function (fieldName, lineItemRow) {
        var regex = /^cf_/gi;
        if (regex.exec(fieldName)) {
            var fieldEle = lineItemRow.find('[name^="' + fieldName + '"]');
            var fieldInfo = fieldEle.data('fieldinfo');
            if (fieldInfo != undefined && fieldInfo.type == 'currency') {
                return true;
            }
        }
        return false;
    },
    AdjustmentShippingResultCalculation: function (conversionRate) {
        //Adjustment
        var thisInstance = this;
        var adjustmentElement = thisInstance.getAdjustmentTextElement();
        var newAdjustment = jQuery(adjustmentElement).val() * conversionRate;
        jQuery(adjustmentElement).val(newAdjustment);

        //Shipping & handling
        var shippingHandlingElement = thisInstance.getShippingAndHandlingControlElement();
        var resultVal = jQuery(shippingHandlingElement).val() * conversionRate;
        jQuery(shippingHandlingElement).val(resultVal);
        jQuery(shippingHandlingElement).trigger('focusout');
    },

    LineItemDirectDiscountCal: function (conversionRate) {
        var lineItemRows = jQuery('.lineItemRow');
        jQuery(lineItemRows).each(function (index) {
            var lineItemRow = jQuery(lineItemRows[index]);
            var discountValue = lineItemRow.find('.discount_amount').val();
            if (isNaN(discountValue)) {
                discountValue = 0;
            }
            var newdiscountValue = conversionRate * discountValue;
            lineItemRow.find('.discount_amount').val(newdiscountValue);
        });
    },

    lineItemActions: function () {
        var lineItemTable = this.getLineItemContentsContainer();

        //this.registerDisountChangeEvent();
        //this.registerDisountValueChange();
        //this.registerLineItemDiscountShowEvent();

        this.registerLineItemAutoComplete();
        this.registerClearLineItemSelection();

        this.registerProductAndServicePopup();
        this.checkLineItemRow();
        this.registerPriceBookPopUp();

        //this.registerQuantityChangeEventHandler();
        //this.registerListPriceChangeEvent();

        // this.registerTaxPercentageChange();
        this.registerLineItemTaxShowEvent();

        //this.registerDeleteLineItemEvent();
        this.registerTaxTypeChange();
        this.registerCurrencyChangeEvent();


        lineItemTable.on('click', '.closeDiv', function (e) {
            $('.popover .popoverCancel').trigger('click');
        });

        lineItemTable.on('click', '.clearComment', function (e) {
            var elem = jQuery(e.currentTarget);
            var parentElem = elem.closest('div');
            var comment = jQuery('.lineItemCommentBox', parentElem).val('');
        });

    },

    /***
     * Function which will update the line item row elements with the sequence number
     * @params : lineItemRow - tr line item row for which the sequence need to be updated
     *             currentSequenceNUmber - existing sequence number that the elments is having
     *             expectedSequenceNumber - sequence number to which it has to update
     *
     * @return : row element after changes
     */
    updateLineItemsElementWithSequenceNumber: function (lineItemRow, expectedSequenceNumber, currentSequenceNumber) {
        var thisInstance = this;
        if (typeof currentSequenceNumber == 'undefined') {
            //by default there will zero current sequence number
            currentSequenceNumber = 0;
        }
        var idFields = ['productName', 'subproduct_ids', 'hdnProductId',
            'comment', 'quantity', 'qty', 'listprice', 'discount_type', 'discount_percent', 'listprice',
            'discount_amount', 'lineItemType', 'searchIcon', 'net_price', 'total', 'parentProductId', 'level'];
        if(jQuery.isEmptyObject(thisInstance.columnSetting)) {
            var jsonSetting = $('.input_settings').text();
            thisInstance.columnSetting = JSON.parse(jsonSetting);
        }
        jQuery.each(thisInstance.columnSetting, function (index, value) {
            var regex = /cf_/gi;
            if (regex.exec(value.columnName)) {
                idFields.push(value.columnName);
            }
            if (idFields.indexOf(value.columnName) == -1){
                idFields.push(value.columnName);
            }
        });
        var classFields = new Array('taxPercentage');
        //To handle variable tax ids
        for(var classIndex in classFields) {
            var className = classFields[classIndex];
            jQuery('.'+className,lineItemRow).each(function(index, domElement){
                var idString = domElement.id
                //remove last character which will be the row number
                idFields.push(idString.slice(0,(idString.length-1)));
            });
        }
        var expectedRowId = 'row' + expectedSequenceNumber;
        jQuery.each(idFields, function (index, elementId) {
            var actualElementId = elementId + currentSequenceNumber;
            var expectedElementId = elementId + expectedSequenceNumber;
            lineItemRow.find('#' + actualElementId).attr('id', expectedElementId)
                .filter('[name="' + actualElementId + '"]').attr('name', expectedElementId);
            lineItemRow.find('select[name="' + actualElementId + '"]').attr('name', expectedElementId);
            lineItemRow.find('select[name="' + actualElementId + '[]"]').attr('name', expectedElementId + '[]');
            lineItemRow.find('[name="' + actualElementId + '"]').attr('name', expectedElementId);
            lineItemRow.find('#' + actualElementId).attr('id', expectedElementId);
            if (lineItemRow.find('#' + actualElementId + '_display').length > 0) {
                lineItemRow.find('#' + actualElementId + '_display').attr('id', expectedElementId + '_display').filter('[name="' + actualElementId + '_display"]').attr('name', expectedElementId + '_display');
            }
        });
        lineItemRow.find('.rowNumber').val(expectedSequenceNumber);

        if (lineItemRow.is('tbody')) {
            return lineItemRow.find('.lineItemRow').attr('id', expectedRowId);
        } else {
            return lineItemRow.attr('id', expectedRowId);
        }
    },


    updateLineItemElementByOrder: function () {
        var thisInstance = this;
        var lineItemContentsContainer = this.getLineItemContentsContainer();
        var lineItems = jQuery('tr.' + this.rowClass, lineItemContentsContainer);
        lineItems.each(function (index, domElement) {
            var lineItemRow = jQuery(domElement);
            var expectedRowIndex = (index + 1);
            var expectedRowId = 'row' + expectedRowIndex;
            var actualRowId = lineItemRow.attr('id');
            if (expectedRowId != actualRowId) {
                var actualIdComponents = actualRowId.split('row');
                thisInstance.updateLineItemsElementWithSequenceNumber(lineItemRow, expectedRowIndex, actualIdComponents[1]);
            }
            thisInstance.updateRowName(index, lineItems);
            if (index > 0) {
                var preElement = jQuery(lineItems[index - 1]);
                var preLevel = preElement.attr('level');
                var currentLevel = lineItemRow.attr('level');
                if (preLevel < currentLevel) {
                    lineItemRow.find('.parentId').val(preElement.find('.selectedModuleId').val());
                } else if (preLevel == currentLevel) {
                    lineItemRow.find('.parentId').val(preElement.find('.parentId').val());
                }
            }

        });
        var tableItemContainer = lineItemContentsContainer.find('tbody.listItem');
        tableItemContainer.find('.running_item').each(function () {
            var index = lineItemContentsContainer.find('tbody.listItem > tr').index(jQuery(this));
            if (index > 0) {
                var preRowItem = lineItemContentsContainer.find('tbody.listItem > tr:lt(' + index + ')').filter('.lineItemRow').last();
                if (preRowItem.length > 0) {
                    var rowId = preRowItem.attr('id');
                    var arrRowId = rowId.split("row");
                    var rowNo = arrRowId[1];
                    jQuery(this).data('running-item-rowno', rowNo);
                    jQuery(this).find('.running_item_name').attr('name', 'running_item_name' + rowNo + '[]');
                    jQuery(this).find('.running_item_value').attr('name', 'running_item_value' + rowNo + '[]');
                } else {
                    jQuery(this).data('running-item-rowno', 0);
                    jQuery(this).find('.running_item_name').attr('name', 'running_item_name0[]');
                    jQuery(this).find('.running_item_value').attr('name', 'running_item_value0[]');
                }
            }
        });
    },
    updateRowName: function (currentIndex, elements) {
        var thisInstance = this;
        var currentItem = jQuery(elements[currentIndex]);
        var preItem = jQuery(elements[currentIndex - 1]);
        var expectedRowIndex = currentIndex + 1;
        var currentLevel = parseInt(currentItem.attr('level'));
        if (currentLevel > 1) {
            var preRowName = preItem.attr('rowName');
            if (preRowName != undefined) {
                var arrRowName = preRowName.split("-");
                arrRowName = arrRowName.slice(0, currentLevel - 1);
                arrRowName.push(expectedRowIndex);
                var rowName = arrRowName.join("-");
                currentItem.attr("rowName", rowName);
            } else {
                currentItem.attr("rowName", expectedRowIndex);
                var arrRowName = [expectedRowIndex];
            }
        } else {
            currentItem.attr("rowName", expectedRowIndex);
            var arrRowName = [expectedRowIndex];
        }
        if (currentItem.next().hasClass('lineItemAction')) {
            thisInstance.updateLineItemActionName(currentItem.next(), arrRowName);
        }

    },
    updateLineItemActionName: function (actionItem, preArrRowName) {
        var thisInstance = this;
        var level = actionItem.find('.addSubProduct').data('level');
        var arrRowName = preArrRowName.slice(0, level - 1);
        var rowName = arrRowName.join("-");
        actionItem.attr('rowName', rowName);
        if (actionItem.next().hasClass('lineItemAction')) {
            thisInstance.updateLineItemActionName(actionItem.next(), arrRowName);
        }
    },
    saveProductCount: function () {
        jQuery('#totalProductCount').val(jQuery('tr.' + this.rowClass, this.getLineItemContentsContainer()).length);
    },

    saveSubTotalValue: function () {
        jQuery('#subtotal').val(this.getNetTotal());
    },

    saveTotalValue: function () {
        jQuery('#total').val(this.getGrandTotal());
    },
    makeLineItemsSortable: function () {
        var thisInstance = this;
        var lineItemTable = jQuery('#lineItemTab .lineItemContainer .listItem');
        lineItemTable
            .on('mousedown', 'tr.lineItemRow', function (e) {
                var rowName = jQuery(this).attr('rowName');
                jQuery("[rowName=" + rowName + "]").addClass('selected');
            })
            .on('mouseup', 'tr.lineItemRow', function (e) {
                jQuery("tr.selected").removeClass('selected');
            })
            .sortable({
                items: 'tr.lineItemRow, tr.section, tr.running_item',
                delay: 150,
                revert: 0,
                helper: function (e, item) {
                    var helper = jQuery('<tr/>');
                    if (!item.hasClass('selected')) {
                        item.addClass('selected').siblings().removeClass('selected');
                    }
                    var elements = item.parent().children('.selected').clone(true,true);
                    thisInstance.itemMove = item.data('multidrag', elements).siblings('.selected');
                    thisInstance.itemMove.hide();
                    return helper.append(elements);
                },
                update: function (e, ui) {
                    //var elements = ui.item.data('multidrag');
                    var currentItem = ui.item;
                    var currentLevel = parseInt(currentItem.attr('level'));
                    var currentRowName = currentItem.attr('rowName');
                    var preItem = currentItem.prev();
                    var preLevel = parseInt(preItem.attr('level'));
                    var nextItem = currentItem.next();
                    var nextLevel = parseInt(nextItem.attr('level'));
                    if (currentItem.is('.lineItemRow')) {
                        var currentLevel = currentItem.attr('level');
                        var currentRowName = currentItem.attr('rowName');
                        var preLevel = preItem.attr('level');
                        var nextLevel = nextItem.attr('level');
                        if (currentLevel == 1 && lineItemTable.find("tr.lineItemRow[rowName=" + currentRowName + "]").length == 1) {
                            lineItemTable.find('.selected').removeClass('selected');
                            if (nextItem.length > 0) {
                                var currentRowId = currentItem.attr('id');
                                var rowNo = currentRowId.replace("row", "");
                                if (nextItem.hasClass('lineItemRow')) {
                                    var nextLevel = nextItem.attr('level');
                                    var nextRowname = nextItem.attr('rowName');
                                    var arrNextRowname = nextRowname.split('-');
                                    arrNextRowname.pop();
                                    arrNextRowname.push(rowNo);
                                    var rowname = arrNextRowname.join("-");
                                    var parentId = nextItem.find("input.parentId").val();
                                } else {
                                    var nextLevel = nextItem.find('.addSubProduct').data('level');
                                    var rowname = nextItem.attr('rowName');
                                    var parentId = nextItem.find('.addSubProduct').data('parent-id');
                                }
                                jQuery('#' + currentRowId).attr('level', nextLevel);
                                jQuery('#' + currentRowId).find('.level').val(nextLevel);
                                jQuery('#' + currentRowId).attr('rowName', rowname);
                                jQuery('#' + currentRowId).find('input.parentId').val(parentId);
                                jQuery('#' + currentRowId).find('i.quoterLineItemPopup').attr('data-parent-id', parentId);

                                for (var i = 1; i < nextLevel; i++) {
                                    jQuery('#' + currentRowId).find('td:first').prepend('&#8594; &nbsp; ');
                                }
                            }
                            thisInstance.updateLineItemElementByOrder();
                            thisInstance.updateParentValueForAllItems();
                        } else if (preLevel == currentLevel || parseInt(preLevel) + 1 == parseInt(currentLevel) || (preLevel == undefined && currentLevel == 1 )) {
                            if (preLevel == currentLevel) {
                                thisInstance.targetItems = [];
                                var preRowName = preItem.attr('rowName');
                                var sbElement = preItem.siblings("[rowName=" + preRowName + "]");
                                sbElement.each(function () {
                                    thisInstance.targetItems.push(jQuery(this));
                                });
                                thisInstance.changeLineItem(preItem, thisInstance.targetItems);
                            }
                            currentItem.after(currentItem.data('multidrag')).remove();
                            lineItemTable.find('.selected').removeClass('selected');
                            thisInstance.updateLineItemElementByOrder();
                            thisInstance.updateParentValueForAllItems();
                        } else {
                            lineItemTable.sortable('cancel');
                            lineItemTable.find('.selected').removeClass('selected');
                            currentItem.after(currentItem.data('multidrag')).remove();
                        }
                        lineItemTable.find(">tr:hidden").remove();
                        thisInstance.calculateAllRunningSubTotalValue();
                        thisInstance.registerEventAddSubProductButton();

                    } else if (currentItem.is('.section')) {
                        if (nextItem.is('.lineItemAction') || nextItem.length == 0 || nextItem.is('.section') || preItem.is('section')) {
                            lineItemTable.sortable('cancel');
                        } else if (nextItem.is('.lineItemRow')) {
                            var nextRowId = nextItem.attr('id');
                            var arrNextRowId = nextRowId.split("row");
                            var nextRowSequence = arrNextRowId[1];
                            var sectionInput = currentItem.find('.section_value');
                            sectionInput.attr('name', 'section' + nextRowSequence);
                            sectionInput.data('rowno', nextRowSequence);
                            thisInstance.calculateTotalValueByFormula(null);
                        }
                    } else if (currentItem.is('.running_item')) {
                        if ((nextItem.length > 0 && nextItem.is('.lineItemRow') && nextLevel == 1) || nextItem.length == 0 || nextItem.is('.running_item') || nextItem.is('.section')) {
                            if (nextItem.length == 0) {
                                var rowNo = lineItemTable.find('.lineItemRow').length;
                            } else if (nextItem.is('.running_item')) {
                                var rowNo = nextItem.data('running-item-rowno');
                            } else if (nextItem.is('.section')) {
                                var sectionName = jQuery('.section_value',nextItem).attr('name');
                                if(sectionName!= undefined && sectionName != ''){
                                    var arrRowNo = sectionName.split("section");
                                    var rowNo = arrRowNo[1] -1;
                                }
                            }else{
                                var nextRowId = nextItem.attr('id');
                                var arrNextRowId = nextRowId.split("row");
                                var nextRowNo = arrNextRowId[1];
                                var rowNo = nextRowNo - 1;
                            }
                            currentItem.data('running-item-rowno', rowNo);
                            currentItem.find('.running_item_name').attr('name', 'running_item_name' + rowNo + '[]');
                            currentItem.find('.running_item_value').attr('name', 'running_item_value' + rowNo + '[]');
                            thisInstance.calculateAllRunningSubTotalValue();
                        } else {
                            lineItemTable.sortable('cancel');
                        }
                        lineItemTable.find('.running_item').removeClass('selected');
                    }
                }
            });
    },
    calculateAllRunningSubTotalValue: function () {
        var thisInstance = this;
        var lineItemTable = jQuery('#lineItemTab .lineItemContainer .listItem');
        jQuery('.addRunningSubTotal .runningSubTotalItem').each(function () {
            var runningItemName = jQuery(this).data('item-name');
            lineItemTable.find('[data-running-item-name="' + runningItemName + '"]').each(function () {
                thisInstance.addClassForRunningItem(jQuery(this), runningItemName);
                var listItem = lineItemTable.find('tr.isNeedCalculate');
                thisInstance.calculateRunningSubTotalValueByFormula(listItem, jQuery(this));
                lineItemTable.find('tr').removeClass('isNeedCalculate');
            });
        });
    },
    addClassForRunningItem: function (currentItemRunning, runningItemName) {
        var prevItem = currentItemRunning.prev();
        if (prevItem.length == 0) return;
        if (prevItem.is('.lineItemRow')) {
            prevItem.addClass('isNeedCalculate');
            this.addClassForRunningItem(prevItem, runningItemName);
        } else if (prevItem.data('running-item-name') == runningItemName) {

        } else {
            this.addClassForRunningItem(prevItem, runningItemName);
        }

    },
    calculateRunningSubTotalValueByFormula: function (listItem, runningItem) {
        var thisIntance = this;
        if (thisIntance.isGroupTaxMode()) {
            thisIntance.calculateGroupTax();
        } else {
            jQuery('#tax').val(0);
            jQuery('.running_tax').val(0);
        }
        thisIntance.calculateShippingAndHandlingTaxCharges();

        var totalSetting = this.totalSetting;
        jQuery.each(totalSetting, function (index, value) {
            var newKey = index.split('__');
            index = newKey[1];
            var totalVal = jQuery('#' + index).val();
            runningItem.find('.running_' + index).val(totalVal);
        });

        jQuery.each(totalSetting, function (index, value) {
            var newKey = index.split('__');
            index = newKey[1];
            if (value.fieldFormula) {
                var formula = value.fieldFormula;
                formula = thisIntance.billRunningItemValueToFormula(formula, listItem, runningItem);
                formula = thisIntance.checkIfCondition(formula);
                var fieldValue = 0;
                try {
                    var fieldValue = eval(formula);
                } catch (e) {
                    if (e instanceof SyntaxError) {
                        fieldValue = 0;
                    }
                }
                var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
                var fieldValue = parseFloat(fieldValue).toFixed(numberOfDecimal);
                if (runningItem == undefined) {
                    jQuery('#' + index).val(fieldValue);
                    jQuery('.' + index + " span").html(thisIntance.numberFormat(fieldValue));
                } else {
                    jQuery('.running_' + index, runningItem).val(fieldValue);
                }
            }
        });
        if (runningItem != undefined) {
            var runningItemName = jQuery('.running_item_name', runningItem).val();
            var runningItemValue = jQuery('.running_' + runningItemName, runningItem).val();
            jQuery('.running_item_value', runningItem).val(runningItemValue);
            jQuery('.running_item_display', runningItem).text(thisIntance.numberFormat(runningItemValue));
        }
    },
    billRunningItemValueToFormula: function (formula, listItem, runningItem) {
        var thisIntance = this;
        var regExp = /SUM(\(.*\))(?=\*|\+|-|\/)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        if(formula.indexOf("IF") >= 0 || formula.indexOf("if") >= 0){
            regExp = /SUM(\([$\w]*\))(?=\*|\+|-|\/|)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        }
        var matches = regExp.exec(formula);
        if (matches) {
            var subRegExp = /SUM\(([^)]+)\)/;
            var subMatches = subRegExp.exec(matches[0]);
            var total = 0;
            listItem.each(function (i, e) {
                var subFormula = subMatches[1];
                var currentItem = jQuery(this);
                if (jQuery(this).attr('level') == 1) {
                    jQuery.each(thisIntance.columnSetting, function (key, val) {
                        var fieldValue = currentItem.find('[name^="' + val.columnName + '"]').val();
                        if (fieldValue == undefined || !fieldValue) {
                            fieldValue = 0;
                        }
                        fieldValue = parseFloat(fieldValue);
                        var pattern = '\\$' + val.columnName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        subFormula = subFormula.replace(regex, fieldValue);
                    });
                    subFormula = thisIntance.billOtherValueToFormula(subFormula);
                    try {
                        var result = eval(subFormula);
                    } catch (e) {
                        if (e instanceof SyntaxError) {
                            result = 0;
                        }
                    }
                    total += result;
                }
            });
            formula = formula.replace(matches[0], total);
            return thisIntance.billItemValueToFormula(formula);
        } else {
            return thisIntance.billTotalValueToFormula(formula, runningItem);
        }
    },
    updateParentValueForAllItems: function () {
        var thisInstance = this;
        var lineItemTab = thisInstance.getLineItemContentsContainer();
        lineItemTab.find("tr." + thisInstance.rowClass).each(function () {
            if (jQuery(this).next().hasClass('lineItemAction')) {
                var rowName = jQuery(this).attr('rowName');
                thisInstance.calculateFieldValueForParentProduct(rowName);
            }
        });
    },

    changeLineItem: function (currentItem, items) {
        items.reverse();
        jQuery.each(items, function (index, value) {
            value.insertAfter(currentItem);
            value.show();
        });
    },

    registerValidation: function () {
        var self = this;
        var editViewForm = this.getForm();
        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {
                var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                app.event.trigger(e);
                self.updateSectionsName();
                if (e.isDefaultPrevented()) {
                    return false;
                }
                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled', true);
                self.calculateAllRunningSubTotalValue();
                self.calculateTotalValueByFormula(null);
                return true;
            }
        });
    },
    updateSectionsName: function () {
        var lineItemContainer = this.getLineItemContentsContainer();
        lineItemContainer.find('tr.section').each(function () {
            var nextRow = jQuery(this).next();
            if (nextRow.is('.lineItemRow')) {
                var nextRowId = nextRow.attr('id');
                var arrNextRowNumber = nextRowId.split("row");
                var nextRowNumber = arrNextRowNumber[1];
                var sectionInput = jQuery(this).find('.section_value');
                sectionInput.attr('name', 'section' + nextRowNumber);
            }
        });
    },
    /**
     * Function which will register event for Reference Fields Selection
     */
    registerReferenceSelectionEvent: function (container) {
        var thisInstance = this;

        jQuery('input[name="contact_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            thisInstance.referenceSelectionEventHandler(data, container);
        });
    },

    /**
     * Reference Fields Selection Event Handler
     */
    referenceSelectionEventHandler: function (data, container) {
        var thisInstance = this;
        var message = app.vtranslate('OVERWRITE_EXISTING_MSG1') + app.vtranslate('SINGLE_' + data['source_module']) + ' (' + data['selectedName'] + ') ' + app.vtranslate('OVERWRITE_EXISTING_MSG2');
        Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
            function (e) {
                thisInstance.copyAddressDetails(data, container);
            },
            function (error, err) {
            });
    },

    /**
     * Function which will copy the address details
     */
    copyAddressDetails: function (data, container, addressMap) {
        var thisInstance = this;
        var sourceModule = data['source_module'];
        var noAddress = true;
        var errorMsg;

        thisInstance.getRecordDetails(data).then(
            function (data) {
                var response = data['result'];
                if (typeof addressMap != "undefined") {
                    var result = response['data'];
                    for (var key in addressMap) {
                        if (result[addressMap[key]] != "") {
                            noAddress = false;
                            break;
                        }
                    }
                    if (noAddress) {
                        if (sourceModule == "Accounts") {
                            errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
                        } else if (sourceModule == "Contacts") {
                            errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
                        }
                        Vtiger_Helper_Js.showPnotify(app.vtranslate(errorMsg));
                    } else {
                        thisInstance.mapAddressDetails(addressMap, result, container);
                    }
                } else {
                    thisInstance.mapAddressDetails(thisInstance.addressFieldsMapping[sourceModule], response['data'], container);
                    if (sourceModule == "Accounts") {
                        container.find('.accountAddress').attr('checked', 'checked');
                    } else if (sourceModule == "Contacts") {
                        container.find('.contactAddress').attr('checked', 'checked');
                    }
                }
            },
            function (error, err) {

            });
    },

    /**
     * Function which will copy the address details of the selected record
     */
    mapAddressDetails: function (addressDetails, result, container) {
        for (var key in addressDetails) {
            container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
            container.find('[name="' + key + '"]').trigger('change');
        }
    },

    registerLineItemAutoComplete: function (container) {
        var thisInstance = this;
        if (typeof container == 'undefined') {
            container = thisInstance.getLineItemContentsContainer();
        }
        container.find('input.autoComplete').autocomplete({
            'minLength': '3',
            'source': function (request, response) {
                //element will be array of dom elements
                //here this refers to auto complete instance
                var inputElement = jQuery(this.element[0]);
                var tdElement = inputElement.closest('td');
                var searchValue = request.term;
                var params = {};
                var currentModule = jQuery("#EditView").find('input[name="module"]').val();
                if(currentModule == 'PSTemplates') {
                    currentModule=jQuery("#EditView").find('select[name="target_module"]').val();
                }
                var searchModule = tdElement.find('.quoterLineItemPopup').data('moduleName');

                if (typeof ProductServiceLookup_Js !== 'undefined') {
                    params.search_module = searchModule;
                    params.search_value = searchValue;
                    params.parent_module = currentModule;
                } else {
                    params.search_module = searchModule;
                    params.search_value = searchValue;
                }
                thisInstance.searchModuleNames(params).then(function (data) {
                    var reponseDataList = [];
                    var serverDataFormat = data.result;
                    if (serverDataFormat.length <= 0) {
                        serverDataFormat = new Array({
                            'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
                            'type': 'no results'
                        });
                    }
                    for (var id in serverDataFormat) {
                        var responseData = serverDataFormat[id];
                        reponseDataList.push(responseData);
                    }
                    response(reponseDataList);
                });
            },
            'select': function (event, ui) {
                var selectedItemData = ui.item;
                //To stop selection if no results is selected
                if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
                    return false;
                }
                var element = jQuery(this);
                element.attr('disabled', 'disabled');
                var trElement = element.closest('tr');
                var selectedModule = trElement.find('.quoterLineItemPopup').data('moduleName');
                var popupElement = trElement.find('.quoterLineItemPopup');
                var paramsUrl = {
                    module: 'Quoter',
                    action: 'GetTaxes',
                    record: selectedItemData.id,
                    current_module : app.getModuleName(),
                    viewType: popupElement.data('popup'),
                    customSetting: thisInstance.columnSetting,
                    currency_id: jQuery('#currency_id').val()
                };
                AppConnector.request(paramsUrl).then(
                    function (data) {
                        var responseData = data.result;
                        for (var id in data) {
                            if (typeof data[id] == "object") {
                                var rowname = trElement.attr('rowname');
                                trElement.siblings('[rowname="' + rowname + '"]').remove();
                                var recordData = data[id];
                                thisInstance.mapResultsToFields(selectedModule, popupElement, recordData.defaultValue, recordData.customValue, thisInstance);
                                /*thisInstance.registerEventForChangeFieldValue();
                                thisInstance.calculateAllRunningSubTotalValue();
                                thisInstance.calculateTotalValueByFormula(null);*/
                            }
                        }
                    },
                    function (error, err) {

                    }
                );
            },
            'change': function (event, ui) {
                var element = jQuery(this);
                //if you dont have disabled attribute means the user didnt select the item
                if (element.attr('disabled') == undefined) {
                    element.closest('td').find('.clearLineItem').trigger('click');
                }
            }
        });
    },

    registerClearLineItemSelection: function () {
        var thisInstance = this;
        var lineItemTable = this.getLineItemContentsContainer();
        jQuery('.clearLineItemNew').off('click');
        lineItemTable.on('click', '.clearLineItemNew', function (e) {
            var elem = jQuery(e.currentTarget);
            var parentElem = elem.closest('td');
            thisInstance.clearLineItemDetails(parentElem);
            parentElem.find('input.productName').removeAttr('disabled').val('');
            //thisInstance.registerEventForChangeFieldValue();
            thisInstance.calculateAllRunningSubTotalValue();
            thisInstance.calculateTotalValueByFormula(null);
            e.preventDefault();
        });
    },

    clearLineItemDetails: function (parentElem) {
        var thisInstance = this;
        var lineItemRow = parentElem.closest('tr.' + thisInstance.rowClass);
        jQuery.each(thisInstance.columnSetting, function (index, objColumnField) {
            jQuery('[name^="' + objColumnField.columnName + '"]', lineItemRow).val('');
            jQuery('select[name^="' + objColumnField.columnName + '"]', lineItemRow).select2('val', '');
        });
        jQuery('.total', lineItemRow).html('0');
        jQuery('.net_price', lineItemRow).html('0');

    },

    checkLineItemRow: function () {
        var lineItemTable = this.getLineItemContentsContainer();
        var noRow = lineItemTable.find('.lineItemRow').length;
        if (noRow > 1) {
            this.showLineItemsDeleteIcon();
        } else {
            this.hideLineItemsDeleteIcon();
        }
    },

    showLineItemsDeleteIcon: function () {
        var lineItemTable = this.getLineItemContentsContainer();
        lineItemTable.find('.deleteRow').show();
    },

    hideLineItemsDeleteIcon: function () {
        var lineItemTable = this.getLineItemContentsContainer();
        lineItemTable.find('.deleteRow').hide();
    },

    /**
     * Function to swap array
     * @param Array that need to be swapped
     */
    swapObject: function (objectToSwap) {
        var swappedArray = {};
        var newKey, newValue;
        for (var key in objectToSwap) {
            newKey = objectToSwap[key];
            newValue = key;
            swappedArray[newKey] = newValue;
        }
        return swappedArray;
    },

    /**
     * Function to copy address between fields
     * @param strings which accepts value as either odd or even
     */
    copyAddress: function (swapMode) {
        var thisInstance = this;
        var formElement = this.getForm();
        var addressMapping = this.addressFieldsMappingInModule;
        if (swapMode == "false") {
            for (var key in addressMapping) {
                var fromElement = formElement.find('[name="' + key + '"]');
                var toElement = formElement.find('[name="' + addressMapping[key] + '"]');
                toElement.val(fromElement.val());
            }
        } else if (swapMode) {
            var swappedArray = thisInstance.swapObject(addressMapping);
            for (var key in swappedArray) {
                var fromElement = formElement.find('[name="' + key + '"]');
                var toElement = formElement.find('[name="' + swappedArray[key] + '"]');
                toElement.val(fromElement.val());
            }
            toElement.val(fromElement.val());
        }
    },

    /**
     * Function to register event for copying addresses
     */
    registerEventForCopyAddress: function () {
        var thisInstance = this;
        var formElement = this.getForm();
        jQuery('[name="copyAddressFromRight"],[name="copyAddressFromLeft"]').change(function () {
            var element = jQuery(this);
            var elementClass = element.attr('class');
            var targetCopyAddress = element.data('copyAddress');
            var objectToMapAddress;
            if (elementClass == "accountAddress") {
                var recordRelativeAccountId = jQuery('[name="account_id"]').val();
                if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
                    Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS'));
                } else {
                    var recordRelativeAccountName = jQuery('#account_id_display').val();
                    var data = {
                        'record': recordRelativeAccountId,
                        'selectedName': recordRelativeAccountName,
                        'source_module': "Accounts"
                    };
                    if (targetCopyAddress == "billing") {
                        objectToMapAddress = thisInstance.addressFieldsMappingBetweenModules['AccountsBillMap'];
                    } else if (targetCopyAddress == "shipping") {
                        objectToMapAddress = thisInstance.addressFieldsMappingBetweenModules['AccountsShipMap'];
                    }
                    thisInstance.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
                    element.attr('checked', 'checked');
                }
            } else if (elementClass == "contactAddress") {
                var recordRelativeContactId = jQuery('[name="contact_id"]').val();
                if (recordRelativeContactId == "" || recordRelativeContactId == "0") {
                    Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_CONTACT_TO_COPY_ADDRESS'));
                } else {
                    var recordRelativeContactName = jQuery('#contact_id_display').val();
                    var data = {
                        'record': recordRelativeContactId,
                        'selectedName': recordRelativeContactName,
                        source_module: "Contacts"
                    };
                    if (targetCopyAddress == "billing") {
                        objectToMapAddress = thisInstance.addressFieldsMappingBetweenModules['ContactsBillMap'];
                    } else if (targetCopyAddress == "shipping") {
                        objectToMapAddress = thisInstance.addressFieldsMappingBetweenModules['ContactsShipMap'];
                    }
                    thisInstance.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
                    element.attr('checked', 'checked');
                }
            } else if (elementClass == "shippingAddress") {
                var target = element.data('target');
                if (target == "shipping") {
                    var swapMode = "true";
                }
                thisInstance.copyAddress(swapMode);
            } else if (elementClass == "billingAddress") {
                var target = element.data('target');
                if (target == "billing") {
                    var swapMode = "false";
                }
                thisInstance.copyAddress(swapMode);
            }
        });
        jQuery('[name="copyAddress"]').on('click', function (e) {
            var element = jQuery(e.currentTarget);
            var swapMode;
            var target = element.data('target');
            if (target == "billing") {
                swapMode = "false";
            } else if (target == "shipping") {
                swapMode = "true";
            }
            thisInstance.copyAddress(swapMode);
        })
    },

    /**
     * Function to toggle shipping and billing address according to layout
     */
    registerForTogglingBillingandShippingAddress: function () {
        var billingAddressPosition = jQuery('[name="bill_street"]').closest('td').index();
        var copyAddress1Block = jQuery('[name="copyAddress1"]');
        var copyAddress2Block = jQuery('[name="copyAddress2"]');
        var copyHeader1 = jQuery('[name="copyHeader1"]');
        var copyHeader2 = jQuery('[name="copyHeader2"]');
        var copyAddress1toggleAddressLeftContainer = copyAddress1Block.find('[name="togglingAddressContainerLeft"]');
        var copyAddress1toggleAddressRightContainer = copyAddress1Block.find('[name="togglingAddressContainerRight"]');
        var copyAddress2toggleAddressLeftContainer = copyAddress2Block.find('[name="togglingAddressContainerLeft"]');
        var copyAddress2toggleAddressRightContainer = copyAddress2Block.find('[name="togglingAddressContainerRight"]');
        var headerText1 = copyHeader1.html();
        var headerText2 = copyHeader2.html();

        if (billingAddressPosition == 3) {
            if (copyAddress1toggleAddressLeftContainer.hasClass('hide')) {
                copyAddress1toggleAddressLeftContainer.removeClass('hide');
            }
            copyAddress1toggleAddressRightContainer.addClass('hide');
            if (copyAddress2toggleAddressRightContainer.hasClass('hide')) {
                copyAddress2toggleAddressRightContainer.removeClass('hide');
            }
            copyAddress2toggleAddressLeftContainer.addClass('hide');
            copyHeader1.html(headerText2);
            copyHeader2.html(headerText1);
            copyAddress1Block.find('[data-copy-address]').each(function () {
                jQuery(this).data('copyAddress', 'shipping');
            });
            copyAddress2Block.find('[data-copy-address]').each(function () {
                jQuery(this).data('copyAddress', 'billing');
            })
        }
    },

    /**
     * Function to check for relation operation
     * if relation exist calculation should happen by default
     */
    registerForRealtionOperation: function () {
        var form = this.getForm();
        var relationExist = form.find('[name="relationOperation"]').val();
        if (relationExist) {
            jQuery('.qty').trigger('focusout');
        }
    },

    //Related to preTaxTotal Field

    /**
     * Function to set the pre tax total
     */
    setPreTaxTotal: function (preTaxTotalValue) {
        jQuery('#pre_tax_total').val(preTaxTotalValue);
        return this;
    },

    /**
     * Function to get the pre tax total
     */
    getPreTaxTotal: function () {
        return parseFloat(jQuery('#preTaxTotal').text());
    },

    /**
     * Function to calculate the preTaxTotal value
     */
    calculatePreTaxTotal: function () {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        var netTotal = this.getNetTotal();
        var shippingHandlingCharge = this.getShippingAndHandling();
        var finalDiscountValue = this.getFinalDiscountTotal();
        var preTaxTotal = netTotal + shippingHandlingCharge - finalDiscountValue;
        var preTaxTotalValue = parseFloat(preTaxTotal).toFixed(numberOfDecimal);
        this.setPreTaxTotal(preTaxTotalValue);
    },

    /**
     * Function to save the pre tax total value
     */
    savePreTaxTotalValue: function () {
        jQuery('#pre_tax_total').val(this.getPreTaxTotal());
    },

    /**
     * Function which will register all the events
     */
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerReferenceSelectionEvent(container);
    },
    registerEventForTextarea: function () {
        var currentElement = null;
        jQuery('#lineItemTab textarea').on('click', function () {
            if (currentElement == null) {
                currentElement = jQuery(this);
                currentElement.animate({height: '60px'}, "slow");
                var functionHandle = function (e) {
                    element = jQuery(e.target);
                    if (element.is(currentElement)) {
                        return;
                    }
                    if (currentElement != null) {
                        currentElement.animate({height: '30px'}, "slow");
                        currentElement = null;
                    }
                };
                jQuery(document).on('click', '*', functionHandle);
            } else {

            }

        });
    },
    registerEventForChangeStandarFieldValue:function(){
        var thisInstance = this;
        $('td.fieldValue .inputElement').on('keyup change', function () {
            if (!isNaN(jQuery(this).val())) {
                var rows = $('table.lineItemContainer tr.lineItemRow');
                rows.each(function(k,row){
                    var listPriceInput = $(row).find('input.listPrice');
                    var name = listPriceInput.attr('name');
                    thisInstance.calculateValueByFormula($(row), name);
                    listPriceInput.trigger('change');
                });

            }
        });
    },
    registerEventForChangeFieldValue: function () {

        var thisInstance = this;
        lineItemTab = jQuery('#lineItemTab .lineItemContainer .listItem');
        lineItemTab.on('keyup change', 'input[type="text"]:not(.taxPercentage)', function () {
            if (!isNaN(jQuery(this).val())) {
                var parentRow = jQuery(this).closest('tr.lineItemRow');
                Quoter_Js.fixWidthInput(parentRow);
                thisInstance.calculateValueByFormula(parentRow, jQuery(this).attr('name'));
                thisInstance.calculateAllRunningSubTotalValue();
                thisInstance.calculateTotalValueByFormula(null);
            }
        });
        /*jQuery('select',lineItemTab).on('change', function () {
            if (!isNaN(jQuery(this).val())) {
                var parentRow = jQuery(this).closest('tr.lineItemRow');
                thisInstance.calculateValueByFormula(parentRow, jQuery(this).attr('name'));
                thisInstance.calculateAllRunningSubTotalValue();
                thisInstance.calculateTotalValueByFormula(null);
            }
        });*/
    },
    calculateTotalValueByFormula: function (currentName) {
        var thisIntance = this;
        thisIntance.calculateShippingAndHandlingTaxCharges();
        thisIntance.changeItemToltalFieldsValues(currentName);
        if (thisIntance.isGroupTaxMode()) {
            thisIntance.calculateGroupTax();
        } else {
            jQuery('#tax').val(0);
        }
    },
    changeItemToltalFieldsValues:function(currentName){
        var thisIntance = this;
        var totalSetting = this.totalSetting;
        jQuery.each(totalSetting, function (index, value) {
            var newKey = index.split('__');
            index = newKey[1];
            if (value.fieldFormula && currentName != index) {
                var sections = value.sectionInfo;
                // formula = thisIntance.billItemValueToFormula(value.fieldFormula);
                if(sections != '' && sections != undefined) {
                    formula = thisIntance.billItemValueToFormulaForSections(value);
                }else{
                    formula = thisIntance.billItemValueToFormula(value.fieldFormula);
                }
                formula = thisIntance.checkIfCondition(formula);
                //#3042455
                // fix issue caculate total by formula does not correct
                var check = parseInt(formula);
                if(isNaN(check)){
                        formula = thisIntance.billItemValueToFormula(value.fieldFormula);
                        formula = thisIntance.checkIfCondition(formula);
                }
                //end   #3042455
                var fieldValue = 0;
                try {
                    var fieldValue = eval(formula);
                } catch (e) {
                    if (e instanceof SyntaxError) {
                        fieldValue = 0;
                    }
                }
                if(isNaN(fieldValue)){
                    fieldValue = 0;
                }
                var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
                var fieldValue = parseFloat(fieldValue).toFixed(numberOfDecimal);
                jQuery('#' + index).val(fieldValue);
                jQuery('.' + index + " span").html(thisIntance.numberFormat(fieldValue));
            }
            if(index == 'tax'){
                if (thisIntance.isGroupTaxMode()) {
                    thisIntance.calculateGroupTax();
                } else {
                    jQuery('#tax').val(0);
                }
            }
        });
    },
    billItemValueToFormula: function (formula, rowTotal, runningItem) {
        var thisInstance = this;
        var regExp = /SUM(\(.*\))(?=\*|\+|-|\/)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        if(formula.indexOf("IF") >= 0 || formula.indexOf("if") >= 0){
            regExp = /SUM(\([$\w]*\))(?=\*|\+|-|\/|)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        }
        if (rowTotal == undefined) {
            rowTotal = jQuery('#lineItemTab .lineItemContainer tr.lineItemRow').length;
        }
        var matches = regExp.exec(formula);
        if (matches) {
            var subRegExp = /SUM\(([^)]+)\)/;
            var subMatches = subRegExp.exec(matches[0]);
            var total = 0;
            for (var i = 1; i <= rowTotal; i++) {
                var subFormula = subMatches[1];
                if (jQuery('#row' + i).attr('level') == 1) {
                    jQuery.each(thisInstance.columnSetting, function (key, val) {
                        // var fieldValue = jQuery('#lineItemTab .lineItemContainer [name="' + val.columnName + i + '"]').val();
                        var fieldValue = jQuery('#row' + i).find('[name^="' + val.columnName + '"]').val();
                        if (fieldValue == undefined || !fieldValue) {
                            fieldValue = 0;
                        }
                        fieldValue = parseFloat(fieldValue);
                        var pattern = '\\$' + val.columnName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        subFormula = subFormula.replace(regex, fieldValue);
                    });
                    subFormula = thisInstance.billOtherValueToFormula(subFormula);
                    try {
                        var result = eval(subFormula);
                    } catch (e) {
                        if (e instanceof SyntaxError) {
                            result = 0;
                        }
                    }
                    total += result;
                }
            }
            formula = formula.replace(matches[0], total);
            return thisInstance.billItemValueToFormula(formula);
        } else {
            return thisInstance.billTotalValueToFormula(formula, runningItem);
        }
    },
    billItemValueToFormulaForSections: function (value, rowTotal, runningItem) {
        var thisInstance = this;
        var regExp = /SUM(\(.*\))(?=\*|\+|-|\/)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        if (rowTotal == undefined) {
            rowTotal = jQuery('#lineItemTab .lineItemContainer tr.lineItemRow').length;
        }
        var formula = value.fieldFormula;
        if(formula.indexOf("IF") >= 0 || formula.indexOf("if") >= 0){
            regExp = /SUM(\([$\w]*\))(?=\*|\+|-|\/|)|SUM(\([$\w]*\)|[($\w+\-*\/)]*)/;
        }
        var sections = value.sectionInfo;
        var allSections = jQuery('tr.section').find('.section_value');
        var endrow = rowTotal;
        var dataSections = {};
        $.each(allSections, function (key, val) {
            var focus = $(this);
            dataSections[focus.val()] = focus.data('rowno');
        });
        var startrow = dataSections[sections];
        //Object Helper Functions
        oFunctions = {};
        oFunctions.keys = {};

        //NEXT KEY
        oFunctions.keys.next = function(o, id){
            var keys = Object.keys( o ),
                idIndex = keys.indexOf( id ),
                nextIndex = idIndex += 1;
            if(nextIndex >= keys.length){
                //we're at the end, there is no next
                return;
            }
            var nextKey = keys[ nextIndex ]
            return nextKey;
        };
        var nextSection = oFunctions.keys.next(dataSections, sections);
        if(nextSection != undefined) {
            endrow = dataSections[nextSection] - 1;
        }
        var matches = regExp.exec(formula);
        if (matches) {
            var subRegExp = /SUM\(([^)]+)\)/;
            var subMatches = subRegExp.exec(matches[0]);
            var total = 0;
            for (var i = startrow; i <= endrow; i++) {

                var subFormula = subMatches[1];
                if (jQuery('#row' + i).attr('level') == 1) {
                    jQuery.each(thisInstance.columnSetting, function (key, val) {
                        // var fieldValue = jQuery('#lineItemTab .lineItemContainer [name="' + val.columnName + i + '"]').val();
                        var fieldValue = jQuery('#row' + i).find('[name^="' + val.columnName + '"]').val();
                        if (fieldValue == undefined || !fieldValue) {
                            fieldValue = 0;
                        }
                        fieldValue = parseFloat(fieldValue);
                        var pattern = '\\$' + val.columnName + '\\$';
                        var regex = new RegExp(pattern, "gi");
                        subFormula = subFormula.replace(regex, fieldValue);
                    });
                    subFormula = thisInstance.billOtherValueToFormula(subFormula);
                    try {
                        var result = eval(subFormula);
                    } catch (e) {
                        if (e instanceof SyntaxError) {
                            result = 0;
                        }
                    }
                    total += result;
                }
            }
            formula = formula.replace(matches[0], total);
            if((formula.indexOf("IF") >= 0 || formula.indexOf("if") >= 0) && formula.indexOf("SUM") >= 0){
                formula = {sectionInfo:value.sectionInfo,fieldFormula:formula};
                return thisInstance.billItemValueToFormulaForSections(formula);
            }else{
                return formula;
            }
            return formula;
        } else {
            return thisInstance.billTotalValueToFormula(formula, runningItem);
        }
    },
    billTotalValueToFormula: function (formula, runningItem) {
        var thisInstance = this;
        if (this.totalSetting) {
            jQuery.each(this.totalSetting, function (index, value) {
                var newKey = index.split('__');
                index = newKey[1];
                if (runningItem == undefined) {
                    var totalValue = jQuery("#" + index).val();
                } else {
                    var totalValue = runningItem.find(".running_" + index).val();
                }
                var pattern = '\\$' + index + '\\$';
                var regex = new RegExp(pattern, "gi");
                if (totalValue == '' || totalValue == null) {
                    formula = formula.replace(regex, 0);
                } else {
                    formula = formula.replace(regex, totalValue);
                }
            });
            formula = thisInstance.billOtherValueToFormula(formula);
        }
        return formula;
    },
    billOtherValueToFormula: function (formula) {
        var patternForOtherfield = /(?:\$)(.*?)(?:\$)/gi;
        var match = patternForOtherfield.exec(formula);
        if (match != null) {
            var fieldOtherValue = jQuery('[name="' + match[1] + '"]').val();
            if (isNaN(fieldOtherValue)) {
                fieldOtherValue = 0;
            }
            var pattern = '\\$' + match[1] + '\\$';
            var regex = new RegExp(pattern, "gi");
            formula = formula.replace(regex, fieldOtherValue);
            return this.billOtherValueToFormula(formula);
        }
        return formula;
    },
    registerEventAddSubProductButton: function () {
        var thisInstance = this;
        var handleAddSubProduct = function () {
            var newRow = thisInstance.getSubBasicRow('Product').addClass(thisInstance.rowClass);
            jQuery('.quoterLineItemPopup[data-module-name="Services"]', newRow).remove();
            var parentId = jQuery(this).data('parent-id');
            var sequenceNumber = thisInstance.getNextLineItemRowNumber();
            var level = jQuery(this).data('level');
            newRow.attr('level', level).find('.level').val(level);
            var strDisplay = "<i>";
            for (var i = 1; i < level; i++) {
                strDisplay += "&#8594; &nbsp; ";
            }
            strDisplay += "</i>";
            newRow.find('td:first div').prepend(strDisplay);

            newRow.find('.parentId').val(parentId);
            newRow.find('.quoterLineItemPopup').attr("data-parent-id", parentId);
            newRow.attr('id', 'row' + sequenceNumber);

            var prevRow = jQuery(this).closest('tr').prev();
            prevRow.after(newRow);
            thisInstance.checkLineItemRow();
            newRow.find('input.rowNumber').val(sequenceNumber);
            thisInstance.updateLineItemsElementWithSequenceNumber(newRow, sequenceNumber);
            thisInstance.updateLineItemElementByOrder();
            newRow.find('input.productName').addClass('autoComplete');
            thisInstance.registerLineItemAutoComplete(newRow);
            Quoter_Js.fixWidthInput(newRow);
            vtUtils.applyFieldElementsView(newRow);
            thisInstance.makeLineItemsSortable();
            var tableHeight = jQuery('#lineItemTab .itemDetailContainer .tblItemDetailContainer').outerHeight();
            var selectHeight = jQuery('#lineItemTab .itemDetailContainer .tblItemDetailContainer tr td div.chzn-container .chzn-drop').height();
            if (selectHeight != null) {
                jQuery('#lineItemTab .itemDetailContainer').height(tableHeight + selectHeight - 60);
            }
        };
        jQuery('.addSubProduct').off('click');
        jQuery('.addSubProduct').on('click', handleAddSubProduct);

    },
    fixFieldName: function () {
        var thisInstance = this;
        var setting = thisInstance.columnSetting;
        if (setting && setting != undefined) {
            jQuery.each(setting, function (index, val) {
                if (val.columnName.match(/^cf_/gi)) {
                    var customFields = jQuery('#lineItemTab').find('.' + val.columnName);
                    if (customFields.length > 0) {
                        customFields.each(function () {
                            var ele = jQuery(this);
                            var lineItemType = ele.data('lineitemtype');
                            var id = ele.data('rowid');
                            var inputele = null;
                            var displayEle = null;
                            if (lineItemType == 'Services') {
                                inputele = ele.find('[name^="' + val.serviceField + '"]:not(:hidden)');
                                displayEle = ele.find('[name^="' + val.serviceField + '_display"]');
                            } else {
                                inputele = ele.find('[name^="' + val.productField + '"]:not(:hidden)');
                                displayEle = ele.find('[name^="' + val.productField + '_display"]');
                            }

                            if (inputele != null && inputele.length > 0) {
                                inputele.each(function () {
                                    jQuery(this).attr('name', id);
                                    var fieldtype = jQuery(this).data('fieldtype');
                                    if (fieldtype == 'multipicklist') {
                                        jQuery(this).attr('name', id + '[]');
                                    } else {
                                        jQuery(this).attr('name', id);
                                    }
                                    if (!jQuery(this).is('select')) {
                                        jQuery(this).attr('id', id);
                                    }
                                });
                            }
                            if (displayEle != null && displayEle.length > 0) {
                                displayEle.each(function () {
                                    jQuery(this).attr('name', id + "_display");
                                    jQuery(this).attr('id', id + "_display");
                                });
                            }
                        });
                    }
                    var serviceBase = jQuery('.lineItemCloneCopyForService  .' + val.columnName).find('[name^="' + val.serviceField + '"]');
                    if (serviceBase != null && serviceBase.length > 0) {
                        serviceBase.each(function () {
                            var fieldtype = jQuery(this).data('fieldtype');
                            if (fieldtype == 'multipicklist') {
                                jQuery(this).attr('name', val.columnName + '0[]');
                            } else {
                                jQuery(this).attr('name', val.columnName + '0');
                            }
                            if (!serviceBase.is('select')) {
                                serviceBase.attr('id', val.columnName + '0');
                            }
                        });
                    }
                    jQuery('.lineItemCloneCopyForService [name="' + val.serviceField + '_display"]').attr('name', val.columnName + '0').attr('id', val.columnName + '0');

                    var productBase = jQuery('.lineItemCloneCopyForProduct .' + val.columnName).find('[name^="' + val.productField + '"]');
                    if (productBase != null && productBase.length > 0) {
                        productBase.each(function () {
                            var fieldtype = jQuery(this).data('fieldtype');
                            if (fieldtype == 'multipicklist') {
                                jQuery(this).attr('name', val.columnName + '0[]');
                            } else {
                                jQuery(this).attr('name', val.columnName + '0');
                            }
                            if (!productBase.is('select')) {
                                productBase.attr('id', val.columnName + '0');
                            }
                        });
                    }
                    jQuery('.lineItemCloneCopyForProduct [name="' + val.serviceField + '_display"]').attr('name', val.columnName + '0').attr('id', val.columnName + '0');
                }
            });

        }
    },
    registerEventForChangeTotalValue: function () {
        var thisInstance = this;
        lineItemResult = jQuery('#lineItemResult');
        lineItemResult.find('input[type="text"], select').on('keyup change', function () {
            if (!isNaN(jQuery(this).val())) {
                thisInstance.calculateAllRunningSubTotalValue();
                thisInstance.calculateTotalValueByFormula(jQuery(this).attr('id'));
                thisInstance.calculateTotalValueByFormula(jQuery(this).attr('id'));
            }
        });
    },
    registerEventForSectionDropDown: function () {
        var sectionContainer = jQuery('.section_container');
        sectionContainer.find('.section_item').on('click', function () {
            var subsection = jQuery(this).data('subsection');
            if(subsection == true){
                var sectionValue = jQuery(this).data('section');
                //var columnCount = jQuery('#lineItemTab .lineItemContainer tr:eq(0)>th').length;
                var columnCount = jQuery('#lineItemTab .lineItemContainer tr:eq(0)>th.cellItem:visible').length;
                var lineItemContainer = jQuery('#lineItemTab .lineItemContainer tbody.listItem');
                var lastLineItemRow = lineItemContainer.find('.lineItemRow').last();
                var rownoSections = 1;
                if(lastLineItemRow.length > 0) {
                    rownoSections = parseInt(lastLineItemRow.attr('rowname')) + 1;
                }
                if (jQuery('#taxtype').val() == 'group') {
                    var tdSection = '<td class="fieldLabel hide" style="border-left:0;">&nbsp;</td>';
                } else {
                    var tdSection = '<td class="fieldLabel" style="border-left:0;">&nbsp;</td>';
                }
                var minusColumn = 0;
                if(jQuery('#taxtype').val() != 'group') minusColumn = 1;
                var trElement = '<tr class="section" style="background-color: #f9f9f9;font-size: 12px;">' +
                    '<td class="fieldLabel" colspan="' + (parseInt(columnCount) - minusColumn) + '" style = "font-size: 12px;">' +
                    '<span class="section_tool" style="display: inline-block; width:100%; text-align: left; position: relative;">' +
                    '<img class="section_move_icon" src="layouts/v7/skins/images/drag.png" border="0" title="Drag">&nbsp;' +
                    '<i class="fa fa-trash deleteSection cursorPointer" title="Delete" style="font-size: 13px; color: black;"></i>&nbsp;'+
                    '<b>' + sectionValue + '</b>'+
                    '</span>' +
                        // '<span style="text-align: left;"><b>' + sectionValue + '</b></span>' +
                    '<input type = "hidden" class ="section_value" name="section0" value="' + sectionValue + '" data-rowno="'+rownoSections+'" />' +
                    '</td>' + tdSection +
                    '</tr>';
                lineItemContainer.append(trElement);
            }else{

            }
        });
    },
    registerEventForRunningSubTotalDropDown: function () {
        var thisInstance = this;
        var runningSubTotalItemContainer = jQuery('.addRunningSubTotal');
        runningSubTotalItemContainer.find('.runningSubTotalItem').on('click', function () {
            var subtotal = jQuery(this).data('subtotal');
            if(subtotal == true){
                var itemName = jQuery(this).data('item-name');
                var label = jQuery(this).text();
                var runningLabel = jQuery(this).data('running-label');
                //var columnCount = jQuery('#lineItemTab .lineItemContainer tr:eq(0)>th').length;
                var columnCount = jQuery('#lineItemTab .lineItemContainer tr:eq(0)>th.cellItem:visible').length;
                var lineItemContainer = jQuery('#lineItemTab .lineItemContainer tbody.listItem');
                var runningItemVal = jQuery('#' + itemName).val();
                var rowNo = lineItemContainer.find('.lineItemRow').length;
                if (jQuery('#taxtype').val() == 'group') {
                    var tdLast = '<td class="tdSpace hide" style="border-left:0;">&nbsp;</td>';
                } else {
                    var tdLast = '<td class="tdSpace" style="border-left:0;">&nbsp;</td>';
                }
                var minusColumn = 1;
                if(jQuery('#taxtype').val() != 'group') minusColumn = 2;
                var trElement = '<tr style="background-color: #f9f9f9;font-size: 12px;" class="running_item" data-running-item-name = "' + itemName + '" data-running-item-rowno = "' + rowNo + '">' +
                    '<td>' +
                    '<span class="running_item_tool" style="display: inline-block; width:40px; text-align: left;">' +
                    '<img class="running_item_move_icon" src="layouts/v7/skins/images/drag.png" border="0" title="Drag">&nbsp;' +
                    '<i class="fa fa-trash delete_running_item cursorPointer" title="Delete"></i>&nbsp;' +
                    '</span>&nbsp;&nbsp;' +
                    '</td>' + tdLast + '<td colspan="' + (parseInt(columnCount) - minusColumn) + '" style="border-left:0;">' +
                    '<span class="pull-right" style="text-align: left;"><b>' + runningLabel +' '+ label + ': </b><b class="running_item_display">' + runningItemVal + '</b></span>' +
                    '<input type = "hidden" class ="running_item_name" name="running_item_name' + rowNo + '[]" value="' + itemName + '" />' +
                    '<input type = "hidden" class ="running_item_value" name="running_item_value' + rowNo + '[]" value="' + runningItemVal + '" />';
                jQuery.each(thisInstance.totalSetting, function (key, val) {
                    var newKey = key.split('__');
                    key = newKey[1];
                    trElement += '<input type = "hidden" class ="running_' + key + '" value="' + jQuery('#' + key).val() + '" />';
                });
                trElement += '</td></tr>';
                lineItemContainer.append(trElement);
                thisInstance.registerEventForDeleteRunningItem();
                thisInstance.calculateAllRunningSubTotalValue();
            }else{

            }

        });
    },
    registerEventForDeleteRunningItem: function () {
        jQuery('.delete_running_item').on('click', function () {
            jQuery(this).closest('tr.running_item').remove();
        });
    },
    registerTksSelectTemplateQuote: function () {
        var thisInstance = this;
        app.changeSelectElementView(jQuery('#quoter_template'));
            // jQuery('#tks_quotetemp').attr('id', 'quoter_template');
            jQuery(document).on('change','#quoter_template', function () {
                var templateId = jQuery(this).val();
                var params = {
                    module: 'Quoter',
                    action: 'ActionAjax',
                    mode: 'getPSTemplates',
                    record: templateId,
                    current_module: app.getModuleName()
                };
                var progressIndicator = jQuery.progressIndicator();
                AppConnector.request(params).then(
                    function (data) {
                        progressIndicator.hide();
                        var responseData = data.result;
                        var defaultValue = responseData.defaultValue;
                        var customValue = responseData.customValue;
                        var lastrw = jQuery('#lineItemTab .lineItemContainer tr.lineItemRow:last');
                        prod = lastrw.find('input.productName');
                        if (typeof prod.val() == "undefined" || prod.val() == null || prod.val() == '') {
                            lastrw.remove();
                        }
                        jQuery.each(defaultValue, function (i, v) {
                            var sectionValue = v[Object.keys(v)[0]]['section'];
                            if(sectionValue != null && sectionValue!=''){
                                var columnCount = jQuery('#lineItemTab .lineItemContainer tr:eq(0)>th.cellItem:visible').length;
                                var lineItemContainer = jQuery('#lineItemTab .lineItemContainer tbody.listItem');
                                var lastLineItemRow = lineItemContainer.find('.lineItemRow').last();
                                var rownoSections = 1;
                                if(lastLineItemRow.length > 0) {
                                    rownoSections = parseInt(lastLineItemRow.attr('rowname')) + 1;
                                }
                                if (jQuery('#taxtype').val() == 'group') {
                                    var tdSection = '<td class="fieldLabel hide" style="border-left:0;">&nbsp;</td>';
                                } else {
                                    var tdSection = '<td class="fieldLabel" style="border-left:0;">&nbsp;</td>';
                                }
                                var minusColumn = 0;
                                if(jQuery('#taxtype').val() != 'group') minusColumn = 1;
                                var trElement = '<tr class="section" style="background-color: #f9f9f9;font-size: 12px;">' +
                                    '<td class="fieldLabel" colspan="' + (parseInt(columnCount) - minusColumn) + '" style = "font-size: 12px;">' +
                                    '<span class="section_tool" style="display: inline-block; width:100%; text-align: left; position: relative;">' +
                                    '<img class="section_move_icon" src="layouts/v7/skins/images/drag.png" border="0" title="Drag">&nbsp;' +
                                    '<i class="fa fa-trash deleteSection cursorPointer" title="Delete" style="font-size: 13px; color: black;"></i>&nbsp;'+
                                    '<b>' + sectionValue + '</b>'+
                                    '</span>' +
                                    '<input type = "hidden" class ="section_value" name="section0" value="' + sectionValue + '" data-rowno="'+rownoSections+'" />' +
                                    '</td>' + tdSection +
                                    '</tr>';
                                lineItemContainer.append(trElement);
                            }
                            var type = v[Object.keys(v)[0]]['type'];
                            if (type == 'Products') {
                                var row = jQuery('#addProduct').trigger('click');
                            } else if (type == 'Services') {
                                var row1 = jQuery('#addService').trigger('click');
                            }
                            var newRow = jQuery('#lineItemTab .lineItemContainer tr.lineItemRow:last');
                            var targetElem = jQuery('.quoterLineItemPopup', newRow);
                            var isTemplate = true;
                            thisInstance.mapResultsToFields(type, targetElem, defaultValue[i], customValue[i], thisInstance, isTemplate);
                        });
                        if(responseData.record_data.hdnTaxType) {
                            jQuery('#taxtype').val(responseData.record_data.hdnTaxType);
                            jQuery("#taxtype option[value="+responseData.record_data.hdnTaxType+"]").attr('selected', 'selected');
                            jQuery('#taxtype').trigger("liszt:updated").trigger("change");
                        }
                        //thisInstance.registerEventForChangeFieldValue();
                        thisInstance.calculateTotalValueByFormula(null);
                    }
                );
            });
    },
    registerSubmitEventUpdateSection : function () {
        var self = this;
        var editViewForm = this.getForm();
        editViewForm.submit(function(e){
            self.updateSectionsName();
            self.calculateAllRunningSubTotalValue();
            self.calculateTotalValueByFormulaWhenSubmit(null);
        })
    },
    calculateTotalValueByFormulaWhenSubmit: function (currentName) {
        var thisIntance = this;
        if (thisIntance.isGroupTaxMode()) {

        } else {
            jQuery('#tax').val(0);
        }
        thisIntance.calculateShippingAndHandlingTaxCharges();
        thisIntance.changeItemToltalFieldsValues(currentName);
    },
    registerAutoCompleteFields: function (container) {
        var thisInstance = this;
        container.find('input.autoCompleteNew').autocomplete({
            'minLength': '3',
            'source': function (request, response) {
                //element will be array of dom elements
                //here this refers to auto complete instance
                var inputElement = jQuery(this.element[0]);
                var searchValue = request.term;
                var params = thisInstance.getReferenceSearchParams(inputElement);
                params.search_value = searchValue;
                thisInstance.searchModuleNames(params).then(function (data) {
                    var reponseDataList = [];
                    var serverDataFormat = data.result;
                    if (serverDataFormat.length <= 0) {
                        jQuery(inputElement).val('');
                        serverDataFormat = new Array({
                            'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
                            'type': 'no results'
                        });
                    }
                    for (var id in serverDataFormat) {
                        var responseData = serverDataFormat[id];
                        reponseDataList.push(responseData);
                    }
                    response(reponseDataList);
                });
            },
            'select': function (event, ui) {
                var selectedItemData = ui.item;
                //To stop selection if no results is selected
                if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
                    return false;
                }
                selectedItemData.name = selectedItemData.value;
                var element = jQuery(this);
                var tdElement = element.closest('td');
                thisInstance.setReferenceFieldValue(tdElement, selectedItemData);

                var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
                var fieldElement = tdElement.find('input[name="' + sourceField + '"]');

                fieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, {'data': selectedItemData});
            },
            'change': function (event, ui) {
                var element = jQuery(this);
                //if you dont have readonly attribute means the user didnt select the item
                if (element.attr('readonly') == undefined) {
                    element.closest('td').find('.clearReferenceSelection').trigger('click');
                }
            },
            'open': function (event, ui) {
                //To Make the menu come up in the case of quick create
                jQuery(this).data('autocomplete').menu.element.css('z-index', '100001');

            }
        });
    },
    registerEventForSaveEventButton: function (){
        var thisInstance = this;
        var obj =jQuery('#frmEventsList table .listViewEntriesCheckBox');
        jQuery('#btnSaveEvent').on('click',function(){

            if(jQuery('#productName1').val() !== 'undefined'){
                if(jQuery('#productName1').val()== ''){
                    jQuery('#row1').remove();
                }
            }

            var record = [];
            // var inventoryInstance = new Inventory_Edit_Js();

            var lineItem = jQuery('#lineItemTab .lineItemRow');
            var activityidExist = [];
            lineItem.each(function(index) {
                var id = index + 1;
                var serviceId = jQuery('#hdnProductId' + id).val();
                var activityid = jQuery(this).find("input[name='activityid[]']").val();
                activityidExist.push(activityid);
                if (typeof  serviceId == 'undefined') {
                    jQuery(this).remove();
                }
            });

            //var activityid
            thisInstance.arrActivityid = [];
            var params = {};
            params.currentTarget = jQuery('button#addService');
            obj.each(function(){
                if(jQuery(this).is(":checked")){
                    var recordid = jQuery(this).val();
                    if(jQuery.inArray(recordid,activityidExist) == -1){
                        //add new row
                        var currentRow = jQuery(this).closest('tr');
                        var data = currentRow.data('info');
                        var newRow = thisInstance.registerEventAddService();
                        var quantityService = data.quantity;

                        var servicePrice = data.unit_price;
                        var start_date_time = data.start_date_time;
                        start_date_time = start_date_time.split(' ')[0];

                        var rowNumber = newRow.find('.rowNumber').val();

                        var dataDescription = 'Date: ' + start_date_time + "\n" + 'Description: ' + data.description;
                        newRow.find('#productName'+rowNumber).val(data.service_name);
                        newRow.find('#comment'+rowNumber).val(dataDescription);
                        if (data.quantity == '0'){
                            quantityService = '0.1';
                        }
                        newRow.find('#quantity'+rowNumber).val(quantityService);
                        newRow.find('#listprice'+rowNumber).val(Math.round(servicePrice * 1000)/1000);
                        newRow.find('#productTotal'+rowNumber).html(Math.round(data.quantity*servicePrice * 1000)/1000);
                        newRow.find('#netPrice'+rowNumber).html(Math.round(data.quantity*servicePrice * 1000)/1000);
                        newRow.find('#hdnProductId'+rowNumber).val(data.serviceid);
                        newRow.find('#lineItemType'+rowNumber).val('Services');
                        var element = "<input type='hidden' name='activityid[]' value='"+ recordid + "'/>";
                        var parentRow = newRow;
                        var serviceid = data.serviceid;
                        var newParams = {
                            module: 'Quoter',
                            action: 'ActionAjax',
                            mode: 'getCustomFieldValue',
                            targetModule:'Invoice',
                            records: serviceid,
                            viewType: 'ServicesPopup'
                        };
                        AppConnector.request(newParams).then(
                            function(data){
                                var customValues = data.result;
                                jQuery.each(customValues, function (i,customValue) {
                                    if(i == serviceid ){
                                        jQuery.each(customValue,function(index,value){
                                            var customFields = parentRow.find('[name="'+index+rowNumber+'"],[name="'+index+rowNumber+'[]"]');
                                            customFields.each(function() {
                                                var customField = jQuery(this);
                                                if(value){
                                                    if(typeof value =='object'){
                                                        customField.val(value.fieldvalue);
                                                        customField.closest('td').find('[name="'+index+rowNumber+'_display"]').val(value.display_value).attr('readonly',true);
                                                    }else{
                                                        customField.val(value);
                                                        if(customField.is('select')){
                                                            var arrValue = value.split(" |##| ");
                                                            jQuery.each(arrValue, function( i, v ){
                                                                customField.find('option[value="'+ v +'"]').prop("selected", "selected")
                                                            });
                                                        }
                                                    }
                                                }
                                            });
                                        });
                                    }
                                });
                            }
                        );
                        newRow.append(element);
                        thisInstance.lineItemToTalResultCalculations();
                        $(('#quantity' + rowNumber)).trigger('change');
                    }
                    thisInstance.arrActivityid.push(recordid);
                }
            });
            lineItem.each(function(index){
                var activityid = jQuery(this).find("input[name='activityid[]']").val();
                if(typeof activityid != 'undefined' && jQuery.inArray(activityid,thisInstance.arrActivityid) == -1){
                    jQuery(this).remove();
                }
            });
            app.helper.hideModal();
            //jQuery.unblockUI();

        });
    },
    registerEventForReviewTimeLogButton: function(){
        var timeTracker = new TimeTrackerInvoiceJs();
        var Instance = this;
        jQuery('#openListEvent').on('click', function () {
            var actionParams = {
                'module':'TimeTracker',
                'view':'MassActionAjax',
                'mode':'getEventForInvoice'
            };
            var contactid = jQuery("#EditView input[name=contact_id]").val();
            var accountid = jQuery("#EditView input[name=account_id]").val();

            if(typeof contactid != 'undefined'){
                actionParams.contactid = contactid;
            }

            if(typeof accountid != 'undefined'){
                actionParams.accountid = accountid;
            }
            app.request.post({data:actionParams}).then(
                function(err, res){
                    if (err === null){
                        app.helper.showModal(res,{'cb' : res});
                        var obj =jQuery('#frmEventsList table .listViewEntriesCheckBox');
                        obj.each(function(){
                            var recordid = jQuery(this).val();
                            if(jQuery.inArray(recordid,Instance.arrActivityid) != -1){
                                jQuery(this).attr('checked',true);
                            }
                        });
                        Instance.registerEventForSaveEventButton();
                        timeTracker.registerEventForMainCheckbox();
                    }
                }
            );
        });
    },
    registerEventDeleteSections : function () {
        var thisInstance = this;
        jQuery(document).on('click', '.deleteSection', function () {
            var focus = $(this);
            var parentElement = focus.closest('tr.section');
            parentElement.remove();
            thisInstance.calculateTotalValueByFormula(null);
        });
    },
    registerFixItemColumnWidth: function () {
        var self = this;
        var vtversion=self.getVtigerVersion();
        if(vtversion.indexOf('8.2') != -1){
            $('table#lineItemTab tbody tr > :nth-child(1)').css({'width':'10%'});
            $('table#lineItemTab tbody tr > :nth-child(1) input').css({'width':'40%'});
        }
    },
    registerRegionChangeEvent: function () {
        var regionElement = this.getRegionElement();
        var thisInstance = this;
        regionElement.change(function (e) {
            var message = app.vtranslate('JS_CONFIRM_TAXES_AND_CHARGES_REPLACE');
            var element = jQuery(e.currentTarget);
            app.helper.showConfirmationBox({'message': message}).then(
                function (e) {
                    var lineItemTab = thisInstance.getLineItemContentsContainer();
                    var selectedRegion = element.find('option:selected');
                    var selectedRegionId = selectedRegion.val();
                    var info = selectedRegion.data('info');
                    lineItemTab.find('tr.' + thisInstance.rowClass).each(function (index, domElement) {
                        var lineItemRow = jQuery(domElement);
                        var taxPercentages = jQuery('.taxPercentage', lineItemRow);
                        jQuery.each(taxPercentages, function (index1, taxDomElement) {
                            var taxPercentage = jQuery(taxDomElement);
                            var regionsList = taxPercentage.data('regionsList');
                            var value = regionsList['default'];
                            if (selectedRegionId && regionsList[selectedRegionId]) {
                                value = regionsList[selectedRegionId];
                            }
                            taxPercentage.val(parseFloat(value));
                        });
                        if (thisInstance.isIndividualTaxMode()) {
                            thisInstance.calculateTaxForLineItem(lineItemRow);
                        }
                        thisInstance.calculateValueByFormula(lineItemRow);
                    });
                    thisInstance.calculateNetTotal();
                    thisInstance.calculateFinalDiscount();
                    var taxes = info.taxes;
                    var groupTaxContainer = jQuery('#group_tax_row');
                    for (var taxId in taxes) {
                        element = groupTaxContainer.find('[name="tax' + taxId + '_group_percentage"]');
                        element.val(parseFloat(taxes[taxId]['value']));
                        element.data('compoundOn', taxes[taxId]['compoundOn']);
                    }
                    if (thisInstance.isGroupTaxMode()) {
                        thisInstance.calculateGroupTax();
                    }

                    var charges = info.charges;
                    var chargesContainer = jQuery('#chargesBlock');
                    var chargeTaxesContainer = jQuery('#chargeTaxesBlock');
                    for (var chargeId in charges) {
                        var chargeInfo = charges[chargeId];
                        var property = 'percent';
                        var chargeValue = parseFloat(chargeInfo[property]);
                        if (chargeInfo.hasOwnProperty('value')) {
                            property = 'value';
                            chargeValue = parseFloat(chargeInfo[property]) * parseFloat(jQuery('#conversion_rate').val());
                        }
                        chargesContainer.find('[name="charges[' + chargeId + '][' + property + ']"]').val(chargeValue);

                        var chargeTaxes = chargeInfo['taxes'];
                        for (var chargeTaxId in chargeTaxes) {
                            element = chargeTaxesContainer.find('[name="charges[' + chargeId + '][taxes][' + chargeTaxId + ']"]');
                            element.val(parseFloat(chargeTaxes[chargeTaxId]['value']));
                            element.data('compoundOn', chargeTaxes[chargeTaxId]['compoundOn']);
                        }
                    }
                    thisInstance.calculateTotalValueByFormula();
                    thisInstance.calculateGrandTotal();
                },
                function(error, err){});
        });
    },
    _registerEvents: function () {
        this.registerClearReferenceSelectionEvent(this.getLineItemContentsContainer());
        this.registerAutoCompleteFields(jQuery('tbody.listItem'));
        this.registerAddingNewProductsAndServices();
        this.registerDeleteLineItemEvent();
        this.makeLineItemsSortable();
        this.lineItemActions();
        this.lineItemResultActions();
        this.registerEventForTextarea();
        this.registerEventForChangeFieldValue();
        this.registerEventForChangeStandarFieldValue();
        this.registerEventAddSubProductButton();
        this.registerEventForChangeTotalValue();
        this.registerEventForSectionDropDown();
        this.registerEventForRunningSubTotalDropDown();
        this.registerEventForDeleteRunningItem();
        this.registerTksSelectTemplateQuote();
        this.registerSubmitEventUpdateSection();
        this.registerEventDeleteSections();
        this.registerRegionChangeEvent();
        this.registerFixItemColumnWidth();

        //this.registerValidation();
    }

});
jQuery(document).ready(function () {
    var sPageURL = window.location.search.substring(1);
    var targetModule = '';
    var targetView = '';
    var targetRecord = '';
    var targetMode = '';
    var salesorder_id = '';
    var quote_id = '';
    var invoice_id = '';
    var isDuplicate = '';
    var sourceRecord = '';
    var sourceModule = '';
    var returnrecord = '';
    var returnmodule = '';
    var relatedModule = '';

    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == 'module') {
            targetModule = sParameterName[1];
        }
        else if (sParameterName[0] == 'view') {
            targetView = sParameterName[1];
        }
        else if (sParameterName[0] == 'record') {
            targetRecord = sParameterName[1];
        } else if (sParameterName[0] == 'salesorder_id') {
            salesorder_id = sParameterName[1];
        } else if (sParameterName[0] == 'quote_id') {
            quote_id = sParameterName[1];
        } else if (sParameterName[0] == 'invoice_id') {
            invoice_id = sParameterName[1];
        } else if (sParameterName[0] == 'mode') {
            targetMode = sParameterName[1];
        } else if (sParameterName[0] == 'isDuplicate') {
            isDuplicate = sParameterName[1];
        }else if (sParameterName[0] == 'sourceRecord') {
            sourceRecord = sParameterName[1];
        }else if (sParameterName[0] == 'sourceModule') {
            sourceModule = sParameterName[1];
        }else if (sParameterName[0] == 'returnrecord') {
            returnrecord = sParameterName[1];
        }else if (sParameterName[0] == 'returnmodule') {
            returnmodule = sParameterName[1];
        }else if (sParameterName[0] == 'relatedModule') {
            relatedModule = sParameterName[1];
        }
    }
    var lineItemTab = jQuery('#lineItemTab');

    var listModulesHandle = ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'];

    if (targetView == 'Detail' && listModulesHandle.indexOf(targetModule) != -1) {
        var currentRelatedTab = jQuery('.related-tabs .tab-item').filter('.active');
        if(currentRelatedTab.length > 0 && currentRelatedTab.data('link-key') == 'LBL_RECORD_DETAILS'){
            Quoter_Js.loadLineItemsDetail(targetRecord,targetModule);
        }
        app.event.on("post.relatedListLoad.click", function () {
            var currentRelatedTab = jQuery('.related-tabs .tab-item').filter('.active');
            if(currentRelatedTab.length > 0 && currentRelatedTab.data('link-key') == 'LBL_RECORD_DETAILS') {
                Quoter_Js.loadLineItemsDetail(targetRecord, targetModule);
            }
        });

    }else if (targetView == 'Edit' && listModulesHandle.indexOf(targetModule) != -1) {
        if(sourceRecord == '' && sourceModule == '') {
            sourceRecord = returnrecord;
            sourceModule = returnmodule;
        }

        params = {
            module: 'Quoter',
            view: 'MassActionAjax',
            mode: 'getItemsEdit',
            record: targetRecord,
            current_module: targetModule,
            salesorder_id: salesorder_id,
            quote_id: quote_id,
            invoice_id: invoice_id,
            isDuplicate: isDuplicate,
            sourceRecord: sourceRecord,
            sourceModule: sourceModule
        };
        app.helper.showProgress();
        AppConnector.request(params).then(
            function (response) {
                app.helper.hideProgress();
                if (response.result.isActive == true) {
                    jQuery("[name='subtotal'], [name='total']").remove();
                    lineItemTab.html(response.result.html);
                    lineItemTab.css('table-layout', 'fixed');
                    jQuery('.lineItemContainer').css('margin', 0);
                    jQuery('#lineItemResult').html(response.result.html1);
                    jQuery('.lineitemTableContainer').append(response.result.html2);
                    var setting = response.result.setting;
                    var jsonSettings = JSON.stringify(setting);
                    var inputSettings = '<textarea type="hidden" class="input_settings">'+jsonSettings+'</textarea>';
                    if($('.input_settings').length == 0) {
                        $('body').append(inputSettings);
                    }
                    var totalSetting = response.result.totalSettings;
                    //add section dropdown
                    var sectionSettings = response.result.sectionSettings;
                    Quoter_Js.addSectionDropDown(sectionSettings);
                    Quoter_Js.addRunningSubTotalDropDown(totalSetting);
                    var quoterInstance = new Quoter_Js();
                    quoterInstance.columnSetting = setting;
                    quoterInstance.totalSetting = totalSetting;
                    quoterInstance.separator = response.result.separator;
                    quoterInstance.fixFieldName();
                    lineItemTab.find('tr.lineItemRow').each(function () {
                        Quoter_Js.fixWidthInput(jQuery(this));
                        quoterInstance.registerLineItemAutoComplete(jQuery(this));
                        vtUtils.applyFieldElementsView(jQuery(this));
                    });
                    quoterInstance._registerEvents();

                    Quoter_Js.registerEventForProductImages();
                    //thisInstance.updateParentValueForAllItems();
                    quoterInstance.calculateAllRunningSubTotalValue();
                    quoterInstance.calculateTotalValueByFormula();

                    if((sourceRecord!= '' && sourceModule != '')|| (sourceRecord == '' && targetModule =='PurchaseOrder' && (salesorder_id !='' || invoice_id !=''))) {
                        var listPrice = jQuery(document).find('[name^="listprice"]');
                        listPrice.trigger('change');
                    }
                }
            }
        );
    }else if(listModulesHandle.indexOf(relatedModule) != -1 && targetView == 'Detail' && targetMode == 'showRelatedList') {
        var relationEdit = jQuery('#listview-table').find('[name="relationEdit"]');
        $.each(relationEdit, function (key, val) {
            var focus = $(this);
            focus.find('.fa-pencil').on('click', function () {
                var dataUrl = focus.data('url');
                window.location.href = dataUrl;
            });
        });
    }
});
jQuery(document).ajaxComplete( function (event, request, settings) {
    var sPageURL = settings.url;
    var targetModule = '';
    var targetView = '';
    var targetRecord = '';
    var targetMode = '';
    var viewMode = '';
    var salesorder_id = '';
    var quote_id = '';
    var isDuplicate = '';
    var displayMode = '';
    var sourceRecord = '';
    var sourceModule = '';
    var returnrecord = '';
    var returnmodule = '';
    var relatedModule = '';
    sPageURL = sPageURL.replace('index.php?', '');
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == 'module') {
            targetModule = sParameterName[1];
        }
        else if (sParameterName[0] == 'view') {
            targetView = sParameterName[1];
        }
        else if (sParameterName[0] == 'record') {
            targetRecord = sParameterName[1];
        } else if (sParameterName[0] == 'salesorder_id') {
            salesorder_id = sParameterName[1];
        } else if (sParameterName[0] == 'quote_id') {
            quote_id = sParameterName[1];
        } else if (sParameterName[0] == 'mode') {
            targetMode = sParameterName[1];
        } else if (sParameterName[0] == 'isDuplicate') {
            isDuplicate = sParameterName[1];
        }else if (sParameterName[0] == 'requestMode') {
            viewMode = sParameterName[1];
        }else if (sParameterName[0] == 'displayMode') {
            displayMode = sParameterName[1];
        }else if (sParameterName[0] == 'sourceRecord') {
            sourceRecord = sParameterName[1];
        }else if (sParameterName[0] == 'sourceModule') {
            sourceModule = sParameterName[1];
        }else if (sParameterName[0] == 'returnrecord') {
            returnrecord = sParameterName[1];
        }else if (sParameterName[0] == 'returnmodule') {
            returnmodule = sParameterName[1];
        }else if (sParameterName[0] == 'relatedModule') {
            relatedModule = sParameterName[1];
        }
    }
    var listModulesHandle = ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'];
    if (targetView == 'Detail' && targetMode == 'showDetailViewByMode' && viewMode == 'full'&& displayMode == 'overlay' && listModulesHandle.indexOf(targetModule) != -1) {
            Quoter_Js.loadLineItemsDetail(targetRecord,targetModule);
        $('.modal-content').find('.editRelatedRecord').on('click', function () {
            var dataUrl = $(this).val();
            window.location.href = dataUrl;

        });
    }else if(listModulesHandle.indexOf(relatedModule) != -1 && targetView == 'Detail' && targetMode == 'showRelatedList') {
        var relationEdit = jQuery('#listview-table').find('[name="relationEdit"]');
        $.each(relationEdit, function (key, val) {
            var focus = $(this);
            focus.find('.fa-pencil').on('click', function () {
                var dataUrl = focus.data('url');
                window.location.href = dataUrl;
            });
        });
    }
    $('[data-toggle="popover"]').popover();
    //fix conflict with module VTETabs START
    var url = settings.data;
    if(typeof url == 'undefined' && settings.url) url = settings.url;
    if(Object.prototype.toString.call(url) =='[object String]') {
        if (url.indexOf('module=VTETabs') != -1 && url.indexOf('view=Edit') != -1 && url.indexOf('mode=showModuleEditView') > -1){
            var top_url = window.location.href;
            var params = app.convertUrlToDataParams(top_url);
            targetModule = params['module'];
            targetView = params['view'];
            if (targetView == 'Edit' && listModulesHandle.indexOf(targetModule) != -1) {
                if (sourceRecord == '' && sourceModule == '') {
                    sourceRecord = returnrecord;
                    sourceModule = returnmodule;
                }
                params = {
                    module: 'Quoter',
                    view: 'MassActionAjax',
                    mode: 'getItemsEdit',
                    record: targetRecord,
                    current_module: targetModule,
                    salesorder_id: salesorder_id,
                    quote_id: quote_id,
                    isDuplicate: isDuplicate,
                    sourceRecord: sourceRecord,
                    sourceModule: sourceModule
                };
                app.helper.showProgress();
                AppConnector.request(params).then(
                    function (response) {
                        app.helper.hideProgress();
                        if (response.result.isActive == true) {
                            var setting = response.result.setting;
                            var totalSetting = response.result.totalSettings;
                            //add section dropdown
                            var sectionSettings = response.result.sectionSettings;
                            var quoterInstance = new Quoter_Js();
                            quoterInstance.columnSetting = setting;
                            quoterInstance.totalSetting = totalSetting;
                            var lineItemTab = jQuery('#lineItemTab');
                            setTimeout(function() {
                                lineItemTab.find('tr.lineItemRow').each(function () {
                                    quoterInstance.registerLineItemAutoComplete(jQuery(this));
                                });
                            },3000);
                        }
                    }
                );
            }
        }
    }
    //fix conflict with module VTETabs END
});
$(document).ready(function(){
    $('#lineItemTab').css({'margin-top':'10px'});
});
