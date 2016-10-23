<?php

require_once __DIR__.'/vendor/autoload.php';

$main = new \Core\Main();
$tables = $main->listTables();
if(isset($_POST['exportdata'])){
    $main->doDataExport();
}
if(isset($_POST['loaddata'])){
    $main->loadData();
}


if($main->getDataLoaded()){
    $fileheaders = $main->getFileHeader();
    $columns = $main->getColumns();

}

?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script   src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<form method="post" enctype="multipart/form-data">

<p>
    Document to Import : <input type="file" name="file">
</p>
<p>
    Table to Use : <select name="table" id="tables">
        <?php foreach ($tables as $key=>$value){

            echo "<option value='".$value."'>".$value."</option>";
        } ?>
    </select>
</p>
<p>
   <button type="submit" name="loaddata" value="1">load data</button>
</p>

</form>

<form method="post" enctype="multipart/form-data">
<h3>Map Fields</h3>
<div class="col-md-6">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Document Columns</th>
                <th>Action</th>
                <th>Table Fields</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(isset($fileheaders)){
                foreach ($fileheaders as $key => $value){
                    echo "<tr>";
                    echo "<td><input name='data[".$key."][doc_title]' type='hidden' value='".$value."'  />".$value."</td>";
                    echo "<td><select name='data[".$key."][relation]' class='relation' data-key='".$key."'><option value='no_relation'>No Relation</option><option value='direct'> => </option> <option value='foreign_key'> Foreign Key</option></select></td>";
                    echo "<td><select name='data[".$key."][columns]'>";
                    echo "<option>Select Related Field</option>";
                    foreach ($columns as $column){
                        echo "<option value='".$column."'>".$column."</option>";
                    }
                    echo "</select></td>";
                    echo "</tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>4
</div>
    <button name="exportdata" value="1" type="submit">Import Into Table</button>
</form>
<script>
    $( document ).ready(function() {
        var tables = $("#tables").html();
        $(".relation").change(function(){
            var dis = $(this);
           if(dis.val() ==  "foreign_key"){
               dis.after("<select name='data["+dis.data('key')+"][foreign_key_table]'>"+tables+"</select><input name='data["+dis.data('key')+"][foreign_key_display_field]' placeholder='Foreign Key Display Field' />");
           }
        });
    });
</script>