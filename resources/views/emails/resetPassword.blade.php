<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recuperar contraseña</title>
</head>
<body>
    <p style="font-weight: bold; color: green">{{ $token }}</p>
    <a href="http://127.0.0.1:8000/api/validation-token/{{ $email }}/{{ $token }}">Recuperar contraseña</a>
</body>
</html>