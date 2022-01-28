<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Spartan:wght@100&display=swap" rel="stylesheet">
    <title>Tu cupón de Descuento</title>
</head>

<body>
    <div class="titulo wrapper">
        <h1>Regístrate y obtén <span>$500 pesos de descuento</span> en cualquiera de nuestros servicios.</h1>
    </div>

    <div>
        <form class="formulario" action="crear_cupon.php" method="POST">
            <img src="img/logo.jpg" alt="VL-Receptivo">
            <input type="text" placeholder="Nombre(s)" name="nombre" required>
            <input type="text" placeholder="Apellido(s)" name="apellido" required>
            <input type="email" placeholder="email" name="correo" required>
            <input type="tel" placeholder="Teléfono" name="telefono" required>
            <input class="button" type="submit" value="Crear cupón" name="crear" required>
        </form>
    </div>
</body>

</html>