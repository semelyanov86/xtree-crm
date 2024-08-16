<script type='text/javascript'>
    var styleArray = {$styleArray|@json_encode};
    var colorRecordData = {$modelData|@json_encode};

    var colorizerCheckFktAlternative = {$colorizerCheckFktAlternative|@json_encode};
    var colorizerCheckFkt = {$colorizeFunctions|@json_encode};
    var colorizer_picklists = {$picklists|@json_encode};

    var COLORIZER_VERSION = '{$smarty.const.COLORIZER_VERSION}';
</script>
<script type='text/javascript'>
    {$extraJavaScript}
</script>
<script type='text/javascript'>
    initColorizer();
</script>
<script src="modules/Colorizer/resources/jquery.color.min.js?{$smarty.const.COLORIZER_VERSION}" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" media="all" href="modules/Colorizer/resources/xbbcode/xbbcode.css?{$smarty.const.COLORIZER_VERSION}">
<style type='text/css'>td.colorized { background-image:none; } td.colorized label { font-weight:inherit; } td.colorized a { color:inherit !important; text-decoration: underline; }</style>
