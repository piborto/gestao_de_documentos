<?php
// config/conexao.php

// No Docker, o host é o nome do serviço do banco de dados (db) configurado no docker-compose.yml
$db_host = "db"; 
$db_user = "root";
$db_pass = "root"; // Senha que definimos no docker-compose
$db_name = "qualidade_teste"; 

try {
    // Mantendo a sua lógica perfeita com PDO
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=latin1"; 

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false, 
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" 
    );

    // Salvando na variável $conexao para usarmos nos nossos Models
    $conexao = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

?>