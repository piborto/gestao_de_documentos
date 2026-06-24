<?php

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "qualidade_teste"; 

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=latin1"; 

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false, 
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" 
    );

    $conexao = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

?>