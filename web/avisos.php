<?php
$host = getenv('DB_HOST');
$nombreBD = getenv('DB_NAME');
$usuario = getenv('DB_USER');
$contrasena = getenv('DB_PASS');
$conexion = new PDO(
    "mysql:host={$host};dbname={$nombreBD};charset=utf8mb4",
    $usuario,
    $contrasena
);
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$consulta = $conexion->prepare(
    "INSERT INTO avisos (nombre, email, producto)
     VALUES (:nombre, :email, :producto)"
);
$consulta->execute([
    ':nombre' => $_POST['nombre'],
    ':email' => $_POST['email'],
    ':producto' => $_POST['producto']
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>NovaHarvest - Aviso registrado</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <img src="img/logo.png" alt="Logotipo de NovaHarvest" width="180">
    <h1>NovaHarvest</h1>
    <nav>
        <a href="index.html">Inicio</a>
        <a href="excedentes.php">Excedentes</a>
        <a href="avisos.html">Avísame</a>
        <a href="temporada.md">Temporada</a>
        <a href="estado.php">Estado</a>
    </nav>
</header>
<main>
    <h1>Aviso registrado</h1>
    <p>Gracias. Te avisaremos cuando haya excedentes del producto que buscas.</p>
    <p><a href="excedentes.php">Consultar excedentes disponibles</a></p>
</main>
<footer>NovaHarvest · IES Valle del Jerte · Curso 2026-2027</footer>
</body>
</html>
