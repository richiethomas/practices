<pre>
<?php
echo "<p>Why, hello.</p>";


$url = "https://us02web.zoom.us/j/89561925744?pwd=aFN6ODBIb01EVkNldzRVUzlpdzRjZz09

Meeting ID: 895 6192 5744
Passcode: 929910";

$url = "https://us02web.zoom.us/j/89561925744?pwd=aFN6ODBIb01EVkNldzRVUzlpdzRjZz09";

$url = "TBA";

//preg_match('/^(https:\/\/\S+)\s*([\S\s]*)/', $url, $matches);
preg_match('/^(\S+)\s*([\S\s]*)/', $url, $matches);
print_r($matches);
?>
</pre>

//phpinfo();


