<?php
function generarPassword($longitud = 8) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $indice = random_int(0, strlen($caracteres) - 1);
        $password .= $caracteres[$indice];
    }
    
    return $password;
}

// Ejemplo de uso
//$contrasenaGenerada = generarPassword(10);
