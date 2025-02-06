<!--database2024 最終課題 1972107 宮林 凜太朗-->

<?php
$hostname = "127.0.0.1";
$username = "root";
$password = "dbpass";
$dbname = "okodukaicho";
$tablename = "account";
$pagename = "DB_2024.php";

session_start();

mysqli_report(MYSQLI_REPORT_OFF);

if (!array_key_exists('transition', $_POST))
{
    echo_page_main();
}
elseif ($_POST['transition'] == "trans_add_account")
{
    //アカウント作成画面へ
    echo_page_addaccount();
}
elseif ($_POST['transition'] == "trans_complete1")
{
    action_trans_complete1();
    //アカウント作成完了画面
}
elseif ($_POST['transition'] == "trans_login")
{
    //ログイン画面へ
    echo_page_login("ログイン");
}
elseif ($_POST['transition'] == "trans_mypage")
{
    //マイページへ
    //セッションを使用しユーザー情報の保存を行なっています
    //認証に失敗した場合、再度ログインページに飛ぶ仕様にしてます
    if (!array_key_exists('user', $_POST))
    {
        $user = $_SESSION['user'];
        $pass = $_SESSION['pass'];
    }
    else{
        $user = $_POST['user'];
        $pass = $_POST['pass'];
    }

    $link = mysqli_connect($hostname,$username,$password,$dbname);
    if (! $link){ exit("Connect error!"); }

    $result = mysqli_query($link,"SELECT * FROM $tablename WHERE name = '$user' AND pass = '$pass'");
    if (!$result){ exit("Unexpected query error!"); }else
    {
        $row = mysqli_fetch_row($result);
        if(!$row){
            mysqli_close($link);
            echo_page_login("パスワードが違います");
            exit;
        }else {
            mysqli_close($link);
            echo_page_mypage($user);
        }
    }

}
elseif ($_POST['transition'] == "trans_input_new")
{
    //新規入力画面へ
    echo_page_input();
}
elseif ($_POST['transition'] == "trans_confirm")
{
    //削除確認画面へ

    echo_page_confirm();
}
elseif ($_POST['transition'] == "trans_complete2")
{

    echo_page_complete2();
    //アカウント削除の完了画面
}
elseif ($_POST['transition'] == "trans_return_top")
{
    //メイン画面に戻る処理
    echo_page_main();
}
else
{
    echo "Internal Error!"; // あり得ないエラー
}
////////////////////////////////////////////////////////////////
function echo_page_main()
{
    echo <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>トップページ</title>
    </head>
    <body>
      <h1>お小遣い帳システム</h1>
        <p>アカウントを作成して、日付・金額・用途(コメント)を記録できます。</p>
        <br>
        <form method="post" action="$pagename">
            アカウントの作成はこちら<button type="submit" name="addaccount" value="Btn1">アカウントの追加</button>
            <input type="hidden" name="transition" value="trans_add_account">
        </form>
        <br>
        <form method="post" action="$pagename">
            ログインはこちら<button type="submit" name="login" value="Btn2">ログイン</button>
            <input type="hidden" name="transition" value="trans_login">
        </form>
    </body>
</html>
EOT;
}
function echo_page_addaccount()
{
echo <<<EOT
  <!DOCTYPE html>
  <html>
    <head>
      <meta  charset="utf-8" />
      <title>アカウント作成画面</title>
    </head>
    <body>
      <h1>アカウント作成</h1>
      <form method="post" action="$pagename">
        name(10文字以下の英数字)<input type="text" maxlength=10 name="Txt1" value="">
        <br>
        password<input type="text" name="Txt2" value="">
        <br>
        <button type="submit" name="makeaccount" value="Btn3">登録</button>
        <input type="hidden" name="transition" value="trans_complete1">
      </form>
      <form method="post" action="$pagename">
        <button type="submit" name="cancel" value="Btn4">メイン画面に戻る</button>
        <input type="hidden" name="transition" value="trans_return_top">
      </form>
    </body>
  </html>
EOT;
}
function action_trans_complete1()
{
    global $hostname, $username, $password, $dbname, $tablename;

echo <<<EOT
  <!DOCTYPE html>
  <html>
    <head>
      <meta  charset="utf-8" />
      <title>完了画面</title>
    </head>
    <body>
      <form method="post" action="$pagename">
        <button type="submit" name="return" value="Btn5">メイン画面に戻る</button>
        <input type="hidden" name="transition" value="trans_return_top">
      </form>
EOT;
  if (array_key_exists('Txt1', $_POST))
  {
     $name = $_POST['Txt1'];
  }
  else
  {
     echo "名前が未入力です。";
  }

  if (array_key_exists('Txt2', $_POST))
  {
     $pass = $_POST['Txt2'];
  }
  else
  {
     echo "パスワードが未入力です。";
  }

  mysqli_report(MYSQLI_REPORT_OFF);

  $link = mysqli_connect('127.0.0.1','root','dbpass');
  if (! $link){ exit("Connect error!"); }

  $result = mysqli_query($link,"USE $dbname");
  if (!$result) { exit("USE failed!"); }

  $result = mysqli_query($link,"INSERT INTO $tablename SET name = '$name', pass = '$pass'");
  if (! $result){
    exit("アカウント作成に失敗しました");
  }
  else{
    $result = mysqli_query($link,"SELECT * FROM $tablename WHERE name = '$name'");
    if (! $result){ exit("アカウント作成に失敗しました"); }else{
      $row = mysqli_fetch_row($result);
      $id = $row[0];
    }
    $result = mysqli_query($link,"CREATE TABLE record$id(id INT NOT NULL AUTO_INCREMENT,
    `date` DATE,price INT,comment TEXT,PRIMARY KEY(id))CHARACTER SET utf8");
    if (! $result){ exit("アカウント作成に失敗しました3"); }else{ echo "アカウントが作成されました!"; }
  }

  mysqli_close($link);

echo <<<EOT
 "</pre>";
    </body>
  </html>
EOT;
}
function echo_page_login($msg)
{
echo <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>ログインページ</title>
    </head>
    <body>
$msg
    <form method="POST" action="$pagename">
        username <input type="text" maxlength=10 name="user" value=""><br>
        password <input type="text" name="pass" value=""><br>
        <button type="submit" name="login" value="login">Login</button>
        <input type="hidden" name="transition" value="trans_mypage">
    </form>
    <form method="post" action="$pagename">
      <button type="submit" name="return" value="return">メイン画面に戻る</button>
      <input type="hidden" name="transition" value="trans_return_top">
    </form>
    </body>
</html>
EOT;
}
function echo_page_mypage($who)
{
    global $hostname, $username, $password, $dbname, $tablename, $user, $pass, $id;

    $_SESSION['user'] = $user;
    $_SESSION['pass'] = $pass;

echo <<<EOT
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>マイページ</title>
  </head>
  <body>
こんにちは $who さん
    <form method="post" action="$pagename">
      <button type="submit" name="return" value="return">メイン画面に戻る</button>
      <input type="hidden" name="transition" value="trans_return_top">
    </form>
    <form method="post" action="$pagename">
      <button type="submit" name="return" value="return">新規入力</button>
      <input type="hidden" name="transition" value="trans_input_new">
    </form>
    <form method="post" action="$pagename">
      <button type="submit" name="delete" value="delete">アカウントの削除</button>
      <input type="hidden" name="transition" value="trans_confirm">
    </form>
  </body>
</html>
EOT;

$link = mysqli_connect($hostname,$username,$password,$dbname);
if (! $link){ exit("Connect error!"); }

$result = mysqli_query($link,"SELECT * FROM $tablename WHERE name = '$user'");
if (! $result){ exit("認証エラー"); }else{
  $row = mysqli_fetch_row($result);
  $_SESSION['id'] = $row[0];
  $id = $_SESSION['id'];
}

if (array_key_exists('date', $_POST))
{
   $date = $_POST['date'];
}
else
{
   $date = '';
}
if (array_key_exists('price', $_POST))
{
   $price = $_POST['price'];
}
else
{
   $price = '';
}
if (array_key_exists('comment', $_POST))
{
   $comment = $_POST['comment'];
}
else
{
   $comment = '';
}

$result = mysqli_query($link,"INSERT INTO record$id SET `date` = '$date', price = '$price', comment = '$comment'");

$result = mysqli_query($link,"SELECT * FROM record$id");
if (!$result){ exit("Unexpected query error!"); }

echo '<table border="1">';

$ary_of_fieldinfo = mysqli_fetch_fields($result);

foreach ($ary_of_fieldinfo as $key => $value)
{
    echo "<th>" . htmlspecialchars($value->name) . "</th>";
}

while ($row = mysqli_fetch_row($result))
{
    echo "<tr>";
    foreach ($row as $key => $value)
    {
        echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
mysqli_free_result($result);

mysqli_close($link);

}
function echo_page_input()
{
echo <<<EOT
  <!DOCTYPE html>
  <html>
    <head>
      <meta  charset="utf-8" />
      <title>記録入力画面</title>
    </head>
    <body>
      <h1>必要事項を入力してください</h1>
      <form method="post" action="$pagename">
        日付(YYYY-MM-DD)<input type="date" name="date" value="">
        <br>
        金額<input type="text" name="price" value="">
        <br>
        用途(コメント)<input type="text" name="comment" value="">
        <br>
        <button type="submit" name="newrecord" value="newrecord">記録する</button>
        <input type="hidden" name="transition" value="trans_mypage">
      </form>
      <form method="post" action="$pagename">
        <button type="submit" name="cancel" value="cancel">キャンセル</button>
        <input type="hidden" name="transition" value="trans_mypage">
      </form>
    </body>
  </html>
EOT;
}
function echo_page_confirm()
{
    global $user, $pass;

    $user = $_SESSION['user'];
    $pass = $_SESSION['pass'];

echo <<<EOT
<!DOCTYPE html>
  <html>
      <head>
        <meta charset="utf-8" />
        <title>確認画面</title>
      </head>
      <body>
アカウントを削除しますか？<br>
復旧はできません。<br>
        <form method="post" action="$pagename">
          <button type="submit" name="delete2" value="delete2">アカウントを削除する</button>
          <input type="hidden" name="transition" value="trans_complete2">
        </form>
        <form method="post" action="$pagename">
          <button type="submit" name="cancel2" value="cancel2">キャンセル</button>
          <input type="hidden" name="transition" value="trans_mypage">
        </form>
      </body>
    </html>
EOT;
}
function echo_page_complete2()
{
    global $hostname, $username, $password, $dbname, $tablename, $user, $pass, $id;

echo <<<EOT
<!DOCTYPE html>
  <html>
    <head>
      <meta charset="utf-8" />
      <title>完了画面</title>
    </head>
    <body>
アカウントが削除されました。
      <form method="post" action="$pagename">
        <button type="submit" name="return2" value="retur2">メイン画面に戻る</button>
        <input type="hidden" name="transition" value="trans_return_top">
      </form>
    </body>
  </html>
EOT;

  $user = $_SESSION['user'];
  $id = $_SESSION['id'];

  $link = mysqli_connect($hostname,$username,$password,$dbname);
  if (! $link){ exit("Connect error!"); }

  $result = mysqli_query($link,"DROP TABEL record$id");
  if (! $result){ exit("記録のエラー"); }

  $result = mysqli_query($link,"DELETE FROM $teblename WHERE id = $id");
  if (! $result){ exit("アカウントのエラー"); }

  mysqli_close($link);
}
?>
