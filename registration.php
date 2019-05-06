<?php
include('vendor/autoload.php');
use ReallySimpleJWT\Token;

header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"]=="POST") {
    include('connect.php');

    $table = "users";
    $login = $_POST['login'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $salt = generateRandomString(3);
    $password = md5($password.$salt.$secret);
    
    $sql = "INSERT INTO " . $table . " (id, login, password, salt, registrationEmail) 
                   VALUES (0, '" . $login . "', '" . $password . "', '" . $salt . "', '" . $email . "')";
                   
    $connection->query($sql);  
    
    $sql = "SELECT id from " . $table . " where login = '" . $login . "'";
    
    $result = $connection->query($sql);
    
    $row = $result->fetch(PDO::FETCH_NUM);
    
    $sql = "INSERT INTO matchmaking (player_id, status, opponent_id, matchID) 
                   VALUES (".$row[0].", -1, 0, -1)";
                   
    $connection->query($sql); 
    
    $accessToken = generateAccessToken($row[0], $key, "hisduel.000webhost.com");
    
    $refreshToken = generateRefreshToken($row[0], $key, "hisduel.000webhost.com");

    $data = [ 'access' => $accessToken, 'refresh' => $refreshToken ];
    
    echo json_encode( $data );

}

function generateRandomString($length)
{
    $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ';
    $numChars = strlen($chars);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= substr($chars, rand(1, $numChars) - 1, 1);
    }
    return $string;
}

function generateAccessToken($userID, $secret, $issuer)
{
    $expiration = time() + 3600 * 24 * 14;
    return Token::create($userID, $secret, $expiration, $issuer);
}

function generateRefreshToken($userID, $secret, $issuer)
{
    $expiration = time() + 3600 * 24 * 28;
    return Token::create($userID, $secret, $expiration, $issuer);
}

function validateToken($token, $key)
{
    return Token::validate($token, $key);
}