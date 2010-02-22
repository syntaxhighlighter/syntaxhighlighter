<?php

$treat = false;
if (isset($_POST['src'])) {
  $script = $_POST['src'];
  if (get_magic_quotes_gpc())
    $script = stripslashes($script);
  $encoding = (int)$_POST['ascii_encoding'];
  $fast_decode = isset($_POST['fast_decode']) && $_POST['fast_decode'];
  $special_char = isset($_POST['special_char'])&& $_POST['special_char'];
  
  require 'class.JavaScriptPacker.php';
  $t1 = microtime(true);
  $packer = new JavaScriptPacker($script, $encoding, $fast_decode, $special_char);
  $packed = $packer->pack();
  $t2 = microtime(true);
  
  $originalLength = strlen($script);
  $packedLength = strlen($packed);
  $ratio =  number_format($packedLength / $originalLength, 3);
  $time = sprintf('%.4f', ($t2 - $t1) );
  
  $treat = true;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
          "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>JavaScript Packer in PHP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
.result {
  border: 1px blue dashed;
  color: black;
  background-color: #e5e5e5;
  padding: 0.2em;
}
</style>
<script type="text/javascript">
function decode() {
  var packed = document.getElementById('packed');
  eval("packed.value=String" + packed.value.slice(4));
}
</script>
</head>
<body>
  <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
    <div>
      <h3>script to pack:</h3>
      <textarea name="src" id="src" rows="10" cols="80"><?php if($treat) echo htmlspecialchars($script);?></textarea>
    </div>
    <!-- <fieldset> -->
    <div>
      <label for="ascii-encoding">Encoding:</label>
      <select name="ascii_encoding" id="ascii-encoding">
        <option value="0"<?php if ($treat && $encoding == 0) echo ' selected'?>>None</option>
        <option value="10"<?php if ($treat && $encoding == 10) echo ' selected'?>>Numeric</option>
        <option value="62"<?php if (!$treat) echo 'selected';if ($treat && $encoding == 62) echo ' selected';?>>Normal</option>
        <option value="95"<?php if ($treat && $encoding == 95) echo ' selected'?>>High ASCII</option>
      </select>
      <label>
        Fast Decode:
        <input type="checkbox" name="fast_decode" id="fast-decode"<?php if (!$treat || $fast_decode) echo ' checked'?>>
      </label>
      <label>
        Special Characters:
        <input type="checkbox" name="special_char" id="special-char"<?php if ($treat && $special_char) echo ' checked'?>>
      </label>
      <input type="submit" value="Pack">
    </div>
    <!-- </fieldset> -->
  </form>
  
  <?php if ($treat) {?>
  <div id="result">
    <h3>packed result:</h3>
    <textarea id="packed" class="result" rows="10" cols="80" readonly="readonly"><?php echo htmlspecialchars($packed);?></textarea>
    <p>
      compression ratio:
      <?php echo $originalLength, '/', $packedLength, ' = ',$ratio; ?>
      <br>performed in <?php echo $time; ?> s.
    </p>
    <p><button type="button" onclick="decode()">decode</button></p>
  </div>
  <?php };//end if($treat)?>
</body>
</html>
