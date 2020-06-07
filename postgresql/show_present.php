<?php

    require "db_dsn.php";
    require "victim.php";

    $dbh = new PDO($dsn);

    function is_restrict($symbol)
    {
        $restrict_symbols = [';'];

        if(in_array($symbol, $restrict_symbols)){
            return true;
        }
        else{
            return false;
        }
    }

    function store_part($index, $str_obj)
    {
        $result = '';
        for($i=$index;$i<strlen($str_obj);$i++){
            $result = $result.$str_obj[$i];
        }

        return $result;
    }

    $restrict = [';'];
    $sql_operators = ['SELECT', 'DROP', 'OR', 'AND', 'SHOW', 'UNION', 'UPDATE'];
    $compare_operator = ['>', '<', '!', '='];

    $global_arr = $_GET['result'];
    $init_values = explode('"', $global_arr);


    $temp_arr = array_diff($init_values, array('{'));
    $temp_arr = array_diff($temp_arr, array('}'));
    $temp_arr = array_diff($temp_arr, array(':'));
    $temp_arr = array_diff($temp_arr, array(','));

    $new_arr = array();
    $counter = 0;
    for($i=0;$i<sizeof($init_values);$i++){
        if($temp_arr[$i] == NULL){
            continue;
        }
        else{
            $new_arr[$counter++] = $temp_arr[$i];
        }
    }
    $table_name = $new_arr[1];
    
    $real_parameters = array();
    for($i=1;$i<sizeof($new_arr) - 1;$i++){
        if($i%2 == 0){
            $temp_arr = explode(' ', $new_arr[$i+1]);
            $new_value = '';
            $arr_size = sizeof($temp_arr);
            $sql_part = '';
            $value_part = '';
            for($j=0;$j<$arr_size;$j++){
                $is_sql = in_array(strtoupper($temp_arr[$j]), $sql_operators);
                if($is_sql){
                    for($k=$j;$k<$arr_size;$k++)
                        {$sql_part = $sql_part.$temp_arr[$k].' ';}
                    break;
                }
                else{
                    $value_part = $value_part.$temp_arr[$j].' ';
                }
            }
            $template = $value_part;
            if( ((strpos($value_part, ';')) !== false)){
                $value_part = trim($value_part);
                $restrict = ';';
                $value_part = explode(';', $value_part);
                $before = (str_replace(' ','', $value_part[0]));
                if($value_part[sizeof($value_part) - 1])
                {
                    $after = $value_part[sizeof($value_part) - 1];
                    if(is_numeric($before))
                        {$value_part = $before.' '.$restrict." '{$after}'".' ';}
                    else
                        {$value_part = "'{$before}'".' '.$restrict." '{$after}'".' ';}

                }
                else{
                    if(is_numeric($before))
                        {$value_part = $before.' '.$restrict.' ';}
                    else
                        {$value_part = "'{$before}'".' '.$restrict.' ';}
                }
            }
            else{
                if(is_numeric(explode(' ', $template)[0])){
                    $value_part = $template;
                }
                else{
                    $value_part = (str_replace(' ','', $template));
                    $value_part = "'{$value_part}'";
                }
            }
        $new_value = $new_value.$value_part.' '.$sql_part;
        $real_parameters[$new_arr[$i]] = $new_value;
        }
    }


    end($real_parameters);
    $ok_next = explode(';',$real_parameters[key($real_parameters)]);

    if(sizeof($ok_next) > 1){
        $query_array = array();
        for($i=1;$i<sizeof($ok_next);$i++){
            $query_array[$i] = $ok_next[$i];
        }
        end($real_parameters);
        $real_parameters[key($real_parameters)] = $ok_next[0];
    }
    $condition_kernel = '';
    $counter = 0;
    foreach($real_parameters as $key => $value){
        $sym_part = '';
        $sql_part = '';
        if($value[0] == "'" && in_array($value[1], $compare_operator)){
            $temp_arr = explode("'", $value);
            if($temp_arr[2]){
                for($i=2;$i<sizeof($temp_arr);$i++)
                    {$sql_part = $sql_part.$temp_arr[$i].' ';}
            }
            $temp_str = $temp_arr[1];
            $other_part = '';
            $two_symbol = in_array($temp_str[0], $compare_operator) && in_array($temp_str[1], $compare_operator);
            if($two_symbol){
                $sym_part = $sym_part.$temp_str[0].$temp_str[1];
                $other_part = store_part(2, $temp_str);
            }
            else{
                $sym_part = $sym_part.$temp_str[0];
                $other_part = store_part(1, $temp_str);
            }
            $temp = trim($other_part);
            if(is_numeric($temp)){
                $other_part = $other_part.' '.$sql_part;//
            }
            else
                {$other_part = "'{$other_part}'".' '.$sql_part;}//
        }
        if(($counter++) == sizeof($real_parameters) - 1){
            if($sym_part)
                {$condition_kernel = $condition_kernel.$key.$sym_part.$other_part.' ';}
            else
                {$condition_kernel = $condition_kernel.$key.'='.$value.' ';}
        }
        else{
            if($sym_part)
                {$condition_kernel = $condition_kernel.$key.$sym_part.$other_part.' '.'OR'.' ';}
            else
                {$condition_kernel = $condition_kernel.$key.'='.$value.' '.'OR'.' ';}
        }

    }
    
    $query = "SELECT";
    $query_condition = " WHERE ";
    $query_from = "FROM ";

    $query = $query.' * '.$query_from.$table_name.$query_condition.$condition_kernel;
    
    $query_array[0] = $query;

    for($i=0;$i<sizeof($query_array);$i++)
    {
        $result = array();
        $temp = $query_array[$i];
        $query_result = analyze_sql($temp);
        // print($query_result['result']);

        if($query_result['select'])
        {
            echo "<div id='select'><h3><i>{$temp}</i></h3></div>";
            print($query_result['select']);
        }
        if($query_result['show'])
        {
            echo "<div id='show'><h3><i>{$temp}</i></h3></div>";
            print($query_result['show']);
        }
        if($query_result['update']){
            echo "<div id='update'><h3><i>{$temp}</i></h3></div>";
            foreach($query_result['update'] as $k=>$v){
                print($v);
            }
        }
        if($query_result['drop'])
        {
            echo "<div id='drop'><h3><i>{$temp}</i></h3></div>";
            print($query_result['drop']);
        }

        if($query_result['no_values']){
            echo " <div class='alert' id='pdo_alert'>
            <span class='closebtn' onclick='this.parentElement.style.display='none';'>&times;</span>
            NO VALUES for query <b>`{$temp}`</b> : No Values.
            </div> ";
        }
        if($query_result['error']){
            $error = $query_result['error'];
            echo " <div class='alert' id='pdo_alert'>
            <span class='closebtn' onclick='this.parentElement.style.display='none';'>&times;</span>
            PDO ERROR for query <b>`{$temp}`</b> : {$error}.
            </div> ";
        }

        echo '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';

    }

?>

 
<style>
.alert {
   padding: 20px;
   background-color: #f44336; /* Red */
   color: white;
   margin-bottom: 15px;
 }

 /* The close button */
 .closebtn {
   margin-left: 15px;
   color: white;
   font-weight: bold;
   float: right;
   font-size: 22px;
   line-height: 20px;
   cursor: pointer;
   transition: 0.3s;
 }

 /* When moving the mouse over the close button */
 .closebtn:hover {
   color: black;
 }
 </style> 

