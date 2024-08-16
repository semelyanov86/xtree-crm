<div class="container-fluid" id="SignaturesContainer">
    <br>
    <div class="row">
        <div class="col-lg-6">
            <h4>{vtranslate('LBL_SIGNATURES_EDIT', $QUALIFIED_MODULE)}: {$RECORD_MODEL->getVariable()}</h4>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-12">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="Signatures">
                <input type="hidden" name="module" value="PDFMaker">
                <input type="hidden" name="mode" value="save">
                <input type="hidden" name="record" value="{$RECORD_MODEL->getRecord()}">
                <input type="hidden" name="type" value="accept">
                <div class="container-fluid container-edit">
                    <div class="row">
                        <div class="col-lg-5">{vtranslate('LBL_IMAGE', $QUALIFIED_MODULE)}</div>
                        <div class="col-lg-6">
                            <input class="form-control" type="file" name="image" accept=".png,.jpg">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-5">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</div>
                        <div class="col-lg-6">
                            <input class="form-control" type="text" name="name" value="{$RECORD_MODEL->get('name')}">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-5">{vtranslate('LBL_IMAGE_WIDTH', $QUALIFIED_MODULE)}</div>
                        <div class="col-lg-6">
                            <input class="form-control inputElement" type="text" name="width" value="{$RECORD_MODEL->get('width')}">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-5">{vtranslate('LBL_IMAGE_HEIGHT', $QUALIFIED_MODULE)}</div>
                        <div class="col-lg-6">
                            <input class="form-control inputElement" type="text" name="height" value="{$RECORD_MODEL->get('height')}">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-5"></div>
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-success">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                            <a href="javascript:history.back();" class="cancelLink">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>