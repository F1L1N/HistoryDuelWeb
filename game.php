<?php
header('Content-Type: application/json');

if (isset($_POST['mode']))
{
    include('connect.php'); 
    switch ($_POST['mode']) 
    {
        case 0:
            //добавление в БД записи об игре
            getMatchID($connection);
            break;
        case 1:
            //выдача случайного вопроса по запросу
            getQuestionByRandom($connection);
            break;
        case 2:
            //оценка ответа игрока
            estimateAnswer($connection);
            break;
        case 3:
            //проверка статуса игры
            checkMistake($connection);
            break;
        case 4:
            //отправка данных о финале игры
            sendTotalGame($connection);
            break;
        case 5:
            //обновление данных об игре
            updateGame($connection);
            break;  
        case 6:
            //занесение в бд текущего вопроса
            setQuestionByHost($connection);
            break;
        case 7:
            //запрос на завершение игры
            closeGame($connection);
            break;            
    }
}

function getMatchID ($connection)
{
    $sql = "SELECT matchID from matchmaking where player_id = ".$_POST['id'];
                   
    $id = $connection->query($sql); 
    
    $data = [ 'gameId' => $id];
    
    echo json_encode( $data );  
}

function getQuestionByRandom($connection)
{
    $sql = "select id from questions";
    $questionsIds = $connection->query($sql)->fetchAll();
    
    $sql = "SELECT * from questions where id = ".$questionsIds[array_rand($questionsIds)]['id'];
    $question = $connection->query($sql)->fetch(PDO::FETCH_NUM);
    
    $data = ['id' => $question[0], 
             'question' => $question[1],
             'variant1' => $question[2],
             'variant2' => $question[3],
             'variant3' => $question[4],
             'variant4' => $question[5]];
     
    echo json_encode( $data );         
}

function setQuestionByHost($connection)
{
    $sql = "SELECT id from questions";
    $questionsIds = $connection->query($sql)->fetchAll();
    
    $sql = "SELECT player1_id, questions from games where id = ".$_POST['gameId'];
    $gameInfo = $connection->query($sql)->fetch(PDO::FETCH_NUM);
    if ($gameInfo[0] == $_POST['id'])
    {
        $currentQuestionId = $questionsIds[array_rand($questionsIds)]['id'];
        $sql = "UPDATE games".
                " SET current = ".$currentQuestionId.", questions = '".$gameInfo[1]." ".$currentQuestionId.
                "' WHERE id = ".$_POST['gameId'];    
        $connection->query($sql);    
    }
}

function estimateAnswer($connection)
{
    $sql = "SELECT right_variant from questions where id = ".$_POST['questionId'];
    $questionAnswer = $connection->query($sql)->fetch(PDO::FETCH_NUM);
    updateMistake($connection, $_POST['id'], $questionAnswer[0] == $_POST['answer']); 
    
    $data = [ 'estimation' => $answer];
    echo json_encode( $data );  
    
    //checkMistake($connection);
}

function updateMistake($connection, $playerId, $answer)
{
    if (!$answer)
    {
        $sql = "SELECT player1_id, player2_id, player1_mistakes, player2_mistakes
                from games where id = ".$_POST['gameId'];
        $playersInfo = $connection->query($sql)->fetch(PDO::FETCH_NUM);        
        if ($playersInfo[0] == $playerId)
        {
            $playerMistakes = $playersInfo[2] + 1;
            $sql = "UPDATE games
                SET player1_mistakes = ".$playerMistakes."
                WHERE id = ".$_POST['gameId'];
            $connection->query($sql);
        }
        else
        {
            $playerMistakes = $playersInfo[3] + 1;
            $sql = "UPDATE games
                SET player2_mistakes = ".$playerMistakes."
                WHERE id = ".$_POST['gameId'];
            $connection->query($sql);
        }
    }
}

function checkMistake($connection)
{
    $sql = "SELECT player1_id, player2_id, player1_mistakes, player2_mistakes
                from games where id = ".$_POST['gameId'];
    $playersInfo = $connection->query($sql)->fetch(PDO::FETCH_NUM); 
    if ($playersInfo[2] == 5)
    {
        $sql = "UPDATE games
                SET win = ".$playersInfo[1]."
                WHERE id = ".$_POST['gameId'];
        $connection->query($sql);        
    }
    else
    {
        if ($playersInfo[3] == 5)
        {
            $sql = "UPDATE games
                    SET win = ".$playersInfo[0]."
                    WHERE id = ".$_POST['gameId'];
            $connection->query($sql);   
        }
        else
        {
            if (($playersInfo[2] == 5) and ($playersInfo[3] == 5))
            {
                $sql = "UPDATE games
                        SET win = 0
                        WHERE id = ".$_POST['gameId'];
                $connection->query($sql);                  
            }
        }
    }
}

function sendTotalGame($connection)
{
    $sql = "SELECT win from games where id = ".$_POST['gameId'];
    $gameInfo = $connection->query($sql)->fetch(PDO::FETCH_NUM); 
    if ($gameInfo[0] == $_POST['id'])
    {
        $data = [ 'result' => 'WIN'];
    }
    else 
    {
        if ($gameInfo[0] == 0)
        {
            $data = [ 'result' => 'DRAW'];
        }
        else 
        {
            $data = [ 'result' => 'LOSE'];
        }
    }
    closeGame($connection);
    echo json_encode( $data );   
}

function closeGame($connection)
{
    $sql = "UPDATE matchmaking
            SET status = -1, opponent_id = 0, matchID = 0
            WHERE player_id = ".$_POST['id'];
    $connection->query($sql);
}