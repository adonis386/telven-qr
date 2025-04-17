<?php
// Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "<br>";
echo "MySQL Extension: " . (extension_loaded('mysqli') ? "Loaded" : "Not loaded") . "<br>";

// Intentar conectar a MySQL
$mysqli = @new mysqli('localhost', 'root', '9XfObH!w7G[J9m0c');

if ($mysqli->connect_error) {
    echo "Error de conexión MySQL: " . $mysqli->connect_error . "<br>";
    echo "Error code: " . $mysqli->connect_errno . "<br>";
} else {
    echo "Conexión exitosa a MySQL<br>";
    echo "MySQL Server Version: " . $mysqli->server_info . "<br>";
    echo "MySQL Client Version: " . $mysqli->client_info . "<br>";
    $mysqli->close();
}

// Verificar si MySQL está corriendo
$output = [];
exec('netstat -an | findstr "3306"', $output);
echo "MySQL Port Status:<br>";
if (empty($output)) {
    echo "Puerto 3306 no está en uso (MySQL probablemente no está corriendo)<br>";
} else {
    echo "Puerto 3306 está en uso:<br>";
    foreach ($output as $line) {
        echo $line . "<br>";
    }
}
?> 