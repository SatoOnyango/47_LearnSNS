<?php
session_start();
//require(読み込みたいファイル名)
require('../dbconnect.php');

//正規ルートを通らずに遷移した場合、sign.phpへ強制遷移
//
if(!isset($_SESSION['47_LearnSNS'])){
    header('Location: signup.php');
    exit();
}


// echo '<pre>';
// var_dump($_SESSION);
// echo '</pre>';

// echo $_SESSION['47_LearnSNS']['name'].'<br>';
// echo $_SESSION['47_LearnSNS']['email'].'<br>';
// echo $_SESSION['47_LearnSNS']['password'].'<br>';
// echo $_SESSION['47_LearnSNS']['img_name'].'<br>';

$name = $_SESSION['47_LearnSNS']['name'];
$email = $_SESSION['47_LearnSNS']['email'];
$password = $_SESSION['47_LearnSNS']['password'];
$img_name = $_SESSION['47_LearnSNS']['img_name'];


// POST送信の時（登録ボタンが押された時のみ処理するif文）
if(!empty($_POST)){
    // echo 'POST送信されました';
    //1. SQL文を用意
    $sql = 'INSERT INTO `users`(`name`,`email`,`password`,`img_name`,`created`) VALUES(?,?,?,?,NOW());';
    //2. ?に代入したい値を設定
    //パスワードはハッシュ化する
    // password_hash(パスワード,PASSWORD_DEFAULT)

    $data = [$name,$email, password_hash($password,PASSWORD_DEFAULT),$img_name];

    //3.SQL文をセットする
    $stmt = $dbh->prepare($sql);

    //4.SQL文を実行する
    $stmt->execute($data);

    // 登録完了ページへ遷移する
    // header('Location: 遷移先')
    // セッションに保持した内容は不要になったら破棄すること
    unset($_SESSION['47_LearnSNS']);
    header('Location: thanks.php');
    exit();

}


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Learn SNS</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
</head>
<body style="margin-top: 60px">
    <div class="container">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2 thumbnail">
                <h2 class="text-center content_header">アカウント情報確認</h2>
                <div class="row">
                    <div class="col-xs-4">
                        <img src="../user_profile_img/<?php echo htmlspecialchars($img_name); ?>" class="img-responsive img-thumbnail">
                    </div>
                    <div class="col-xs-8">
                        <div>
                            <span>ユーザー名</span>
                            <p class="lead"><?php echo htmlspecialchars($name); ?></p>
                        </div>
                        <div>
                            <span>メールアドレス</span>
                            <p class="lead"><?php echo htmlspecialchars($email) ?></p>
                        </div>
                        <div>
                            <span>パスワード</span>
                            <p class="lead">お客様のパスワード</p>
                        </div>
                        <form method="POST" action="check.php">
                            <!-- 
                                    GET送信（時）のパラメーター
                                    URL?キー = 値
                             -->
                            <a href="signup.php?action=rewrite" class="btn btn-default">&laquo;&nbsp;戻る</a> | 
                            <!-- 
                                DBに登録したい値は$_SESSIONが保持しているので、
                                formから値を送信する必要はないが、
                                !empty($_POST)を処理するためにinput type="hidden"を使って、$_POSTを空じゃない状態にしている。
                             -->
                            <input type="hidden" name="action" value="submit">
                            <input type="submit" class="btn btn-primary" value="ユーザー登録">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/jquery-3.1.1.js"></script>
    <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="../assets/js/bootstrap.js"></script>
</body>
</html>