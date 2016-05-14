<?php
session_start();

require_once(dirname(__FILE__)."/../src/Facebook/autoload.php");
$app_id = '232959010414896';
$app_secret = '80c616ceee375dfa9d564b33d4e1d451';
$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => 'v2.6',
  'default_access_token' => $app_id . '|' . $app_secret
]);

$lista_de_pages = array('theballisonthetable','798157940318724');

echo '<table border="1" style="font-family:arial; font-size:7px;">';
foreach($lista_de_pages as $page){
  $response = $fb->get('/'.$page.'?fields=feed.limit(100)');
  $graphNode = $response->getGraphNode();
  foreach ($graphNode['feed'] as $key => $post) {
    echo '<tr>';
    echo '<td>' .$page.':'. $key . ':' . $post['id'] . ':' . $post['message'] . '</td>';
    echo '</tr>';
    if (strpos($post['message'], '#apostinha') !== false) {
      
      $tags = explode('#',$post['message']);
      foreach ($tags as $tag)
      {
        echo $tag.'<br>';
        if (strpos($tag, 'Jogo_') !== false) {
          $JogoID = substr($tag,6);
          break;
        }
      }
      
      $response = $fb->get($post['id'].'?fields=comments.limit(999)');
      $graphNodePost = $response->getGraphNode();
      foreach ($graphNodePost['comments'] as $key => $comentario) {
          echo '<tr>';
          echo '<td>' . $key .  ':' . $comentario['id'] . '</td>';
          echo '<td>' . $key .  ':' . $comentario['message'] . '</td>';
          echo '<td>' . $key .  ':' . $comentario['from']['name'] . '</td>';
          echo '<td>' . $key .  ':' . $comentario['from']['id'] . '</td>';
          $created_timeOBJ = $comentario['created_time'];
          $created_timeSTR = $created_timeOBJ->format('Y-m-d H:i:s'); 
          $created_time = strtotime($created_timeSTR); //unix
          echo '<td>' . $key . ':' . $created_timeSTR . '</td>';
          echo '</tr>';
          $tags = explode('#',$comentario['message']);
          foreach ($tags as $tag)
          {
              $UserAposta = $tag;
              break;
          }
          
          
          InsereTabela($JogoID, $page, $post['id'], $comentario['id'], $comentario['from']['id'], $comentario['from']['name'], $UserAposta, $created_time);
      }
    }
  }
}
echo '</table>';
echo '<br>fim<br>';


function InsereTabela($JogoID, $PageID, $PostID, $CommentID, $UserID, $UserName, $UserAposta, $UserApostaTime){
  $dbopts = parse_url(getenv('DATABASE_URL'));
  $dsn = "pgsql:"
      . "host=" . $dbopts["host"] . ";"
      . "dbname=". ltrim($dbopts["path"],'/') . ";"
      . "user=" . $dbopts["user"] . ";"
      . "port=" . $dbopts["port"] . ";"
      . "sslmode=require;"
      . "password=" . $dbopts["pass"];
  $db = new PDO($dsn);
  $query = "INSERT INTO Apostas (JogoID, PageID, PostID, CommentID, UserID, UserName, UserAposta, UserApostaTime) VALUES"
      . "('".$JogoID."', '".$PageID."', '".$PostID."', '".$CommentID."', ".$UserID.", '".$UserName."', '".$UserAposta."', '.$UserApostaTime.');";
  echo '<br>'.$query;
  $result = $db->query($query);
  echo var_dump($result);
  $result->closeCursor();
  }
  
?>
