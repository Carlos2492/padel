<?php

function redimensionarImagen($origin, $destino, $newWidth, $newHeight, $jpgQuality = 100) {
    // getimagesize devuelve un array con: anchura,altura,tipo,cadena de 
    // texto con el valor correcto height="yyy" width="xxx"
    $datos = getimagesize($origin);

    // comprobamos que la imagen sea superior a los tamaños de la nueva imagen
    if ($datos[0] > $newWidth || $datos[1] > $newHeight) {

        // creamos una nueva imagen desde el original dependiendo del tipo
        if ($datos[2] == 1)
            $img = imagecreatefromgif($origin);
        if ($datos[2] == 2)
            $img = imagecreatefromjpeg($origin);
        if ($datos[2] == 3)
            $img = imagecreatefrompng($origin);

        // Redimensionamos proporcionalmente
        if (rad2deg(atan($datos[0] / $datos[1])) > rad2deg(atan($newWidth / $newHeight))) {
            $anchura = $newWidth;
            $altura = round(($datos[1] * $newWidth) / $datos[0]);
        } else {
            $altura = $newHeight;
            $anchura = round(($datos[0] * $newHeight) / $datos[1]);
        }

        // creamos la imagen nueva
        $newImage = imagecreatetruecolor($anchura, $altura);

        // redimensiona la imagen original copiandola en la imagen
        imagecopyresampled($newImage, $img, 0, 0, 0, 0, $anchura, $altura, $datos[0], $datos[1]);

        // guardar la nueva imagen redimensionada donde indicia $destino
        if ($datos[2] == 1)
            imagegif($newImage, $destino);
        if ($datos[2] == 2)
            imagejpeg($newImage, $destino, $jpgQuality);
        if ($datos[2] == 3)
            imagepng($newImage, $destino);

        // eliminamos la imagen temporal
        imagedestroy($newImage);

        return true;
    }
    return false;
}

    

