<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        p {
            font-size: 12px;
        }

        .signature {
            font-style: italic;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div>
    <p>Hola {{ $user->username }},</p>
    <p>{{ $informacion }} 😉</p>
    <p>
        <a href="{{ $url }}" class="button">Confirmar mi correo</a>
    </p>
    <p class="signature">Mailtrap</p>
</div>
</body>
</html>
