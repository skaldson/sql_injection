<?php
    require "db_dsn.php";
    require "table_class.php";
    
    $dbh = new PDO($dsn);

    $table_name = $_GET['table_name'];

    $query_column_name = "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}'";
    $query_table_info = "SELECT * FROM {$table_name}";
    $stmt_info = $dbh->prepare($query_table_info);
    $stmt_info->execute();

    $stmt_columns = $dbh->prepare($query_column_name);
    $stmt_columns->execute();

    $result_info = $stmt_info->fetchAll(PDO::FETCH_ASSOC);
    $result_columns = $stmt_columns->fetchAll(PDO::FETCH_ASSOC);
    echo "<table id='tabl1' style='border: solid 1px black; text-align: center'>";
    $names_str = '<tr><th>';
    $checkbox = '';
    $check_str = "<input type='checkbox' name='{$table_name}[]' value=";
    $label_start = "<label>";
    $label_end = "</label></br>";

    $column_amount = sizeof($result_columns);
    for($i=0;$i<$column_amount;$i++)
    {
        $temp = (string)$result_columns[$i]['column_name'];
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

<form target="blank" action="init_present.php" method="get">
    <?php echo $checkbox?>
    <input type="submit" value="submit this param">
</form>

<form>
    <input type="submit" value="refresh page">
</form> 


