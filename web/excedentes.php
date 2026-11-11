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

$consulta = $conexion->query(
    "SELECT producto, productor, pueblo, kilos, precio_kg
     FROM excedentes
     WHERE kilos > 0
     ORDER BY id"
);
$excedentes = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>NovaHarvest - Excedentes disponibles</title>
    <link rel="stylesheet" href="/web/css/estilos.css">
</head>
<body>
<header>
    <img src="/web/img/logo.png" alt="Logotipo de NovaHarvest" width="180">
    <h1>NovaHarvest</h1>
    <nav>
        <a href="/">Inicio</a>
        <a href="/web/excedentes.php">Excedentes</a>
        <a href="/web/avisos.html">Avísame</a>
        <a href="/web/temporada.md">Temporada</a>
        <a href="/web/estado.php">Estado</a>
    </nav>
</header>
<main>
    <h1>Excedentes disponibles</h1>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Productor</th>
                <th>Pueblo</th>
                <th>Kilos</th>
                <th>Precio/kg</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($excedentes as $excedente): ?>
            <tr>
                <td><?= $excedente['producto'] ?></td>
                <td><?= $excedente['productor'] ?></td>
                <td><?= $excedente['pueblo'] ?></td>
                <td><?= $excedente['kilos'] ?></td>
                <td><?= number_format($excedente['precio_kg'], 2, ',', '.') ?> €</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<footer>NovaHarvest · IES Valle del Jerte · Curso 2026-2027</footer>
</body>
</html>
