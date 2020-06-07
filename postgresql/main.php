<?php

    require "db_dsn.php";

    $dbh = new PDO($dsn);

    $stmt = $dbh->prepare('SELECT table_schema,table_name
    FROM information_schema.tables
    ORDER BY table_schema,table_name;');
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tables_name= array();
    for($i=sizeof($result) - 1;$i>=0;$i--){
        foreach($result[$i] as $k=>$v){
            if($v == 'public'){
                end($result[$i]);
                array_push($tables_name, $result[$i][key($result[$i])]);
            }
        }
    }

?>

<script>
var ajax;
InitAjax();
function InitAjax() 
{
	try 
	{ /* пробуем создать компонент XMLHTTP для IE старых версий */
	ajax = new ActiveXObject("Microsoft.XMLHTTP");
	} 
		catch (e) 
		{
		try 
			{//XMLHTTP для IE версий >6
			ajax = new ActiveXObject("Msxml2.XMLHTTP");
			} 
			catch (e) 
			{
			    try 
				{// XMLHTTP для Mozilla и остальных
				ajax = new XMLHttpRequest();
				} 
				catch(e) 
				{ ajax = 0; }
			}
		}
}

function sendAjaxGetRequest(request_string,response_handler)
{
	if (!ajax) 
	{
		alert("Ajax не инициализирован");
		return;
	}
	ajax.onreadystatechange = response_handler;
	ajax.open( "GET", request_string, true );
	ajax.send(null);
}

function sqlInjType_1()
{
    var value = document.getElementById("table_name").value;
    var params = 'table_name=' + encodeURIComponent(value);
    sendAjaxGetRequest("main_table.php?"+params, onType1Response);
}


function onType1Response()
{
    if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var d1 = document.getElementById('table_name'); 
            // alert(d1.value);
			d1.insertAdjacentHTML('afterend', ajax.responseText);
		}
		else alert(ajax.status + " - " + ajax.statusText);
		ajax.abort();
	}
}

</script>

<p><h2>SQL injection</h2></p>
<p><b>Choose table</b></p>
<form method="get">
<select name="table_name" id="table_name">     
    <?php
        for($i=0;$i<sizeof($tables_name); $i++){
            echo "<option>{$tables_name[$i]}</option>";
        }
    ?>
</select>
<p><input type="button" id="table_go" value="show table" onclick="sqlInjType_1();"></p>
</form>
