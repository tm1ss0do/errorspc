<?php
//-------------------------
//ログをとる
//-------------------------
ini_set('error_log','php.log');
ini_set('log_errrors','on');
//-------------------------
//デバッグ用関数
//-------------------------
$debug_flg = true;
if($debug_flg){
  function debug($str){
    error_log('デバッグ：'.$str);
  }
}
//-------------------------
//セッション開始
//-------------------------
session_start();

//-------------------------
//抽象クラス
//-------------------------
abstract class Appearance{
  //プロパティ
  protected $name;
  protected $hp;
  protected $attack_min;
  protected $attack_max;
  //セッター
  public function setName($str){
    $this->name = $str;
  }
  public function setHp($str){
    $this->hp = $str;
  }
  //ゲッター
  public function getName(){
    return $this->name;
  }
  public function getHp(){
    return $this->hp;
  }
  public function getAttackMin(){
    return filter_var($this->attack_min, FILTER_VALIDATE_INT);
  }
  public function getAttackMax(){
    return filter_var($this->attack_max, FILTER_VALIDATE_INT);
  }
  public function getParsonality(){
    return $this->parsonality;
  }
  //抽象メソッド
  abstract public function setSerif();
  //メソッド
  public function attack($target){
    History::set($this->name.'の攻撃！');
    $this->setSerif();
    $attackMin = $this->getAttackMin();
    $attackMax = $this->getAttackMax();
    $attackPoint = floor( mt_rand($attackMin,$attackMax) );
    //人のアタックポイント分、エラーのHPを減らす
    $target->setHp( $target->getHp() - $attackPoint );
    History::set($target->getName().'に'.$attackPoint.'ポイントのダメージ！');
  }
}

//-------------------------
//HUMANクラス
//-------------------------

//----------------
//-----parsonality
//----------------
class Parsonality{
  const OPTIM = 1;
  const PESSIM = 2;
}


class Human extends Appearance{
  //プロパティ
  protected $parsonality;
  //ゲッター
  public function getParsonality(){
    return $this->parsonality;
  }
  //コンストラクタ
  public function __construct($name,$hp,$attack_min,$attack_max,$parsonality){
    $this->name = $name;
    $this->hp = $hp;
    $this->attack_min = $attack_min;
    $this->attack_max = $attack_max;
    $this->parsonality = $parsonality;
  }
  //メソッド
  public function setSerif(){
    if($this->parsonality = Parsonality::OPTIM){
      History::set('「まぁなんとかなるでしょ」');
    }elseif($this->parsonality = Parsonality::PESSIM){
      History::set('「ああアAAA...もうやだ...」');
    }
  }

}

//-------------------------
//ERRORSクラス
//-------------------------

//----------------
//-----エラーレベル
//----------------

class ErrLv{
  const NOTICE =
  '[21-Oct-2019 12:45:39 UTC] PHP Notice:  Undefined variable: m_attack in /Applications/MAMP/htdocs/index.php on line 39 ';
  const WARNING =
  '[21-Oct-2019 11:36:37 UTC] PHP Warning:  Creating default object from empty value in /Applications/MAMP/htdocs/index.php on line 106';
  const FATAL =
  '[21-Oct-2019 11:36:37 UTC] PHP Fatal error:  Uncaught Error: Call to undefined method stdClass::attack() in /Applications/MAMP/htdocs/index.php:110
  Stack trace:
  #0 {main}
    thrown in /Applications/MAMP/htdocs/index.php on line 110';
}

class CodeErr extends Appearance{
  //プロパティ
  protected $err_lv;
  //コンストラクタ
  public function __construct($name,$hp,$attack_min,$attack_max,$err_lv){
    $this->name = $name;
    $this->hp = $hp;
    $this->attack_min = $attack_min;
    $this->attack_max = $attack_max;
    $this->err_lv = $err_lv;
  }
  public function getErr_lv(){
    return $this->err_lv;
  }
  //メソッド
  public function setSerif(){
    switch ($this->err_lv){
      case ErrLv::NOTICE:
        History::set('「Notice( ・∇・)」');
        break;
      case ErrLv::WARNING:
        History::set('「Warning!Warning!(☝︎ ՞ਊ ՞)☝︎」');
        break;
      case ErrLv::FATAL:
        History::set('「Fatal..._:(´ཀ`」 ∠):」');
        break;
    }

  }

}
//-------------------------
//HISTORYクラス
//-------------------------

interface HistoryInterface{
  public static function set($str);
  public static function setN($str);
}

class History implements HistoryInterface{
  //クラスメソッド
  public static function set($str){
    if( !empty($_SESSION['history']) ){
      $_SESSION['history'] .= $str.'</br>';
    }
  }
  public static function setN($str){
    $_SESSION['history'] = $str.'</br>';
  }
}

//----------------
//-----表示用変数
//----------------
$humans = array();
$errors = array();

//-------------------------
//インスタンス
//-------------------------
$errors[] = new CodeErr('NOTICE',200,10,20,ErrLv::NOTICE);
$errors[] = new CodeErr('WARNING',300,20,30,ErrLv::WARNING);
$errors[] = new CodeErr('FATAL',400,30,40,ErrLv::FATAL);
$humans[] = new Human('元気な人',300,200,300,Parsonality::OPTIM);
$humans[] = new Human('疲れた人',200,200,250,Parsonality::PESSIM);


//-------------------------
//関数
//-------------------------
//--------人を登場させる
function createHuman(){
  debug('人を登場させます');
  global $humans;
  $human = $humans[ floor(mt_rand(0,1)) ];
  $_SESSION['human'] = $human;
}

//--------エラーを発生させる
function createErr(){
  debug('エラーを発生させます');
  global $errors;
  $error = $errors[ floor(mt_rand(0,2)) ];
  $_SESSION['error'] = $error;
  History::set($error->getName().'エラーが出た！');
}

//--------初期化
function init(){
  History::setN('「エラー出ちゃった・・・」');
  //unset($_SESSION['error']);
  //unset($_SESSION['myhp']);
  unset($_SESSION['knockDownCount']);
  unset($_SESSION['gameover']);
  unset($_SESSION['gameclear']);
  unset($_SESSION['success']);
  createErr();
  createHuman();
  $_SESSION['myhp'] = 500;
  $_SESSION['knockDownCount'] = 0;
}

//--------ゲームオーバー
function gameover(){
  $_SESSION = array();
  $_SESSION['gameover'] = true;
}
//--------ゲームクリア
function gameclear(){
  debug('ゲームクリアです');
  $_SESSION = array();
  $_SESSION['gameclear'] = true;
}

//-------------------------
//POSTされた場合の処理
//-------------------------

if(!empty($_POST)){

  $fix_flg = (!empty($_POST['fix'])) ? true : false;
  $give_up_flg = (!empty($_POST['give_up'])) ? true : false;

  //スタートボタンが押されていた場合
  if( !empty($_POST['start']) ){
    //初期化して、エラーを召喚する
    init();
  }elseif($fix_flg){
    //直すボタンが押されていた場合
    History::setN('「これでどうかな・・・」');
    //人のアタックポイント分、エラーのHPを減らす
    $_SESSION['human']->attack($_SESSION['error']);
    //エラーのアタックポイント分、人の気力を減らす
    History::setN('エラーがじっとこちらを見ている...');
    $_SESSION['error']->attack($_SESSION['human']);

    if( $_SESSION['human']->getHp() <= 0 ){
      gameover();
    }elseif( $_SESSION['error']->getHp() <= 0 ){
      History::set('エラーを倒した！');
      $_SESSION['knockDownCount'] ++;
      $_SESSION['knockDown'] = true;
      createErr();
    }

    if($_SESSION['knockDownCount'] === 3){
      $_SESSION['success'] = '「あと少しで完成しそう！！！」</br>';
    }elseif($_SESSION['knockDownCount'] === 4){
      $_SESSION['success'] = '「このエラーを倒せば完成！」</br>';
    }elseif($_SESSION['knockDownCount'] === 5){
      gameclear();
    }

  }else{
    //$_POST['give_up']が押された時
    //新たなエラーを発生させる
    History::setN('「だめだー！別の部分から手をつけよう」');
    createErr();
  }

}

?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>ERROR PC</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <section id="main">
      <div class="message-container">
        <h1 class="title">ERROR PC</h1>
        <!--start-->
        <?php if( empty($_SESSION) ): ?>
          <div class="img-box">
            <img src="img/mac.png" alt="">
          </div>
        <div class="btn-wrapper start">
          <form class="start" action="" method="post">
            <input type="submit" name="start" value="start">
          </form>
        </div>
      <?php elseif( (!empty($_SESSION['gameclear'])) or (!empty($_SESSION['gameover'])) ): ?>
        <!--------------------------->
        <!--game clear または　game over-->
        <!--------------------------->
        <div class="img-box">
          <img src="img/mac-black.png" alt="">
        </div>
        <div class="panel gameclear-panel">
          <?php if(!empty($_SESSION['gameover'])){
            echo 'GAME OVER';
          }elseif( (!empty($_SESSION['gameclear'])) ){
            echo 'GAME CLEAR!!!';
          }
          ?>
        </div>
        <section id="btn-wrapper">
          <form class="clear" action="" method="post">
            <input type="submit" name="start" value="再挑戦する">
          </form>
        </section>
      <?php else: ?>
        <!--------------------------->
        <!--game play-->
        <!--------------------------->
        <div class="img-box">
          <img src="img/mac-black.png" alt="">
        </div>
        <div class=" panel game-panel">
          <div class="err-message">
            <h2><?php if(!empty($_SESSION['error'])) echo $_SESSION['error']->getName(); ?>エラーがでた！</h2>
            <?php if(!empty($_SESSION['error'])) echo $_SESSION['error']->getErr_lv(); ?>
          </div>
          <div class="status-box">
            エラーを倒した数：<?php echo $_SESSION['knockDownCount']; ?></br>
            <?php echo $_SESSION['human']->getName(); ?>の気力：<?php echo $_SESSION['human']->getHp(); ?></br>
            エラーの手強さ：<?php echo $_SESSION['error']->getHp(); ?></br>
          </div>
          <div class="fix-message">
            <?php if(!empty($_SESSION['history'])) echo $_SESSION['history']; ?>
          </div>
        </div>
        <section id="btn-wrapper">
          <h3><?php if(!empty($_SESSION['success'])) echo $_SESSION['success']; ?></h3>
          <form class="play" action="" method="post">
            <input type="submit" name="fix" value="なおす"></br>
            <input type="submit" name="give_up" value="あきらめる"></br>
            <input type="submit" name="start" value="初めから書き直す">
          </form>
        </section>
      <?php endif; ?>
      </div>
    </section>
  </body>
</html>
