<?php
require_once 'autoload_wf.php';
require_once 'lib/Workflow/Importer.php';

if (empty($_GET['step'])) {
    $importStep = 1;
} else {
    $importStep = intval($_GET['step']);
}

if (!function_exists('lineCount')) {
    function lineCount($file)
    {
        $linecount = 0;
        $handle = fopen($file, 'r');

        while (!feof($handle)) {
            if (fgets($handle) !== false) {
                ++$linecount;
            }
        }
        fclose($handle);

        return $linecount;
    }
}
?>
<link rel="stylesheet" href="modules/Workflow2/adminStyle.css" type="text/css" media="all" />
<h2 style="margin-left:40px;">Workflow Designer &raquo; Import</h2>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="98%">
<tr>
       <td valign="top"></td>
        <td width="100%" valign="top" style="padding: 10px;" class="showPanelBg">
            <br>
            <div class="settingsUI" style="width:95%;padding:10px;margin-left:10px;">
            <h3>Import Step <?php echo $importStep; ?> / 3</h3><br>
<?php
                if ($importStep == 1 && isset($_FILES['importfile'])) {
                    if (is_writable($root_directory . '/test/')) {
                        @mkdir($root_directory . '/test/Workflow2/', 0o777, true);

                        $filePath = tempnam($root_directory . '/test/Workflow2/', 'Importer');
                        if (is_uploaded_file($_FILES['importfile']['tmp_name'])) {
                            move_uploaded_file($_FILES['importfile']['tmp_name'], $filePath);
                        } else {
                            echo 'ERROR: During Upload of File';

                            return;
                        }
                    } else {
                        echo 'ERROR: test directory not writable';

                        return;
                    }

                    $fp = fopen($filePath, 'r');

                    echo '<p><strong>Vorschau auf die Datei:</strong></p>';
                    echo "<table style='margin:5px; border:1px solid #ccc;' cellspacing='0'>";
                    for ($a = 0; $a < 5; ++$a) {
                        echo '<tr>';

                        $data = fgetcsv($fp, 0, $_POST['import_delimiter']);
                        if ($data === false) {
                            break;
                        }

                        foreach ($data as $field) {
                            echo "<td style='padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;'>" . htmlentities($field) . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</table>';

                    $hash = md5(microtime());
                    $_SESSION['import_' . $hash] = ['filePath' => $filePath, 'position' => 0, 'delimiter' => $_REQUEST['import_delimiter'], 'total' => lineCount($filePath), 'workflow' => $_REQUEST['exec_this_workflow']];

                    $data = [];
                    echo "<a href='index.php?module=Workflow2&action=Importer&step=2&process=" . $hash . "'>start Import</a>";
                } elseif ($importStep == 2) {
                    if (!isset($_SESSION['import_' . $_REQUEST['process']])) {
                        echo 'ERROR: import process not exist!';

                        return;
                    }

                    ?>
                    <div id="import_progress"></div>
                    <script type="text/javascript">
                        var importHash = '<?php echo $_REQUEST['process']; ?>';
                        var currentExecId = false;
                        var totalRows = <?php echo lineCount($_SESSION['import_' . $_REQUEST['process']]['filePath']); ?>;
                        function startImport() {
                            jQuery.post("index.php?module=Workflow2&action=Workflow2Ajax&file=AjaxImporter", {importHash: importHash, currentExecID: currentExecId}, function(response) {

                                if(response.ready == true) {
                                    window.location.href = "index.php?module=Workflow2&action=Importer&step=3&process=<?php echo $_REQUEST['process']; ?>";
                                } else {
                                    jQuery("#import_progress").html(response.done + " of " + totalRows + " Rows done!");
                                    currentExecId = response.execID;
                                    window.setTimeout("startImport()", 1000);
                                }

                            }, "json");
                        }
                        jQuery(function() {
                            startImport();
                        });
                    </script>
                    <?php
                } elseif ($importStep == 3) {
                    if (!isset($_SESSION['import_' . $_REQUEST['process']])) {
                        echo 'ERROR: import process not exist!';

                        return;
                    }
                    $data = $_SESSION['import_' . $_REQUEST['process']];

                    @unlink($data['filePath']);
                    echo '<p>Import finished</p>';
                }
?>
        </td>
    </tr>
    </table>