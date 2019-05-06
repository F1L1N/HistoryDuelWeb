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

function findOpponent($connection) : ?string
{
    while (true)
    {
        $sql = "SELECT player_id from matchmaking 
                    where status = 0 and opponent_id = ".$_POST['id'];
        $opponentId = $connection->query($sql)->fetch(PDO::FETCH_NUM);
        if ($opponentId)
        {
            return $opponentId[0];
        }
        else
        {
            $sql = "SELECT player_id from matchmaking 
                        where status = 0 and player_id != ".$_POST['id'];
            $opponentIds = $connection->query($sql)->fetch(PDO::FETCH_NUM);
            if ($opponentIds)
            {
                $opponentId = $opponentIds[array_rand($opponentIds)]; 
                return $opponentId;
            }
            sleep(2);
        }
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
        createGame($connection, $_POST['id'], $opponentId);
        sleep(1);
        waitOpponent($connection, $opponentId);
    }
}

function createGame($connection, $playerId, $opponentId)
{
    $sql = "INSERT INTO games (player1_id, player2_id, win, player1_mistakes, player2_mistakes, questions, current) 
                   VALUES (".$playerId.", ".$opponentId.", -1, 0, 0, '', 0)";
                   
    $connection->query($sql);
    
    $sql = "SELECT id from games where player1_id = ".$playerId." and player2_id = ".$opponentId;
                   
    $id = $connection->query($sql)->fetch(PDO::FETCH_NUM);
    
    $sql = "UPDATE matchmaking
            SET matchID = ".$id[0].
           " WHERE player_id = ".$playerId;
            
    $connection->query($sql);
    
    $sql = "UPDATE matchmaking
            SET matchID = ".$id[0].
           " WHERE player_id = ".$opponentId;
            
    $connection->query($sql);
}