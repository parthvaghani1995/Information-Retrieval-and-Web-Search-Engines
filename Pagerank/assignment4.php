

<?php
header('Content-Type:text/html; charset=utf-8');
$limit = 10;
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;
$linksFromFile = array();

if($query){
        require_once('solr-php-client/Apache/Solr/Service.php');
        $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
        if(get_magic_quotes_gpc() == 1){
                $query = stripslashes($query);
        }
        try{
		if(!isset($_GET['algorithm']))$_GET['algorithm']="lucene";
		if($_GET['algorithm'] == "lucene"){

			$param = array("q.op" => 'AND');
			$results = $solr->search($query, 0, $limit,$param);

		}else{

			$param = array('sort'=>'pageRankFile desc', "q.op" => 'AND');
			$results = $solr->search($query, 0, $limit, $param);

		}

	 }
        catch(Exception $e){
                die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");
        }
}
?>


<html>
<head>
        <title> PHP Solr Client Example </title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
	.searchDiv{
		text-align: center;
	}
	.resultDiv{
		padding-bottom: 20px;
		padding-left: 15px;
	}
	.ResultLineDiv{
		padding-left: 10px;
		padding-bottom: 20px;
	}
	th{
		padding-right: 10px;
	}
	
</style>
</head>
<body>
<div class= "searchDiv">
<h1> Search </h1><br/>
<div class = "formDiv">
<form accept-charset="utf-8" method="get">

    <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>"/><br/><br/> 
	<input type="radio" name="algorithm" value="lucene" /> Solr's Default - Lucene
	<input type="radio" name="algorithm" value="pagerank" /> Google's - PageRank <br/><br/> 
	<input type="submit" class="btn-primary" />
</form>
</div>
</div>
<?php
if($results){
        $total = (int)$results->response->numFound; 
        $start = min(1,$total);
        $end = min($limit, $total);
        $file = fopen("UrlToHtml_NBCNews.csv","r"); 
        while(!feof($file))
 		{
  			$a = fgetcsv($file);
  			#print($a[0] ."-" . $a[1]);
  			$linksFromFile[$a[0]] = $a[1];
  		}

fclose($file);
?>
<div class="ResultLineDiv"> Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total;?>:</div> 
	
<?php
	foreach ($results->response->docs as $doc) {?>
		<tr>
		<?php 
		foreach ($doc as $key => $value) {
			
			if($key == "id"){
				$currentID = $value;
			}
			if($key == 'description'){
				$currentDescription = $value;
			}
			if($key == 'og_url'){
				$currentURL = $value;
			}
			if($key == 'title'){
				$currentTitle = $value;
			}
		}
		if(empty($currentURL)){
			$idFetch = explode("/", $currentID);
			$currentURL = $linksFromFile[$idFetch[9]];
		}
		if(empty($currentDescription)){
			$currentDescription = "N/A";
		}
		if(empty($currentTitle)){
			$currentTitle = "N/A";
		}?>
		<div class="resultDiv">
		<table style ="border: 1px solid black; text-align: left; border-radius:10px; ">
			<tr>
				<th>ID</th>
				<td><?php echo $currentID ?></td>
			</tr>
			<tr>
				<th>Title</th>
				<td><a href=<?php echo $currentURL ?>><?php echo $currentTitle ?></a></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><?php echo $currentDescription ?></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href=<?php echo $currentURL ?>><?php echo $currentURL ?></a></td>
			</tr>
		</table>
	</div>

<?php  
		$currentTitle = $currentDescription = $currentURL = $currentID = "";?>
		</tr>

	<?php }?>


	


	<?php } ?>
</body>
</html>

