<?php

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/Poll.php');

try {
  $poll = new \MyApp\Poll();
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}

// $results = [
//   0 => 12,
//   1 => 22,
//   2 => 45
// ];

$results = $poll->getResults();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Poll Result</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h1>Result...</h1>
  <form action="" method="post">
    <div class="row">
      <?php foreach ($results as $num => $res) : ?>
        <div class="box" id="box_<?= h($num); ?>"><?= h($res); ?></div>
      <?php endforeach ; ?>
    </div>
    <a href="http://localhost/VoteSystem_php/a.php"><div id="btn">Go Back</div></a>
</body>
</html>
