<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recuperar contraseña</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            color: #FEFEFE;
            text-emphasis: none;
            text-decoration: none;
        }
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #2B303A;
            padding: 1.5rem;
        }
        h1 {
            color: #92DCE5;
            margin: 1rem 0;
            letter-spacing: 3px;
            font-size: 10vw;
            font-weight: 500;
        }

        p {
            font-size: 1.2em;
        }

        img {
            width: 230px;
            height: 230px;
            margin: 1rem
        }

        a {
            font-size: 1.2em;
            background-color: #92DCE5;
            padding: .5rem 1rem;
            border-top-left-radius: 15px;
            border-bottom-right-radius: 15px;
            transition: background-color .2s ease;
        }

        a:hover {
            background-color: #64a4ad;
        }
    </style>
</head>
<body>
    <h1>GESTIONPISTAS</h1>
    <p>Para cambiar tu contraseña debes pulsar en el siguiente enlace</p>
    <img src="{{ URL::to('/') }}/images/router.png">
    <a href="http://127.0.0.1:8000/api/validation-token/{{ $email }}/{{ $token }}">Recuperar contraseña</a>
</body>
</html>