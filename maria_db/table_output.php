<?php
    require 'table_class.php';
    require 'db_connect.php';
    
    
    $dbh=MySQLDatabase::connect("kolenapost","root","");
    

    $table_name=$_GET['table_name'];

    $query_table_info = "SELECT * FROM {$table_name}";
    $query_column_name = "SHOW COLUMNS FROM {$table_name}";

    $stmt_info = $dbh->prepare($query_table_info);
    $stmt_info->execute();

    $stmt_columns = $dbh->prepare($query_column_name);
    $stmt_columns->execute();

    $result_info = $stmt_info->fetchAll(PDO::FETCH_ASSOC);
    $result_columns = $stmt_columns->fetchAll(PDO::FETCH_ASSOC);

    echo "<table id='main_table' style='border: solid 1px black; text-align: center'>";
    $names_str = '<tr><th>';
    $checkbox = '';
    $check_str = "<input type='checkbox' id='check' name='{$table_name}[]' value=";
    $label_start = "<label>";
    $label_end = "</label></br>";

    $column_amount = sizeof($result_columns);
    for($i=0;$i<$column_amount;$i++)
    {
        $temp = (string)$result_columns[$i][Field];
        $names_str = $names_str.$temp;
        if($i == (sizeof($result_columns))){
            $names_str = $names_str.'</th>';
            
        }
        else{
            $checkbox = $checkbox.$check_str.$temp.'>';
            $checkbox = $checkbox.$label_start.$temp.$label_end;
            $names_str = $names_str.'</th><th>';
        }
    }
    $names_str = $names_str.'</tr>';
    print($names_str);

    foreach(new TableRows(new RecursiveArrayIterator($result_info)) as $k=>$v)
    {
        echo $v;
    }
    echo "</table>";
    
?>
<script>
function removeId(id)
{
    var Element = document.getElementById(id);

    if(Element)
        {Element.parentNode.removeChild(Element);}
}

function cleanTable()
{
    removeId("main_table");
    removeId("check");
}

document.getElementById("GO").onclick = cleanTable;


</script>
<button onclick="history.go(0);">Refresh Page</button>


<form target="blank" action="first_step.php" method="get">
    <?php echo $checkbox?>
    <input type="submit" value="submit this param" id='GO' onclick="cleanTable();">
</form>




