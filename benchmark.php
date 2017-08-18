<?php
// Start the buffering //
    if($_POST['collect']) { ob_start(); }
?>
<!DOCTYPE html><html><head>
<style>
    table {
        color: #333; /* Lighten up font color */
        font-family: Helvetica, Arial, sans-serif; /* Nicer font */
        width: 640px;
        border-collapse:
        collapse; border-spacing: 0;
    }

    td, th {
        border: 1px solid #CCC; height: 30px;
    } /* Make cells a bit taller */

    th {
        background: #F3F3F3; /* Light grey background */
        font-weight: bold; /* Make sure they're bold */
    }                                                                                                                                                   
  </style>                                                                                                                                                 
  <script type="text/javascript">                                                                                                                          
function showMe (box) {                                                                                                                                  

    var chboxs = document.getElementsByName(box);
    var vis = "none";
    for(var i=0;i<chboxs.length;i++) {
        if(chboxs[i].checked){
         vis = "block";
            break;
        }
    }
    document.getElementById(box).style.display = vis;


  }

function CompareTables(table1,table2)
   {
        var instHasChange = false;
        for(var i=0; i < table1.rows.length; i++)
        {
            var changes =RowExists(table2,table1.rows[i].cells[0].innerHTML,parseFloat(table1.rows[i].cells[1].innerHTML));
            if(!changes[0])
            {
                 table1.rows[i].style.backgroundColor = "orange";
                 instHasChange = true;
            }
            else if(changes[1])
            {
                table1.rows[i].style.backgroundColor = "green";
                instHasChange = true;
            }
            
        }
        for(var i=0; i < table2.rows.length; i++)
        {
            var changes = RowExists(table1,table2.rows[i].cells[0].innerHTML,parseFloat(table2.rows[i].cells[1].innerHTML));
            if(!changes[0])
            {
                 table2.rows[i].style.backgroundColor = "green";
                 instHasChange = true;
            }
            else if(changes[1])
            {
                table2.rows[i].style.backgroundColor = "orange";
                instHasChange = true;
            }
        }
        return instHasChange;
   }
function RowExists(table,columnName,columnValue)
   {
        var hasColumnOrChange = new Array(2);
        hasColumnOrChange[0] = false;
        hasColumnOrChange[1] = false;
        for(var i=0; i < table.rows.length; i++)
        {
            if(table.rows[i].cells[0].innerHTML == columnName)
            {
                hasColumnOrChange[0] = true;
                if(table.rows[i].cells[1].innerHTML > columnValue)
                hasColumnOrChange[1] = true;
            }
           
        }
        return hasColumnOrChange;
   }

</script>
</head>
<body>
<?php
/**
 * PHP Script to benchmark PHP and MySQL-Server
 *
 * inspired by / thanks to:
 * - www.php-benchmark-script.com  (Alessandro Torrisi)
 * - www.webdesign-informatik.de
 *
 * @author odan
 * @license MIT
 */
if($_POST['collect']) {
// -----------------------------------------------------------------------------
// Setup
// -----------------------------------------------------------------------------

    set_time_limit(320); // 6 minutes

    $options = array();

    if($_POST['mybench']) {
        $mysql = True;
        // Optional: mysql performance test
        $options['db.host'] = $_POST['mysql_host'];
        $options['db.user'] = $_POST['mysql_user'];
        $options['db.pw'] = $_POST['mysql_password'];
        $options['db.name'] = $_POST['mysql_db'];
    }
    else {
        $mysql = False;
        echo "MySQL credentials were not provided, so MySQL bench was skipped. <br><br>";
    }
    // -----------------------------------------------------------------------------
    // Main
    // -----------------------------------------------------------------------------
    // check performance
        // Running benchmark
        $benchmarkResult = test_benchmark($options);
        // Reading from file if comparison is enabled
        if($_POST['compare']) { $fileReadResult = file_get_contents($_POST['compare-file']); }
        // saving to file, if selected
        if($_POST['saveFile']) { 
            $file_path = explode('/', $_POST['save-file']);
            if(count($file_path) > 1) {
                mkdir(implode("/", array_slice($file_path, 0, -1)));
            }
            if (file_exists($_POST['save-file'])) {
                $file_array = explode(".", $_POST['save-file']);
                array_splice($file_array, -1, 0, date('dmY_his', time()));
                $file = implode('.', $file_array);
            }
            else {
                $file = $_POST['save-file'];
            }
            file_put_contents($file, json_encode($benchmarkResult));
        }
        echo "You may download file with results via the links: <a href='".$file."'>JSON</a>, <a href='".$file.".html'>HTML</a><br>";
        echo "<table style='border: 1px solid black;'><tr><td><center>Current</center></td>";
        if($_POST['compare']) { echo "<td><center>Comparison</center></td>"; }
        echo "</tr><tr><td>";
        echo array_to_html($benchmarkResult, "current");
        if($_POST['compare']) { echo "</td><td>", array_to_html(json_decode($fileReadResult, true), "file"); }
        echo "</td></tr></table>";
        if($_POST['compare']) {
        if ($mysql == True) { 
            echo "<script>
                window.onload = CompareTables(document.getElementById('currentmysql'), document.getElementById('filemysql'));
                </script>"; 
            }
        echo "<script>
        window.onload = CompareTables(document.getElementById('currentphp'), document.getElementById('filephp'));
        window.onload = CompareTables(document.getElementById('currentdisk'), document.getElementById('filedisk'));
        window.onload = function() {
            var currenttotal = document.getElementById('currenttotal');
            var filetotal = document.getElementById('filetotal');
            var currenttotal_int = parseFloat(currenttotal.innerText);
            var filetotal_int = parseFloat(filetotal.innerText);
            if(currenttotal_int < filetotal_int)
            {
                currenttotal.style.backgroundColor = 'green';
                var better_pct = parseFloat((filetotal_int - currenttotal_int) / (currenttotal_int/100)).toFixed(2);
                currenttotal.innerText = currenttotal_int + ' (' + better_pct + '% better)'
            }
            else if(currenttotal_int > filetotal_int)
            {
                filetotal.style.backgroundColor = 'orange';
                var better_pct = parseFloat((currenttotal_int - filetotal_int) / (filetotal_int/100)).toFixed(2);
                filetotal.innerText = filetotal_int + ' (' + better_pct + '% worse)'
            }
        };
        </script>";
        }
        echo "</body></html>";
        file_put_contents($file.'.html', ob_get_contents());
        ob_end_flush();
        exit;
}
else {
?>

<form method="POST" action="">
  Save results to file: <input type="checkbox" onclick="showMe('saveFile')" name="saveFile" checked>
  <div id="saveFile">
   Filename: <input type="text" name="save-file" value="benchmark/benchmark_results.txt">
  </div>
<br>
  Compare with existing file: <input type="checkbox" onclick="showMe('compare')" name="compare">
  <div id="compare" style="display:none">
   Filename: <input type="text" name="compare-file" value="benchmark/benchmark_results.txt">
  </div>
<br>
MySQL benchmark: <input type="checkbox" onclick="showMe('mybench')" name="mybench">
<div id="mybench" style="display:none">
  MySQL username:
  <input type="text" name="mysql_user"><br>
  MySQL database:
  <input type="text" name="mysql_db"><br>
  MySQL password:
  <input type="password" name="mysql_password"><br>
  MySQL host:
  <input type="text" name="mysql_host" value="127.0.0.1"><br>
</div>
<br><br><input type="submit" value="Submit" name="collect">
</form>

<?php
}
// -----------------------------------------------------------------------------
// Benchmark functions
// -----------------------------------------------------------------------------

function test_benchmark($settings)
{
    global $mysql;
    $timeStart = microtime(true);

    $result = array();
    // $result['version'] = '1.2';
    $result['sysinfo']['time'] = date("Y-m-d H:i:s");
    $result['sysinfo']['php_version'] = PHP_VERSION;
    $result['sysinfo']['platform'] = PHP_OS;
    $result['sysinfo']['post_max_size'] = ini_get('post_max_size');
    $result['sysinfo']['memory_limit'] = ini_get('memory_limit');

    $result['sysinfo']['platform'] = PHP_OS;
    $result['sysinfo']['server_name'] = $_SERVER['SERVER_NAME'];
    $result['sysinfo']['server_addr'] = $_SERVER['SERVER_ADDR'];

    test_math($result['php']);
    test_string($result['php']);
    test_loops($result['php']);
    test_ifelse($result['php']);
    test_disk($result['disk']);

    if ($mysql == True) {
        test_mysql($result['mysql'], $settings);
    }

    $result['total'] = timer_diff($timeStart);
    return $result;
}


function test_math(&$result, $count = 999999)
{
    $timeStart = microtime(true);

    $mathFunctions = array("abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt");
    for ($i = 0; $i < $count; $i++) {
        foreach ($mathFunctions as $function) {
            call_user_func_array($function, array($i));
        }
    }
    $result['math'] = timer_diff($timeStart);
}

function test_string(&$result, $count = 999999)
{
    $timeStart = microtime(true);
    $stringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord");

    $string = 'the quick brown fox jumps over the lazy dog';
    for ($i = 0; $i < $count; $i++) {
        foreach ($stringFunctions as $function) {
            call_user_func_array($function, array($string));
        }
    }
    $result['string'] = timer_diff($timeStart);
}

function test_loops(&$result, $count = 99999999)
{
    $timeStart = microtime(true);
    for ($i = 0; $i < $count; ++$i) {

    }
    $i = 0;
    while ($i < $count) {
        ++$i;
    }
    $result['loops'] = timer_diff($timeStart);
}

function test_ifelse(&$result, $count = 99999999)
{
    $timeStart = microtime(true);
    for ($i = 0; $i < $count; $i++) {
        if ($i == -1) {

        } elseif ($i == -2) {

        } else if ($i == -3) {

        }
    }
    $result['ifelse'] = timer_diff($timeStart);
}

function test_disk(&$result, $filesize = 104857600) {
    $filename = "randfile";
    $src_write = fopen('/dev/urandom', 'r');
    $dest_write = fopen($filename, 'w');
    $writeStart = microtime(true);
    $write = stream_copy_to_stream($src_write, $dest_write, $filesize);
    
    /*
    if ($h = fopen($filename, 'w')) {
        if ($filesize > 1024) {
            for ($i = 0; $i < floor($filesize / 1024); $i++) {
                fwrite($h, bin2hex(openssl_random_pseudo_bytes(511)) . PHP_EOL);
            }
            $filesize = $filesize - (1024 * $i);
        }
        $mod = $filesize % 2;
        fwrite($h, bin2hex(openssl_random_pseudo_bytes(($filesize - $mod) / 2)));
        if ($mod) {
            fwrite($h, substr(uniqid(), 0, 1));
        }
        fclose($h);
        umask(0000);
        chmod($filename, 0644);
    }
    
    */
    $result['write'] = timer_diff($writeStart);
    fclose($src_write);
    fclose($dest_write);

    $src_read = fopen($filename, 'r');
    $dest_read = fopen('/dev/null', 'w');
    $readStart = microtime(true);
    $read = stream_copy_to_stream($src_read, $dest_read);
    $result['read'] = timer_diff($readStart);
    fclose($src_read);
    fclose($dest_read);
    unlink($filename);

}

function test_mysql(&$result, $settings)
{
    $timeStart = microtime(true);

    $link = mysqli_connect($settings['db.host'], $settings['db.user'], $settings['db.pw']);
    $result['mysql_connect'] = timer_diff($timeStart);

    //$arr_return['sysinfo']['mysql_version'] = '';

    mysqli_select_db($link, $settings['db.name']);
    $result['select_db'] = timer_diff($timeStart);

    $dbResult = mysqli_query($link, 'SELECT VERSION() as version;');
    $arr_row = mysqli_fetch_array($dbResult);
    //$result['sysinfo']['mysql_version'] = $arr_row['version'];
    $result['query_version'] = timer_diff($timeStart);

    $query = "SELECT BENCHMARK(1000000,ENCODE('hello',RAND()));";
    $dbResult = mysqli_query($link, $query);
    $result['query_benchmark'] = timer_diff($timeStart);

    mysqli_close($link);

    $result['total_mysql'] = timer_diff($timeStart);
    return $result;
}

function timer_diff($timeStart)
{
    return number_format(microtime(true) - $timeStart, 3);
}


function array_to_html($array, $step, $type='')
{
    $result = '';
    if (is_array($array)) {
        if ($type) { $result .= '<table id='.$step.$type.'>'; }
        else { $result .= '<table>'; }
        foreach ($array as $k => $v) {
            $result .= "<tr><td>";
            $result .= '<strong>' . htmlentities($k) . "</strong></td>";
            if ($k == "total") { $result .= "<td id=".$step.$k.">"; }
            else { $result .= "<td>"; }
            if ($k == "php" || $k == "disk" || $k =="mysql") { $result .= array_to_html($v, $step, $k); }
            else { $result .= array_to_html($v, $step); }
            $result .= "</td></tr>";
        }
        $result .= "</table>";
    } else {
        $result = htmlentities($array);
    }
    return $result;
}
