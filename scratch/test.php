<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>ServerByte Diagnostic Check</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>PDO MySQL Extension:</strong> " . (extension_loaded('pdo_mysql') ? '<span style="color:green">ENABLED</span>' : '<span style="color:red">DISABLED</span>') . "<br>";
echo "<strong>OpenSSL Extension:</strong> " . (extension_loaded('openssl') ? '<span style="color:green">ENABLED</span>' : '<span style="color:red">DISABLED</span>') . "<br>";
echo "<strong>Mbstring Extension:</strong> " . (extension_loaded('mbstring') ? '<span style="color:green">ENABLED</span>' : '<span style="color:red">DISABLED</span>') . "<br>";
echo "<strong>BCMath Extension:</strong> " . (extension_loaded('bcmath') ? '<span style="color:green">ENABLED</span>' : '<span style="color:red">DISABLED</span>') . "<br>";

$envPath = __DIR__ . '/.env';
echo "<strong>.env File Exists:</strong> " . (file_exists($envPath) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO (Missing .env file!)</span>') . "<br>";

$storagePath = __DIR__ . '/storage';
echo "<strong>Storage Writable:</strong> " . (is_writable($storagePath) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO (Check storage folder permissions!)</span>') . "<br>";
