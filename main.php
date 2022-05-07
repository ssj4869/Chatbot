
<?php
error_reporting(0);
if(isset($_POST['msg'])) {
    $resp = processText($_POST['msg']);
	saveMsgs($resp, $_POST['msg']);
 		$jdata = json_decode(file_get_contents("msgs.txt"),true);
		foreach($jdata as $key=>$value) {
			foreach($value as $vkey=>$vval) {
				if($vkey == "chbot") {
					echo "<div class='yours messages'><div class='message last'>$vval</div></div>";
				} else {
					echo "<div class='mine messages'><div class='message'>$vval</div></div>";
				}
			}
		}

}

    if(isset($_POST['del'])) {
		unlink("msgs.txt");
	}
//This function will take the user input as a text
//


 session_start();
 $_SESSION['useTokens'] = false;
    $resp=getSpecsByBand('AS301');
	//echo $resp['processor'];
    global $questions,$type,$db,$brands;
    $questions = array (
	    'what', 'suggest', 'show'
	);
	$type = array (
	    'laptop', 'laptops', 'desktop', 'pc', 'desktop', 'computer', 'computers',
	);
	
	$range = array(
	    'less', 'more', 'above', 'below', 'of', 'in', 'budget'
	);
	
	$brands = array('asus', 'lenovo', 'samsung', 'any','dell', 'sony');
	
	$db = mysqli_connect('localhost', 'root', '', 'chatbot');

	//echo processText("suggest me a computer of dell below 39999");
	
    function processText($msg) {
		$msg = strtolower($msg);
		$ret = '';
		if(strpos($msg, "hello") !== false || (strpos($msg, "hi") !== false) || (strpos($msg, "hey") !== false)) {
			$ret= "Hello, there!";
			return $ret;
		} else {
			global $questions, $type,$range,$brands;
				$brands = array('asus', 'lenovo', 'samsung', 'any','dell', 'sony');

			$found = false;
			$token = array();
			if(file_exists("tokens.txt")) $token = unserialize(file_get_contents("tokens.txt"));
			$fl = 0;
			$sstr = explode(" ", $msg);
			foreach($sstr as $key=>$value) {
				if(in_array($value, $type)) {
					$token['type'] = $value;
					$found = true;
					//$token[''] = false;
				}
				if(in_array($value, $range)) {
					if($value == "budget") $value = "below";
					$token['range'] = $value;
					$found = true;

				}
				if(in_array($value, $brands)) {
					$found = true;
					$token['brand'] = $value;
				}
				if($key == 0 && in_array($value, $questions)!==false) {
					$found = true;
					$token['question'] = $value;
					if(in_array($value, $brands))
						$token['brand'] = $value;
				}

			}
			//check if the user mentioned price
			$val = filter_var($msg, FILTER_SANITIZE_NUMBER_INT);
			$token['price'] = ($token['price'] > 0) ? $token['price'] : $val;
			file_put_contents("tokens.txt", serialize($token));

			if(!isset($token['brand'])) {
				$ret =  "Sure! Can you Specify a brand?";
				Return $ret;
			}
			if($token['price'] == 0 && isset($token['brand'])) {
				$ret= "Specify a price range which fits your budget";
				Return $ret;
			}
			if(!$found && $token['price'] == 0) {
				$ret= "I'm sorry. i didn't understand that.";
				return $ret;
			}

			//echo json_encode($token);
			//$_SESSION['tokens'] = $token;
			$token = unserialize(file_get_contents("tokens.txt"));
			if(!empty($token)) {
				unlink("tokens.txt");
				$response = processTokens($token);
				file_put_contents("data.txt", serialize($response));
				$cnt = count($response);
				$_SESSION['results'] = $response;
				//$str = "okmsg>>>>$cnt>>>>";
				/*
				foreach($response as $key=>$value) {
					$str.= "$value<br>";
				}*/
				$i = 1;
				$str.= '<div  class="panel panel-info"><div class="panel-heading">Okay! Here is what ive found!</div><div style="height: 430px; overflow-y:auto;" class="panel-body">';
				foreach($response as $key=>$value) {
				/*$str.= "<table class='table table-dark table-hover'border='collapse'style='width:100%;text-align:center;'>
				<tr><th><center>Name</center></th>
				<th><center>Value</center></th>
				</tr>";*/
					$model = $value['model'];
					$specs = getSpecsByBand($model);
					$arr = array('Name' => $value['name'],'Brand' => $value['brand'],'Display Size' => "$specs[displaysize] inches",'Resolution' => $specs['resolution'], 'Processor' => $specs['processor'], 'CPU Speed' => "$specs[cpuspeed] GHZ", 'RAM' => "$specs[ram] GB", 'Storage' => $specs['storage'], 'Capacity' => $specs['capacity']);
					//$str.="<tr><td>processor</td><td>$specs[cpuspeed]</td></tr>";
					$str.='<br>';
					$str.= str_repeat("-", 30).'<br>';
					foreach($arr as $skey=>$svalue) {
					    //$str.="<tr><td>$skey</td><td>$svalue</td></tr>";
						$str.= "$skey - $svalue<br>";

					}
					$i++;
					$str.= str_repeat("-", 30);
				}
				//$str.= "</div></div>";
				return $str;
			} else return "I'm sorry, i didn't understand that";
		}
	}
	function processTokens($obj) {
		global $db;
		if(!$db) $db = mysqli_connect('localhost', 'root', '', 'chatbot');

		$ret = array();
		$type = $obj['type'];
		// sort the query.
		if(in_array($type, array('computers', 'computer', 'desktop', 'pc'))) $type = 'computer';
		if(in_array($type, array('laptops', 'laptop'))) $type = 'laptop';
		//sort the price
		$range = "<";
		if(in_array($obj['range'], array('below', 'less', 'in'))) $range = '<';
		if(in_array($obj['range'], array('above', 'more', 'of'))) $range = '>';
		//processSQL
		$SQL = "SELECT * FROM items WHERE price $range $obj[price]";
		if(isset($obj['brand']) && $obj['brand'] != 'any') $SQL.=" AND brand='$obj[brand]'";
		if(isset($obj['type'])) $SQL.= " AND type='$type'";
		//return $SQL;
		file_put_contents("SQL.txt", $SQL);
		$res = mysqli_query($db,$SQL);
		$found = false;
		if($res) {
			while($row = mysqli_fetch_array($res)) {
				//$ret[]= array("name: $row[name],<br>Brand: $row[brand]<br>Model: $row[model_no]<br>Price: $row[price]");
				$ret[] = array('name' => $row['name'], 'brand' => $row['brand'], 'model' => $row['model_no'], 'price' => $row['price']);
			    $found = true;
			}
		} else {
			return "NOTHING";
		}
		if($found == false) {
			return "I'm sorry! I didn't find a product with your specification!! Please change it and try again!!";
		} else {
		return $ret;
		}
		//return $SQL;
		//return $type;
	}
	
	function saveMsgs($chmsg, $msg) {
		$arr = array();
		if(file_exists("msgs.txt")) $arr = json_decode(file_get_contents("msgs.txt"),true);
		$newArr = array("msg" => $msg, "chbot" => $chmsg);
		$arr[] = $newArr;
		$encode = json_encode($arr);
		file_put_contents("msgs.txt", $encode);
	}

	function getSpecsByBand($brand) {
		global $db;
		//echo "brand: $brand;";
		if(!$db) $db = mysqli_connect('localhost', 'root', '', 'chatbot');

		$SQL = "SELECT * FROM data_items WHERE model_no = '$brand'";
		$res = mysqli_query($db,$SQL);
		if(!$res) echo mysqli_error($db);
		return mysqli_fetch_array($res);
	}
	/*
	$val= processText("Suggest me laptops in dell");
	echo print_r($val);*/
?>