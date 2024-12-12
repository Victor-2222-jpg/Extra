<!DOCTYPE html>
<html>
<head>
    <title>Notificación de Usuario Confirmado</title>
</head>
<body>
    <h1>Un usuario ha confirmado su correo</h1>
    <p>Detalles del usuario:</p>
    <ul>
        <li><strong>Username:</strong> {{ $user->username }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
    </ul>

    <p>
        <a href="{{ $url }}" class="button">otorgar rol</a>
    </p>
</body> 
</html>
