<?php
include('vendor/autoload.php');
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Validate;
use ReallySimpleJWT\Encode;

header('Content-Type: application/json');
if (isset($_POST['access']))
{
    include('connect.php');
    if (validateToken($_POST['access'], $key))
    {
       $payload = Token :: getPayload ( $_POST['access'], $key);
       $userID = $payload["user_id"];
       $table = 'users';
       $sql = "SELECT * from " . $table . " where id = '" . $userID . "'";
       $result = $connection->query($sql);
       $row = $result->fetch(PDO::FETCH_NUM);
       
       $data = [ 'id' => $row[0], 
                 'login' => $row[1],
                 'regEmail' => $row[4],
                 'email' => $row[5],
                 'regDate' => $row[7] ];
    
       echo json_encode( $data );
    
    }
}elseif (isset($_POST['refresh'])) {
    include('connect.php');
    if (validateToken($_POST['refresh'], $key))
    {
       include('connect.php');
       $payload = Token :: getPayload ( $_POST['refresh'], $key);
       $userID = $payload["user_id"];
       $table = 'users';
       $sql = "SELECT * from " . $table . " where id = '" . $userID . "'";
       $result = $connection->query($sql);
       $row = $result->fetch(PDO::FETCH_NUM);
       $accessToken = generateAccessToken($row[0], $key, "hisduel.000webhost.com");
       $data = [ 'access' => $accessToken ];
       echo json_encode( $data );
    }
} elseif (isset($_POST['login']) and isset($_POST['password'])) {
    include('connect.php');
    $login = $_POST['login'];
    $password = $_POST['password'];
    $table = 'users';
    if (strpos($login, '@') !== false)
    {
       $sql = "SELECT * from " . $table . " where registrationEmail = '" . $login . "'";       
    }
    else
    {
       $sql = "SELECT * from " . $table . " where login = '" . $login . "'";
    }
    $result = $connection->query($sql);
    $row = $result->fetch(PDO::FETCH_NUM);
    if (md5($password.$row[3].$secret) == $row[2])
    {
        $accessToken = generateAccessToken($row[0], $key, "hisduel.000webhost.com");
        $refreshToken = generateRefreshToken($row[0], $key, "hisduel.000webhost.com");
        $data = [ 'access' => $accessToken, 'refresh' => $refreshToken ];
        echo json_encode( $data );
    }
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