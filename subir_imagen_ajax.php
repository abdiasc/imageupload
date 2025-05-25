<?php
header('Content-Type: application/json');

include ("conexion.php");

$directorio = "uploads/";
$directorioThumb = "uploads/thumbs/";

if (!file_exists($directorioThumb)) {
    mkdir($directorioThumb, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["imagen"])) {
    $archivo = $_FILES["imagen"];
    $nombreTemporal = $archivo["tmp_name"];
    $nombreOriginal = basename($archivo["name"]);
    $nombreFinal = uniqid() . "_" . $nombreOriginal;
    $rutaDestino = $directorio . $nombreFinal;
    $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

    $tiposPermitidos = ["jpg", "jpeg", "png", "gif"];
    if (in_array($tipoArchivo, $tiposPermitidos)) {
        if (move_uploaded_file($nombreTemporal, $rutaDestino)) {
            // Crear miniatura (máx 200px ancho)
            list($ancho, $alto) = getimagesize($rutaDestino);
            $nuevoAncho = 200;
            $nuevoAlto = intval($alto * ($nuevoAncho / $ancho));

            $thumb = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            switch ($tipoArchivo) {
                case 'jpg':
                case 'jpeg':
                    $origen = imagecreatefromjpeg($rutaDestino);
                    break;
                case 'png':
                    $origen = imagecreatefrompng($rutaDestino);
                    break;
                case 'gif':
                    $origen = imagecreatefromgif($rutaDestino);
                    break;
                default:
                    echo json_encode(['error' => 'Tipo de imagen no soportado']);
                    exit;
            }

            imagecopyresampled($thumb, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            $rutaThumb = $directorioThumb . $nombreFinal;
            switch ($tipoArchivo) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumb, $rutaThumb);
                    break;
                case 'png':
                    imagepng($thumb, $rutaThumb);
                    break;
                case 'gif':
                    imagegif($thumb, $rutaThumb);
                    break;
            }

            imagedestroy($origen);
            imagedestroy($thumb);

            // Guardar en base de datos
            $stmt = $conexion->prepare("INSERT INTO imagenes (nombre_archivo) VALUES (?)");
            $stmt->bind_param("s", $nombreFinal);
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'nombre' => $nombreFinal,
                    'thumb' => $rutaThumb,
                    'original' => $rutaDestino
                ]);
            } else {
                echo json_encode(['error' => 'Error al guardar en la base de datos']);
            }
        } else {
            echo json_encode(['error' => 'Error al mover el archivo']);
        }
    } else {
        echo json_encode(['error' => 'Tipo de archivo no permitido']);
    }
} else {
    echo json_encode(['error' => 'No se recibió ninguna imagen']);
}
?>
