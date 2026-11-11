<?php
$fecha = date('d/m/Y H:i:s');
$contenedor = getenv('HOSTNAME');
$host = getenv('DB_HOST');
$nombreBD = getenv('DB_NAME');
$usuario = getenv('DB_USER');
$contrasena = getenv('DB_PASS');
$conexion = new PDO(
    "mysql:host={$host};dbname={$nombreBD};charset=utf8mb4",
    $usuario,
    $contrasena
);
$version = $conexion->query("SELECT VERSION()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>NovaHarvest - Estado del servidor</title>
    <link rel="stylesheet" href="/web/css/estilos.css">
</head>
<body>
<header>
    <img src="/web/img/logo.png" alt="Logotipo de NovaHarvest" width="180">
    <h1>NovaHarvest</h1>
    <nav>
        <a href="/web/index.html">Inicio</a>
        <a href="/web/excedentes.php">Excedentes</a>
        <a href="/web/avisos.html">Avísame</a>
        <a href="/web/temporada.md">Temporada</a>
        <a href="/web/estado.php">Estado</a>
    </nav>
</header>
<main>
    <h1>Estado del servidor</h1>
    <p>Fecha y hora del servidor: <?= $fecha ?></p>
    <p>Contenedor PHP: <?= $contenedor ?></p>
    <p>Base de datos: MariaDB <?= $version ?></p>
    <p>Página generada por PHP-FPM</p>
</main>
<footer>NovaHarvest · IES Valle del Jerte · Curso 2026-2027</footer>
</body>
</html>
