<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded extensions: <br>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo $ext . "<br>";
}

echo "<br>Checking specific extensions:<br>";
$important_extensions = array('mysql', 'mysqli', 'pdo', 'pdo_mysql');
foreach ($important_extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "Loaded" : "Not loaded") . "<br>";
}
?> 