<div class="container-fluid" id="SignaturesContainer">
    <br>
    <div class="row">
        <div class="col-lg-6">
            <h4>{vtranslate('LBL_SIGNATURES', $QUALIFIED_MODULE)}</h4>
        </div>
        <div class="col-lg-6 text-right">
            <a class="btn btn-default" href="index.php?module=PDFMaker&view=Signatures&mode=edit"><i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_SIGNATURE', $QUALIFIED_MODULE)}</a>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>{vtranslate('LBL_IMAGE', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_VARIABLE', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_IMAGE_WIDTH', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_IMAGE_HEIGHT', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$RECORDS item=RECORD_MODEL key=RECORD_ID}
                        <tr>
                            <td><img src="{$RECORD_MODEL->getImage()}" alt=""></td>
                            <td>{$RECORD_MODEL->get('name')}</td>
                            <td>{$RECORD_MODEL->getVariable()}</td>
                            <td>{$RECORD_MODEL->get('width')}</td>
                            <td>{$RECORD_MODEL->get('height')}</td>
                            <td>{$RECORD_MODEL->get('type')}</td>
                            <td>
                                <a href="index.php?module=PDFMaker&view=Signatures&mode=edit&record={$RECORD_ID}"><i class="fa fa-pencil"></i></a>
                                &nbsp;&nbsp;
                                <a href="index.php?module=PDFMaker&action=Signatures&mode=delete&record={$RECORD_ID}" class="deleteSignature"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>