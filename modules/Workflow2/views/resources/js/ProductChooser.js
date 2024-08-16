var productCounter = 1;
if(typeof productCache == 'undefined') {
    var productCache = {};
}
if(typeof Vtiger_Edit_Js == 'undefined') {
    var Vtiger_Edit_Js = function() {};
}
function productChanged(fieldid, productid, productCounter) {
    if(productid == "individual") {
        jQuery("." + fieldid + "_taxes").show();
        var ele = jQuery("#" + fieldid)[0];

        var html = createTemplateTextfield(ele.name.replace("productid", "productid_individual"), ele.id.replace("productid", "productid_individual"), "");

        jQuery(".select2-container.productSelect").remove();

        jQuery(ele).replaceWith(html);
    } else {

        loadProductInfo(productid, productCounter);
        /*
         jQuery("." + fieldid + "_taxes").hide();
         jQuery("." + fieldid + "_taxes :checkbox").attr('checked', false);
         jQuery("." + fieldid + "_taxes input[type=text]").attr("disabled", "disabled");
         // jQuery("." + fieldid + "_taxes")


         jQuery.each(taxlist[productid], function(index, value) {
         jQuery("." + fieldid + "_taxes#" + fieldid + "_tax" + value.taxid).show();
         jQuery("." + fieldid + "_taxes#" + fieldid + "_tax" + value.taxid + " :checkbox").attr('checked', true);
         jQuery("." + fieldid + "_taxes#" + fieldid + "_tax" + value.taxid + " input[type=text]").removeAttr("disabled");
         });


         jQuery("#product_" + productCounter + "_description").val(productList[productid]["description"]);
         jQuery("#product_" + productCounter + "_unitprice").val(productList[productid]["unit_price"]);*/
    }
}

function loadProductInfo(product_id, productCounter) {
    if(typeof productCache[product_id] != 'undefined') {
        setProductInfo(productCache[product_id], productCounter);
    } else {
        jQuery.post('index.php', {module:'Workflow2', 'action':'GetProductData', 'product_id':product_id}, function(response) {
            productCache[product_id] = response;
            setProductInfo(productCache[product_id], productCounter);
        }, 'json');
    }
}
function setProductInfo(productData, productCounter) {
    var fieldid = 'product_' + productCounter + '_productid';
    jQuery("." + fieldid + "_taxes").hide();
    jQuery("." + fieldid + "_taxes :checkbox").attr('checked', false);
    jQuery("." + fieldid + "_taxes input[type=text]").attr("disabled", "disabled");

    jQuery.each(productData.tax, function(index, value) {
        jQuery("." + fieldid + "_taxes#" + fieldid + "_" + index).show();
        jQuery("." + fieldid + "_taxes#" + fieldid + "_" + index + " :checkbox").attr('checked', true);
        jQuery("." + fieldid + "_taxes#" + fieldid + "_" + index + " input[type=text]").val(parseFloat(value.percentage)).removeAttr("disabled");
    });

    jQuery("#product_" + productCounter + "_comment").val(productData['data']["description"]);
    jQuery("#product_" + productCounter + "_unitprice").val(productData['data']["unit_price"]);
}

function getProductSelect(fieldName, fieldId, product, productCounter) {
    if(typeof product.productid_individual != "undefined" && product.productid_individual.length > 0) {
        var html = createTemplateTextfield(fieldName.replace("productid", "productid_individual"), fieldId.replace("productid", "productid_individual"), product.productid_individual);

        return html;
    } else {
        if(product === false) {
            selected = -1;
        } else {
            selected = product.productid;
        }

        var html = "<input type='hidden' class='productSelect' value='" + (selected!=-1?selected:'') + "' onchange='productChanged(\"" + fieldId + "\", this.value, " + productCounter + ");' style='' name='" + fieldName + "' id='" + fieldId + "'>";
        /*
         var html = "<select class='chzn-select span8' onchange='productChanged(\"" + fieldId + "\", this.value, " + productCounter + ");' style='' name='" + fieldName + "' id='" + fieldId + "'>";
         html += '<option value="">' + MOD.LBL_CHOOSE + '</option>';
         jQuery.each(productList, function(index, value) {
         html += '<option value="' + value.productid + '" ' + (selected !== undefined && selected == value.productid?"selected='selected'":"") + '>' + value.productname + '</option>';
         });
         html += '<option value="individual">+++ ' + MOD.LBL_SELECT_INPUT_INDIVIDUAL_VALUE + '</option>';

         html += "</select>";*/
        return html;
    }
}
function removeProduct(productCounter) {
    jQuery("#productChooser_" + productCounter).remove();
}
function addProduct(product) {

    html = "<div class='productChooserContainer' id='productChooser_" + productCounter + "'>";
    html += '<div class="row">';
    html += "<div  class='col-md-9'>";
    //        html += "<div><label style='width: 60px;display: inline-block;vertical-align: middle;'>Product:</label><span class='productChooserContainer_productid' id='productID_display_" + productCounter + "'>Choose Product</span></div><br>";
        html += "<div style='margin-bottom:5px;overflow: hidden;'>" + getProductSelect("task[product][" + productCounter + "][productid]","product_" + productCounter + "_productid",(product !== undefined?product:false), productCounter) + "</div>";

        html += "<div style='overflow:hidden;margin-bottom:5px;'>";
            html += "<div style='width:300px;float:left;line-height:28px;'><label style='width: 80px;margin:0;display: inline-block;vertical-align: middle;'>" + app.vtranslate('Quantity') + ":</label><span style='display:inline-block;max-width:150px;'>" + createTemplateTextfield("task[product][" + productCounter + "][quantity]", "product_" + productCounter + "_quantity",(product !== undefined?product.quantity:""), {width:'150px',title:MOD.LBL_DOUBLE_CLICK_TO_INCREASE_SIZE}) + "</span></div>";
            html += "<div style='width:250px;float:left;line-height:28px;'><label style='width: 70px;margin:0;display: inline-block;vertical-align: middle;'>" + app.vtranslate('Unit Price') + ":</label><span style='display:inline-block;max-width:150px;'>" + createTemplateTextfield("task[product][" + productCounter + "][unitprice]", "product_" + productCounter + "_unitprice",(product !== undefined?product.unitprice:""), {width:'150px',title:MOD.LBL_DOUBLE_CLICK_TO_INCREASE_SIZE}) + "</span></div>";
        html += "</div>";

        html += "<label style='width: 80px;display: inline-block;vertical-align: middle;'>" + app.vtranslate('Product Description') + ":</label><br/>";
        html += createTemplateTextarea("task[product][" + productCounter + "][comment]", "task_product_" + productCounter + "_comment", (product !== undefined?product.comment:""), {height:'70px'});

    html += "</div>";

    html += "<div class='col-md-3'>";
        html += "<div class='buttonbar' style='text-align:right;margin:0 0 10px 0;'><input type='button' class='btn btn-danger' onclick='removeProduct(" + productCounter + ");' value='"+MOD.LBL_REMOVE_RECORD+"' /></div>";

        html += "<span>Discount:</span><select onchange='jQuery(\"#discount_value_" + productCounter + "_container\").css(\"display\",this.value==\"\"?\"none\":\"block\")' name='task[product][" + productCounter + "][discount_mode]'><option value=''>-</option><option value='amount' " + (typeof product != "undefined" && product.discount_mode=='amount'?'selected="selected"':'') + ">Amount</option><option value='percentage' " + (typeof product != "undefined" && product.discount_mode=='percentage'?'selected="selected"':'') + ">Percentage</option></select>";
        html += "<div id='discount_value_" + productCounter + "_container' style='display:" + (typeof product != "undefined" && product.discount_mode==""?"none":"") + ";'>Discount Value:<br>";
        html += createTemplateTextfield("task[product][" + productCounter + "][discount_value]", "product_" + productCounter + "_discount_value",(product !== undefined?product.discount_value:""), {width:'80px'})
        html += "</div>";
        jQuery.each(availTaxes, function(index, value) {
            html += "<div style='overflow:hidden;' class='product_" + productCounter + "_productid_taxes' id='product_" + productCounter + "_productid_tax" + value.taxid + "'>";
            html += "<div style='display:inline-block;'><span style='block;float:left;width:80px;'><input type='checkbox' id='' " + (typeof product !== "undefined" && product["tax" + value.taxid + "_enable"]=="1"?"checked='checked'":"") + " name='task[product][" + productCounter + "][tax" + value.taxid + "_enable]' value='1' onclick='if(!jQuery(this).prop(\"checked\")) { jQuery(\"#product_" + productCounter + "_tax" + value.taxid + "\").attr(\"disabled\",\"disabled\");} else { jQuery(\"#product_" + productCounter + "_tax" + value.taxid + "\").removeAttr(\"disabled\"); } '>"+ value.taxlabel + ":</span></div>";
            html += '<div style="display:inline-block;width:150px;">' + createTemplateTextfield("task[product][" + productCounter + "][tax" + value.taxid + "]", "product_" + productCounter + "_tax" + value.taxid + "",(product !== undefined && typeof product["tax" + value.taxid] !== "undefined"?product["tax" + value.taxid]:value.percentage), {width:'120px', disabled:typeof product === "undefined" || product["tax" + value.taxid + "_enable"] != "1"}) + '</div>';
            html += "</div>";
        });

    html += "</div>";
    html += '</div>';

    if(QUOTER !== false) {
        html += '<h4>Item Details Customizer</h4>';
        var selectHTML = '<select name="task[product][' + productCounter + '][section_value]">';
        selectHTML += '<option value=""></option>';

        jQuery.each(QUOTER.sections, function(fieldName, sectionHead) {
            selectHTML += '<option value="' + sectionHead + '" ' + (product !== undefined && product['section_value'] == sectionHead?'selected="selected"':"") + '>' + sectionHead + '</option>';
        });
        selectHTML += '</select>';
        html += "<div class='QuoterCustomField' style='line-height:28px;'><label style='width: 250px;margin:0;display: inline-block;vertical-align: middle;'>Add Section Head before Product</label>" + selectHTML + "</div>";

/*        var selectHTML = '<select name="task[product][' + productCounter + '][running_item_value]">';
        selectHTML += '<option value=""></option>';
        jQuery.each(QUOTER.runningSubtotals, function(totalKey, sectionHead) {
            selectHTML += '<option value="' + totalKey + '"' + (product !== undefined && product['running_item_value'] == totalKey?'selected="selected"':"") + '>' + sectionHead.fieldLabel + '</option>';
        });
        selectHTML += '</select>';
        */
        html += "<div class='QuoterCustomField' style='line-height:28px;'><label style='width: 250px;margin:0;display: inline-block;vertical-align: middle;'>Add running Total After Product</label>" + selectHTML + "</div>";

        additionalProductFields['section_value'] = false;
        //additionalProductFields['running_item_value'] = false;
    }

    if(jQuery.isEmptyObject(additionalProductFields) == false) {
        html += '<h4>Additional Fields</h4>';

        jQuery.each(additionalProductFields, function(fieldName, fieldData) {
            if(fieldData != false) {
                html += "<div class='ProductChooserCustomField row' style='line-height:28px;'><label class='col-md-3' style='margin-bottom:0;vertical-align: middle;'>" + app.vtranslate(fieldData.label) + ":</label><div class='col-md-9'>" + createTemplateTextfield("task[product][" + productCounter + "][" + fieldName + "]", "product_" + productCounter + "_" + fieldName + "",(product !== undefined?product[fieldName]:"")) + "</div></div>";
            }
        });
    }


    html += "</div>";
    html += '<script type="text/javascript">initProductChooseSelection(' + productCounter + ');</script>';

    jQuery("#product_chooser").append(html);

    if(typeof product != 'undefined' && typeof product.productid_individual  == 'undefined') {
        jQuery(".product_" + productCounter + "_productid_taxes").hide();
    }

    if(typeof product !== "undefined" && typeof product.productid !== "undefined" && product.productid != "" && product.productid != -1) {
        // jQuery("." + fieldid + "_taxes")
        console.log(productCache);
        jQuery.each(productCache[product.productid].tax, function(index, value) {
            jQuery(".product_" + productCounter + "_productid_taxes#product_" + productCounter + "_productid_" + index).show();
        });

    }

    productCounter++;
}
function initProductChooseSelection(productCounter) {
    jQuery("#productChooser_" + productCounter + " .productSelect").select2({
        placeholder: "search for a Product/Service",
        minimumInputLength: 1,
        width:'100%',
        initSelection: function (element, callback) {
            if(typeof jQuery(element).val() == 'undefined') {
                callback();
                return;
            }

            callback({
                id: jQuery(element).val(),
                text: productCache[jQuery(element).val()]['label']
            });
        },
        query: function (query) {
            var data = {
                query: query.term,
                page: query.page,
                pageLimit: 25
            };

            jQuery.post("index.php?module=Workflow2&action=ProductChooser", data, function (results) {
                if(typeof results.results == 'undefined') {
                    var results = { results:[] };
                }
                results.results.push({id:'individual',text:'+++ ' + MOD.LBL_SELECT_INPUT_INDIVIDUAL_VALUE});
                query.callback(results);
            }, 'json');

        }
    });
}

jQuery(function() {
    var globalValuesEl = jQuery("#InventoryGlobalValues");
    if(globalValuesEl.length > 0) {
        var html = "";
        html += "<fieldset style='float:left;width:40%;border:1px solid #eee;padding:5px;'><legend style='font-size:12px;margin-bottom:0;border-bottom:none;max-width:50%;'>" + MOD["LBL_GROUP_TAX_IF_ENABLED"] + "</legend>";
        jQuery.each(availTaxes, function(index, value) {
            html += "<div style='overflow:hidden' class='global_taxes' id='global_tax" + value.taxid + "_container'>";
            html += "<span><span style='display:block;float:left;width:80px;'>"+ value.taxlabel + ":</span></span>";
            html += '<div style="width:200px;" class="pull-right">' + createTemplateTextfield("task[global][tax" + value.taxid + "_group_percentage]", "global_tax" + value.taxid + "",(global_values !== null && typeof global_values["tax" + value.taxid + "_group_percentage"] !== "undefined"?global_values["tax" + value.taxid + "_group_percentage"]:value.percentage), {width:'150px'}) + '</div>';
            html += "</div>";
        });
        html += "</fieldset>";

        html += "<fieldset style='float:right;width:40%;border:1px solid #eee;padding:5px;'><legend style='font-size:12px;margin-bottom:0;border-bottom:none;max-width:50%;'>" + MOD["LBL_SHIPPING_TAX"] + "</legend>";
        jQuery.each(availTaxes, function(index, value) {
            html += "<div style='overflow:hidden' class='global_taxes' id='global_sh_tax" + value.taxid + "_container'>";
            html += "<span><span style='display:block;float:left;width:80px;'>"+ value.taxlabel + ":</span></span>";
            html += '<div style="width:200px;" class="pull-right">' + createTemplateTextfield("task[global][tax" + value.taxid + "_sh_percent]", "global_sh_tax" + value.taxid + "",(global_values !== null && typeof global_values["tax" + value.taxid + "_sh_percent"] !== "undefined"?global_values["tax" + value.taxid + "_sh_percent"]:value.percentage), {width:'200px'}) + "</div>";
            html += "</div>";
        });
        html += "</fieldset>";

        globalValuesEl.html(html);
    }

    if(oldTask != null && selectedProducts !== undefined && selectedProducts !== null) {
        jQuery.each(selectedProducts, function(index, value) {
            addProduct(value);
        });
    }
});