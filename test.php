<?php
header('Content-Type: application/json');
if (isset($_POST['id']))
{
    include('connect.php');
    setSearchingStatus($connection, 0);
    $opponentId = findOpponent($connection);
    setOpponentId($connection, $opponentId);
    waitOpponent($connection, $opponentId);
}

function setOpponentId($connection, $opponentId)
{
    $sql = "UPDATE matchmaking
                SET opponent_id = ". $opponentId ." WHERE player_id = ".$_POST['id'];
    $connection->query($sql);       
}

function setSearchingStatus($connection, $status)
{
    $sql = "UPDATE matchmaking
                SET status = ". $status ." WHERE player_id = ".$_POST['id'];
    $connection->query($sql);       
}

function findOpponent($connection)
{
    $sql = "SELECT player_id from matchmaking 
                where status = 0 and opponent_id = ".$_POST['id'];
    $opponentId = $connection->query($sql)->fetch(PDO::FETCH_ASSOC);
    if ($opponentId)
    {
        return $opponentId['player_id'];
    }
    else
    {
        $sql = "SELECT player_id from matchmaking 
                    where status = 0 and player_id != ".$_POST['id'];
        $opponentIds = $connection->query($sql)->fetch(PDO::FETCH_NUM);
        $opponentId = $opponentIds[array_rand($opponentIds)];
        if (isset($opponentId))
        {
            return $opponentId;
        }
        sleep(1);
        findOpponent($connection);
    }
}

function waitOpponent($connection, $opponentId)
{
    $sql = "SELECT opponent_id from matchmaking where player_id = ".$opponentId;
    $check = $connection->query($sql);
    $result = $check->fetch(PDO::FETCH_NUM);
    if ($result[0] == $_POST['id'])
    {
        $sql = "UPDATE matchmaking
                SET status = 1, opponent_id = ".$opponentId."
                WHERE player_id = ".$_POST['id'];
        $connection->query($sql);
        $data = [ 'opponentId' => $opponentId ];
        echo json_encode( $data );
    }
    else
    {
        sleep(1);
        waitOpponent($connection, $opponentId);
    }
}