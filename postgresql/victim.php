<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
    padding: 5px;
    text-align: left;
    }
</style>



<?php

    require "table_class.php";

    function get_table_data($dbh, $table_name)
    {
        $table_query = "SELECT * FROM {$table_name}";
        $stmt_table = $dbh->prepare($table_query);
        $stmt_table->execute();
        $table_data = $stmt_table->fetchAll(PDO::FETCH_ASSOC);

        return $table_data;
    }

    function table_exist($dbh, $sql_str)
    {
        $sql_literals = explode(' ', $sql_str);
        $victim_name = $sql_literals[1];

        $is_exist = "SELECT 'schema_name.{$victim_name}'::regclass";
        $stmt_exist = $dbh->prepare($is_exist);
        $stmt_exist->execute();
        $exist_result = $stmt_exist->fetchAll(PDO::FETCH_ASSOC);

        return $exist_result;
    }

    function make_table($dbh, $table_name, $before=1, $table_id=0)
    {
        $query_column_name = "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}'";
        $stmt_columns = $dbh->prepare($query_column_name);
        $stmt_columns->execute();
        $result_columns = $stmt_columns->fetchAll(PDO::FETCH_ASSOC);

        $column_amount = sizeof($result_columns);

        $table_data = get_table_data($dbh, $table_name);

        if($before)
            {$table_str = "<table id='table_data".$table_id."' style='float: left;'";}
        else    
            {$table_str = "<table id='table_data' style='float: right;'>";}
        
        $table_str = $table_str.'<tr><th>';

        for($i=0;$i<$column_amount;$i++)
        {
            $temp = (string)$result_columns[$i]['column_name'];
            $table_str = $table_str.$temp;
            if($i == (sizeof($result_columns))){
                $table_str = $table_str.'</th>';
                
            }
            else{
                $table_str = $table_str.'</th><th>';
            }
        }
        $table_str = $table_str.'</tr>';
        if(! (sizeof($table_data) == 0)){
            for($i=0;$i<sizeof($table_data);$i++){
                $table_str = $table_str.'<tr>';
                foreach($table_data[$i] as $k=>$v){
                    $table_str=$table_str.'<th>'.$v.'</th>';
                }
                $table_str = $table_str.'</tr>';
            }
    
            $table_str = $table_str."</table>";
            return $table_str;
        }
        
    }

    function make_universal_table($result)
    {
        $result_columns = array_keys($result[0]);
        
        $table_str = "<table id='universal_table' style='float: left;'".'<tr><th>';

        for($i=0;$i<sizeof($result_columns);$i++){
            $temp = $result_columns[$i];
            $table_str = $table_str.$temp;
            if($i == (sizeof($result_columns)))
                {$table_str = $table_str.'</th>';}
            else
                {$table_str = $table_str.'</th><th>';}
        }
        $table_str = $table_str.'</tr>';
        
        for($i=0;$i<sizeof($result);$i++){
            $table_str = $table_str.'<tr>';
            foreach($result[$i] as $k=>$v){
                $table_str=$table_str.'<th>'.$v.'</th>';
            }
            $table_str = $table_str.'</tr>';
        }

        $table_str = $table_str."</table>";
    
        return $table_str;
    }

    function init_victim($object_name, $keyword, $database=0, $id=0)
    {
        $database = 'kolenapost';
        $table_str = "<table style='width:75%' id='table_victim".$id."'>";
        $table_message = ['Victim', 'Attack Type'];
        if(!$database)
            {$init_info = [$database.'.'.$object_name, $keyword];}
        else
            {{$init_info = ['database:'.' '.$object_name, $keyword];}}
        

        for($i=0;$i<sizeof($table_message);$i++){
            $table_str = $table_str.'<tr>';
            $table_str = $table_str.'<th>'.$table_message[$i].'</th>';
            $table_str = $table_str.'<td>'.$init_info[$i].'</td>';
            $table_str = $table_str.'</tr>';
        }

        return $table_str;
    }

    function make_victim_table($table_name, $keyword, $difference, $id=0)
    {
        $table_str = init_victim($table_name, $keyword);
        $damage_message = ['Column/old value', 'Column/new value'];

        for($i=0;$i<sizeof($difference);$i++){
            foreach($difference[$i] as $k=>$v){
                $table_str = $table_str.'<tr>';
                $table_str = $table_str.'<th>'.$damage_message[0].'</th>';
                $table_str = $table_str.'<td>'.$table_name.'.'.$k.'/'.$v[0].'</td>';
                $table_str = $table_str.'<th>'.$damage_message[1].'</th>';
                $table_str = $table_str.'<td>'.$table_name.'.'.$k.'/'.$v[1].'</td>';
                $table_str = $table_str.'</tr>';
            }
    }
        $table_str = $table_str.'<tr>'.'</table>';
        return $table_str;
    }

    function which_changes_in_table($before, $after)
    {
        $changes = array();

        $counter = 0;
        for($i=0;$i<sizeof($before);$i++)
        {
            foreach($before[$i] as $k=>$v){
                if($before[$i][$k] != $after[$i][$k]){
                    $changes[$counter++][$k] = [$before[$i][$k], $after[$i][$k]];
                }
            }
        }
        return $changes;
    }

    function make_drop_victim($tables_name, $keyword, $succesfull=1, $obj=['Table(s) Destroyed', 'Unsuccesfully'], $db=0, $id=0)
    {
            $table_str = init_victim($tables_name, $keyword, $db);
            if($succesfull){
                $table_str = $table_str.'<tr>'.'<th>'.'Atack Status'.'</th>';
                $table_str = $table_str.'<td>'.$obj[0].'</td>'.'</tr>';
            }
            else{
                $table_str = $table_str.'<tr>'.'<th>'.'Atack Status'.'</th>';
                $table_str = $table_str.'<td>'.$obj[1].'</td>'.'</tr>'.'</table>';
            }
            return $table_str;
    }

    function drop_statement($dbh, $sql_str)
    {
        $sql_str = trim($sql_str);
        $query_arr = explode(' ', $sql_str);
        $many_or_one = $query_arr[1];
        if($many_or_one){
            if(in_array(strtoupper($many_or_one), ['TABLE', 'TABLES'])){
                $tables_name = $query_arr[sizeof($query_arr) - 1];
                $stmt_drop = $dbh->prepare($sql_str);
                if(!$stmt_drop->execute()){
                    $succesfull = $stmt_drop->errorInfo();
                }
                if(!$succesfull){
                    $result['drop'] = (make_drop_victim($tables_name, 'DROP'));
                }
                else{
                    $result['drop'] = (make_drop_victim($tables_name, 'DROP', 0));
                }
            }
            else if(in_array(strtoupper($many_or_one), ['DATABASE', 'DATABASES'])){
                $databases_name = $query_arr[sizeof($query_arr) - 1];
                $stmt_drop = $dbh->prepare($sql_str);
                if(!$stmt_drop->execute()){
                    $succesfull = $stmt_drop->errorInfo();
                }
                if(!$succesfull){
                    $result['drop'] = (make_drop_victim($databases_name, 'DROP',1, ['Database(s) DESTROYED', 'Unsuccesfully'], 1));
                }
                else{
                    $result['drop'] = (make_drop_victim($databases_name, 'DROP', 0, ['Database(s) DESTROYED', 'Unsuccesfully'], 1));
                }
            }
        }
        else{
            $result['error'] = 'Inncorrect query';
        }
        return $result;
    }

    function you_fucking_victim($dbh, $sql_str)
    {
        $result = array();
        $sql_literals = explode(' ', $sql_str);
        $sql_statement = $sql_literals[0];

        $victim_name = $sql_literals[1];

        $table_exist = table_exist($dbh, $sql_str);
        if($table_exist){

            $result['no_values'] = "No such table `{$victim_name}`";

            return $result;
        }

        if($sql_statement == 'DROP'){
            $result = (drop_statement($dbh, $sql_str));

            return $result;
        }

        if($sql_statement == 'UPDATE'){
            $before_table = make_table($dbh, $victim_name);
            $before_table_data = get_table_data($dbh, $victim_name);

            // var_dump($before_table_data);

            $dbh->beginTransaction();

            $stmt = $dbh->exec($sql_str);
            
            if($stmt){
                $after_table = make_table($dbh, $victim_name, 0, $table_id=1);
                $after_table_data = get_table_data($dbh, $victim_name);
                $difference = which_changes_in_table($before_table_data, $after_table_data);
                
                $victim_table = make_victim_table($victim_name, $sql_statement, $difference);

                $result['update'] = [$victim_table, $after_table, $before_table];
                $dbh->rollBack();
            }
            else{
                $result['error'] = ($dbh->errorInfo())[2];
                return $result;
            }
        }
        return $result;
    }

    function select_statement($dbh, $sql_str)
    {
        $result = array();
        $sql_str = trim($sql_str);
        $stmt = $dbh->prepare($sql_str);
        if(!$stmt->execute()){
            $error_message = $stmt->errorInfo();
        }
        
        if($error_message){
            $result['error'] = (string)$error_message[2];
            return $result;
        }
        else{
            $select_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($select_result){
                $result['select'] = (make_universal_table($select_result));
                return $result;
            }
            else{
                $result['no_values'] = 'No values';
                return $result;
            }
        }
        return $result;
    }

    // function output_statement($dbh, $sql_str)
    // {
    //     $result = array();
    //     $sql_str = trim($sql_str);
    //     $stmt = $dbh->prepare($sql_str);
    //     if(!$stmt->execute()){
    //         $error_message = $stmt->errorInfo();
    //     }
        
    //     if($error_message){
    //         $result['error'] = (string)$error_message[2];
    //         return $result;
    //     }
    //     else{
    //         $show_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         if($show_result){
    //             $result['show'] = (make_universal_table($show_result));
    //         }
    //         else{
    //             $result['no_values'] = 'No values';
    //         }
            
    //     }

    //     return $result;
    // }

    function output_table($dbh, $sql_str)
    {
        $result = array();
        $sql_literals = explode(' ', $sql_str);
        $sql_statement = $sql_literals[0];

        if($sql_statement == 'SELECT'){
            $result = select_statement($dbh, $sql_str);
        }
        // if($sql_statement == 'SHOW'){
        //     $result = output_statement($dbh, $sql_str);
        // }

        return $result;
    }
    

    function connect_db()
    {
        $host = '127.0.0.1';
        $db = 'kolenapost';
        $username = 'wardgib';
        $password = '';
        $dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";
        
        $dbh = new PDO($dsn);

        return $dbh;
    }

    function analyze_sql($sql_str)
    {   
        $dbh = connect_db();

        $victim_words = ['UPDATE', 'DROP', 'ALTER'];
        $output_words = ['SELECT', 'SHOW'];
        $result = array();

        $sql_str = trim($sql_str);
        $temp = strtoupper((explode(' ', $sql_str)[0]));
        if(in_array($temp, $victim_words)){
            $result = you_fucking_victim($dbh, $sql_str);
        }
        if(in_array($temp, $output_words)){
            $result = output_table($dbh, $sql_str);
        }
        return $result;
    }

?>

