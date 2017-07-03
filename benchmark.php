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

    td {
        background: #FAFAFA; /* Lighter grey background */
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
 </script></head>
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
    set_time_limit(180); // 3 minutes

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
    if($_POST['compare']) { $fileReadResult = file_get_contents("benchmark_results.txt"); }
    // saving to file, if selected
    if($_POST['saveFile']) { file_put_contents($_POST['save-file'], json_encode($benchmarkResult)); }
    echo "You may download file with results via the <a href='benchmark_results.txt'>link</a><br>";
    echo "<table style='border: 1px solid black;'><tr><td>Results</td>";
    if($_POST['compare']) { echo "<td>File</td>"; }
    echo "</tr><tr><td>";
    echo array_to_html($benchmarkResult);
    if($_POST['compare']) { echo "</td><td>", array_to_html(json_decode($fileReadResult, true)); }
    echo "</td></tr></table></body></html>";
    exit;
}
else {
?>

<form method="POST" action="">
  Save results to file: <input type="checkbox" onclick="showMe('saveFile')" name="saveFile" checked>
  <div id="saveFile">
   Filename: <input type="text" name="save-file" value="benchmark_results.txt">
  </div>
<br>
  Compare with existing file: <input type="checkbox" onclick="showMe('compare')" name="compare">
  <div id="compare" style="display:none">
   Filename: <input type="text" name="compare-file" value="benchmark_results.txt">
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


function array_to_html($array)
{
    $result = '';
    if (is_array($array)) {
        $result .= '<table>';
        foreach ($array as $k => $v) {
            $result .= "\n<tr><td>";
            $result .= '<strong>' . htmlentities($k) . "</strong></td><td>";
            $result .= array_to_html($v);
            $result .= "</td></tr>";
        }
        $result .= "\n</table>";
    } else {
        $result = htmlentities($array);
    }
    return $result;
}
