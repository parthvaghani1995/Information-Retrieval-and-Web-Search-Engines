<?php
error_reporting(0);
header('Content-Type:text/html; charset=utf-8');
include 'SpellCorrector.php';
include 'test2.php';

$limit = 10;
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;
$linksFromFile = array();
$ceck = "";

function highlight($text, $words) {
    preg_match_all('~\w+~', $words, $m);
    if(!$m)
        return $text;
    $re = '~\\b(' . implode('|', $m[0]) . ')\\b~';
    return preg_replace($re, '<b>$0</b>', $text);
}

if($query){
	$split = explode(" ", $query);
    $size = sizeof($split);
    for ($i=0;$i<$size; $i++) { 
      # code...
      $temp = SpellCorrector::correct($split[$i]);
      $check = $check." ".$temp;
    }
    $check = trim($check);
    $query = trim($query);
    if(strcasecmp($check, $query) != 0){
      // echo $query;
      // echo "<a style='text-decoration:none;' href='http://localhost/search.php?q=$query'>";
      echo "
              <div id='newResult' style='position: absolute; top: 20%; padding: 10px;'>
                <span style='font-size:20px;'>Did you mean</span>"." ".
                "<a style='font-size:20px; font-style:italic; text-decoration:none;' href='http://localhost/IRassignment4/assignment4.php?q=$check'>".$check.
                "</a>?<br/>"."<span>Search result for</span>"." ".
                "<a style='text-decoration:none;' href='http://localhost/IRassignment4/assignment4.php?q=$query'>".$query."</a></div>";
      // $query = $check;
    }



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
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
 		<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  		<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
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
<script>

   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;
     var tags = [];
     $("#q").autocomplete({
       source : function(request, response) {
         var correct="",before="";
         var query = $("#q").val().toLowerCase();
         var character_count = query.length - (query.match(/ /g) || []).length;
         var space =  query.lastIndexOf(' ');
         if(query.length-1>space && space!=-1){
          correct=query.substr(space+1);
          before = query.substr(0,space);
        }
        else{
          correct=query.substr(0); 
        }
        var URL = URL_PREFIX + correct+ URL_SUFFIX;
        $.ajax({
         url : URL,
         success : function(data) {
          var js =data.suggest.suggest;
          var docs = JSON.stringify(js);
          console.log(docs)
          var jsonData = JSON.parse(docs);
          var result =jsonData[correct].suggestions;
          var j=0;
          var stem =[];
          for(var i=0;i<5 && j<result.length;i++,j++){
            if(result[j].term==correct)
            {
              i--;
              continue;
            }
            for(var k=0;k<i && i>0;k++){
              if(tags[k].indexOf(result[j].term) >=0){
                i--;
                continue;
              }
            }
            if(result[j].term.indexOf('.')>=0 || result[j].term.indexOf('_')>=0)
            {
              i--;
              continue;
            }
            var s =(result[j].term);
            if(stem.length == 5)
              break;
            if(stem.indexOf(s) == -1)
            {
              stem.push(s);
              if(before==""){
                tags[i]=s;
              }
              else
              {
                tags[i] = before+" ";
                tags[i]+=s;
              }
            }
          }
          // console.log(tags);
          response(tags);
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
 </script>
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
				// $temp = explode("/", $value);
				// $temp1 = "/Users/parth/desktop/usc/ir/assignment4/NBC_News/HTMLfiles/".$temp[9];
				// $snippet = htmlspecialchars(generate_snippet($temp1, $query), ENT_NOQUOTES, 'utf-8');
				// $snippet = mb_convert_case($snippet, MB_CASE_LOWER, "UTF-8");
    //   			$query = mb_convert_case($query, MB_CASE_LOWER, "UTF-8");
    //   			$pieces = explode(" ", $query);
    // 			 #echo count($pieces);
			 //      foreach($pieces as $colors)
			 //      {
			 //        $snippet = preg_replace(" ?".preg_quote($colors)." ?", "<b>$0</b>", $snippet);
			 //      }
			}
			if($key == 'description'){
				$currentDescription = $value;
				// if ($snippet == 0) {
    //   			  $snippet = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
    //   			  $snippet = mb_convert_case($snippet, MB_CASE_LOWER, "UTF-8");
			 //      $query = mb_convert_case($query, MB_CASE_LOWER, "UTF-8");
			 //      $pieces = explode(" ", $query);
			 //      foreach($pieces as $colors)
			 //      {
			 //        $snippet = preg_replace(" ?".preg_quote($colors)." ?", "<b>$0</b>", $snippet);
			 //      }
    //  			}
			}
			if($key == 'og_url'){
				$currentURL = $value;
			}
			if($key == 'title'){
				$currentTitle = $value;
			}
		}

		$temp = explode("/", $currentID);
		$temp1 = "/Users/parth/desktop/usc/ir/assignment4/NBC_News/HTMLfiles/".$temp[9];
		$query = mb_convert_case($query, MB_CASE_LOWER, "UTF-8");
		$pieces = explode(" ", $query);
		$currentDescriptiontemp = mb_convert_case($currentDescription, MB_CASE_LOWER, "UTF-8");
		$currentDescriptionArray = explode(" ", $currentDescriptiontemp);
		if(array_search($pieces,$currentDescriptionArray) == false){
			$snippet = htmlspecialchars(generate_snippet($temp1, $query), ENT_NOQUOTES, 'utf-8');
			#$snippet = "... " . substr($snippet,0,160) ." ...";
			$snippet1 = "... " .$snippet . " ...";
		}else{
			$snippet1 = $currentDescriptiontemp;
		}
		if($snippet == 0){
			$snippet1 = $currentDescriptiontemp;
		}



		if(empty($currentURL)){
			$idFetch = explode("/", $currentID);
			$currentURL = $linksFromFile[$idFetch[9]];
		}
		if(empty($currentDescription)){
			$currentDescription = "N/A";
			$snippet = htmlspecialchars(generate_snippet($temp1, $query), ENT_NOQUOTES, 'utf-8');
			$snippet1 = "... " .$snippet . " ...";
		}
		if($snippet1 == "... 0 ..."){
			$snippet1 = $currentDescriptiontemp;
			if(empty($currentDescriptiontemp)){
				$snippet1 = "N/A";
			}
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
			<tr>
				<th>Snippet</th>
				<td><?php 
				if(strlen($snippet1) <=160){
					$snippet2 = $snippet1;
				}else{
					$snippet2 = substr($snippet1,0,160 ) . " ..."; 
				}
				print highlight($snippet2, $query);?></td>
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

