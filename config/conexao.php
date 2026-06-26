<?php

// Força o PHP a usar UTF-8 em todas as operações, incluindo sessões.
// Esta é a configuração mais importante para garantir a consistência da codificação.
ini_set('default_charset', 'UTF-8');

// Garante que todas as páginas que usam esta conexão enviem o cabeçalho UTF-8.
// Esta é a forma mais robusta de garantir a correta exibição de acentos.
header('Content-Type: text/html; charset=utf-8');

if (session_id() == "") {
    session_start();
}
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "qualidade_teste"; 

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=latin1"; 

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false
    );

    $conexao = new PDO($dsn, $db_user, $db_pass, $options);

    if (basename($_SERVER['PHP_SELF']) == 'conexao.php') {
        echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid #28a745; background-color: #e9f7ef; color: #155724; margin: 20px;'>";
        echo "<h2>Conexão com o Banco de Dados bem-sucedida!</h2>";
        echo "<p>A aplicação conectou-se com sucesso ao banco de dados '<strong>" . htmlspecialchars($db_name) . "</strong>' no host '<strong>" . htmlspecialchars($db_host) . "</strong>'.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    die("<div style='font-family: sans-serif; padding: 20px; border: 2px solid #dc3545; background-color: #f8d7da; color: #721c24; margin: 20px;'><h2>Falha na Conexão com o Banco de Dados!</h2><p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p></div>");
}

?>