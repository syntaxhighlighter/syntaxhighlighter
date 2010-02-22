<?php
// you can pass this script to PHP CLI to convert your file.

// adapt these 2 paths to your files.
$src = 'myScript-src.js';
$out = 'myScript.js';

// or uncomment these lines to use the argc and argv passed by CLI :
/*
if ($argc >= 3) {
	$src = $argv[1];
	$out = $argv[2];
} else {
	echo 'you must specify  a source file and a result filename',"\n";
	echo 'example :', "\n", 'php example-file.php myScript-src.js myPackedScript.js',"\n";
	return;
}
*/

require 'class.JavaScriptPacker.php';

$script = file_get_contents($src);

$t1 = microtime(true);

$packer = new JavaScriptPacker($script, 'Normal', true, false);
$packed = $packer->pack();

$t2 = microtime(true);
$time = sprintf('%.4f', ($t2 - $t1) );
echo 'script ', $src, ' packed in ' , $out, ', in ', $time, ' s.', "\n";

file_put_contents($out, $packed);
?>
