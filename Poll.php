<?php

namespace MyApp;


class Poll {
  private $_db;

  public function __construct() {
    $this->_connectDB();
    $this->_createToken();
  }

  private function _createToken() {
    if (!isset($_SESSION['token'])) {
      $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
    }
  }

  private function _validateToken() {
    if (!isset($_SESSION['token']) ||
        !isset($_POST['token']) ||
        $_SESSION['token'] !== $_POST['token']
    ) {
      throw new \Exception("invalid token!");
    }
  }

  public function getResults() {
    //array_fill(0から3つ(0~2)の値を第三引数で埋める。0=>0,1=>0,2=>0となる。)
    $data = array_fill(0, 3, 0);
    $sql = "select answer, count(id) as c from answers group by answer";
    foreach ($this->_db->query($sql) as $row) {
      $data[$row['answer']] = (int)$row['c'];
    }


    return $data;
  }

  public function post() {
    try {
      //CSRF対策でPOSTされた時にSESSIONに保持してあるTokenが合致するか検証する。
      $this->_validateToken();
      $this->_validateAnswer();
      $this->_save();
      //redirect to result.php
      //リダイレクトの仕方
      header('Location: http://localhost/VoteSystem_php/result.php');
    } catch (\Exception $e) {
      // set error
      $_SESSION['err'] = $e->getMessage();

      //redirect to index.php
      //失敗したら
      header('Location: http://localhost/VoteSystem_php/a.php');
    }
    exit;
  }

  public function _validateAnswer() {
    // var_dump($_POST);
    // exit;
    if (!isset($_POST['answer']) ||
        !in_array($_POST['answer'], [0, 1, 2])) {
      //例外を投げた時に,例外をキャッチしてくれるのはpost()が呼び出されてるとこの中のcatchとなる。
      throw new \Exception('invalid answer!');
    }
  }

  private function _save() {
    //sqlにデータ保存なので、sql文を書いていきます。
    $sql = "insert into answers
            (answer, created, remote_addr, user_agent, answer_date)
            values (:answer, now(), :remote_addr, :user_agent, now())";
    $stmt = $this->_db->prepare($sql);
    //型を指定する。
    $stmt->bindValue(':answer', (int)$_POST['answer'], \PDO::PARAM_INT);
    $stmt->bindValue(':remote_addr', $_SESSION['REMOTE_ADDR'], \PDO::PARAM_STR);
    $stmt->bindValue(':answer', $_SESSION['HTTP_USER_AGENT'], \PDO::PARAM_STR);

    try {
      $stmt->execute();
      // exit;
    } catch (\PDOException $e) {
      throw new \Exception('No more vote for today!');
    }


  }

  public function getError() {
    //エラーメッセージを初期化
    $err = null;
    if (isset($_SESSION['err'])) {
      $err = $_SESSION['err'];
      //SESSIONにデータを残しておくのはあまりよくないのでunsetする。
      unset($_SESSION['err']);
    }
    return $err;
  }

  private function _connectDB() {
      try {
        $this->_db = new \PDO(DSN, DB_USERNAME, DB_PASSWORD);
        $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      } catch (\PDOException $e) {
        throw new \Exception("Failed to connect_DB");

      }

  }
}
 ?>
