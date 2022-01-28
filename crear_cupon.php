<?php

if (isset($_POST['crear'])) {

    include_once 'bdconect.php';
    if ($conn->connect_errno) {
        echo "Falló la conexión a MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
    }

    /** Función para quitar todos los espacios que el usuario pueda introducir de mas en los campos de nombre y apellido.
     * También da formato al nombre con la primer letra de cada palabra en mayúscula.
     */
    function clean_str($texto)
    {
        $texto = trim(preg_replace('/\s+/', ' ', $texto));
        $texto = ucwords(strtolower($texto));
        return $texto;
    }

    !isset($_POST['nombre']) ?: $nombre = clean_str($_POST['nombre']);
    !isset($_POST['apellido']) ?: $apellido = clean_str($_POST['apellido']);
    !isset($_POST['correo']) ?: $correo = $_POST['correo'];
    $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
    $correo = filter_var($correo, FILTER_VALIDATE_EMAIL);
    !isset($_POST['telefono']) ?: $telefono = $_POST['telefono'];

    $nombre_completo = str_replace(' ', '', $nombre . $apellido . $correo);

    // https://www.php.net/manual/es/function.password-hash.php
    // https://www.php.net/manual/es/password.constants.php
    // https://www.php.net/manual/es/function.crypt.php
    $opciones = array('cost' => 10);
    $pax_hashed = password_hash($nombre_completo, PASSWORD_BCRYPT, $opciones);

    try {
        $stmt = $conn->prepare("SELECT CONCAT(nombre,apellido,correo) as nombres_apellidos_correo, pax_hash
                                FROM pasajeros WHERE REPLACE(CONCAT(nombre,apellido,correo),' ', '') = ?");
        $stmt->bind_param("s", $nombre_completo);
        $stmt->execute();
        // con bind_result() podemos renombrar los campos que vienen en la consulta para un mejor control.
        // https://www.php.net/manual/es/mysqli-stmt.bind-result.php
        $stmt->bind_result($nombres_apellidos_correo, $pax_hash);

        if ($stmt->affected_rows) {

            $existe = $stmt->fetch();
            if ($existe) {
                if (password_verify($nombre_completo, $pax_hash)) {
                    echo "Este cupón ya existe. Intente con otro usuario.";
                    die();
                }
            } else {
                try {
                    $sql = "START TRANSACTION;";
                    $sql .= "INSERT INTO pasajeros (nombre, apellido, correo, telefono, pax_hash) VALUES ('$nombre', '$apellido', '$correo', '$telefono', '$pax_hashed');";
                    $sql .= "INSERT INTO cupones (id_pasajero) VALUES (LAST_INSERT_ID());";
                    $sql .= "SELECT id_cupon FROM cupones WHERE id_cupon = LAST_INSERT_ID();";
                    $sql .= "COMMIT;";

                    if (!$conn->multi_query($sql)) {
                        echo "Falló la multiconsulta: (" . $conn->errno . ") " . $conn->error;
                    }

                    do {
                        if ($resultado = $conn->store_result()) {
                            $row = ($resultado->fetch_all(MYSQLI_ASSOC));
                            foreach ($row as $cupon) :

                                // Crear la imagen usando la imagen base
                                $image = imagecreatefromjpeg('img/blanco.jpg');

                                // Asignar el color para el texto
                                $color = imagecolorallocate($image, 255, 19, 155);

                                // Asignar la ruta de la fuente
                                $font_path = __DIR__ . '\Roboto-Bold.ttf';

                                // Creamos el texto con el número de cupon
                                $text = "VL-" . "$cupon[id_cupon]"; // Texto 1

                                // imagettftext ( resource $image, tamaño fuente, aungulo inclinación, eje x, eje y, color texto, fuente, texto )
                                imagettftext($image, 40, 0, 250, 380, $color, $font_path, $text);

                                // https://www.php.net/manual/es/timezones.america.php
                                date_default_timezone_set('America/Cancun');
                                // Establecemos la configuración para dar formato de las fechas en español (por default están en inglés).
                                setlocale(LC_TIME, 'es-ES.UTF-8');

                                // Creamos el texto para la fecha de expedición del cupon
                                $valido = strftime('%d de %B del %Y');

                                // imagettftext ( resource $image, tamaño fuente, aungulo inclinación, eje x, eje y, color texto, fuente, texto )
                                imagettftext($image, 10, 0, 245, 495, $color, $font_path, $valido);

                                // Creamos el texto para la fecha de vencimiento del cupon
                                $fecha = date('j F Y');
                                $expira = strftime('%d de %B del %Y', strtotime($fecha . "+ 1 week"));

                                // imagettftext ( resource $image, tamaño fuente, aungulo inclinación, eje x, eje y, color texto, fuente, texto )
                                imagettftext($image, 10, 0, 245, 510, $color, $font_path, $expira);

                                // Enviar el contenido al navegador
                                imagepng($image, "cupones/" . "$cupon[id_cupon]" . ".jpg"); 

                                // Limpiar la memoria
                                imagedestroy($image);
                            endforeach;

                            $resultado->free();
                        }
                    } while ($conn->more_results() && $conn->next_result());
                } catch (Exception $e) {
                    // No se pudo conectar a la BD
                    echo "Error: " . $e->getMessage();
                }
            }
        }
    } catch (Exception $e) {
        // No se pudo conectar a la BD
        echo "Error: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
}
?>
<a href="cupones/<?php echo $cupon['id_cupon'] ?>.jpg" download="">
    <img style="width: 100%;" src="cupones/<?php echo $cupon['id_cupon'] ?>.jpg" alt="">
</a>
<br>
<a style="font-size:3em" href="cupones/<?php echo $cupon['id_cupon'] ?>.jpg" download="">
    Pulsa sobre la imagen o en este link para descargar tu cupon
</a>