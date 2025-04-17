<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "PHP ini location: " . php_ini_loaded_file() . "<br>";
echo "PHP ini scanned files: " . implode(", ", php_ini_scanned_files()) . "<br>";
echo "Current working directory: " . getcwd() . "<br>";
echo "Loaded extensions: <br>";
print_r(get_loaded_extensions());
?> 