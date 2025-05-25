<?php

include ("conexion.php");
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['eliminar'])) {
    $nombre = $_POST['eliminar'];

    // Eliminar archivos
    @unlink("uploads/$nombre");
    @unlink("uploads/thumbs/$nombre");

    // Eliminar de la base de datos
    $stmt = $conexion->prepare("DELETE FROM imagenes WHERE nombre_archivo = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
}

// Obtener imágenes
$resultado = $conexion->query("SELECT nombre_archivo FROM imagenes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería de Imágenes</title>
    <link rel="stylesheet" href="css/estilos.css">

</head>
<body>

<h1>Galería de Imágenes</h1>

<!-- Formulario de subida -->
<div class="subir-form">
  <div class="card-upload">
    <form action="subir_imagen_ajax.php" method="POST" enctype="multipart/form-data" id="formSubir">
      <label for="inputImagen" class="custom-file-upload">Seleccionar Imagen</label>
      <input type="file" name="imagen" id="inputImagen" accept="image/*" required>
      <input type="submit" value="Subir Imagen">
    </form>
    <img id="preview" src="#" alt="Vista previa">
  </div>
</div>

<!-- Galería -->
<div class="galeria">
    <?php while ($fila = $resultado->fetch_assoc()):
        $nombre = htmlspecialchars($fila['nombre_archivo']);
        $thumb = "uploads/thumbs/$nombre";
        $original = "uploads/$nombre";
    ?>
    <div class="imagen-container">
        <a href="#modal-<?= md5($nombre) ?>">
            <img src="<?= $thumb ?>" alt="Imagen">
        </a>
        <form method="POST" onsubmit="return confirm('¿Eliminar esta imagen?');">
            <input type="hidden" name="eliminar" value="<?= $nombre ?>">
            <button class="btn-eliminar" type="submit">✖</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="modal-<?= md5($nombre) ?>" class="modal">
        <a href="#" class="modal-close">&times;</a>
        <img src="<?= $original ?>" alt="Imagen grande">
    </div>
    <?php endwhile; ?>
</div>

<div id="mensaje"></div>
<script src="js/jscript.js"></script>

</body>
</html>

