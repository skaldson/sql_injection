<?php 

    require 'db_connect.php';

    $db_name = "kolenapost";
    $db_user = "root";
    $db_password = "";

    $query_table_name = "SHOW TABLES";

    $dbh=MySQLDatabase::connect($db_name, $db_user, $db_password);

    $sth_1 = $dbh->prepare($query_table_name);
    $sth_1->execute();

    $tables_name = $sth_1->fetchAll(PDO::FETCH_ASSOC);
    
?>

<style>
.layer1 {
    position: absolute; 
    bottom: 600px; 
    right: 100px; 
    line-height: 1px;
   }
}
</style>

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
    sendAjaxGetRequest("table_output.php?"+params, onType1Response);
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
        foreach($tables_name as $val){
            foreach($val as $temp){
                echo "<option>$temp</option>";
        }
        print("\n");
        }
    ?>
        </select>
    <p><input type="button" id="table_go" value="show table" onclick="sqlInjType_1();"></p>
    </form>
	
