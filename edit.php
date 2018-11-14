<?php
session_start();
require('dbconnect.php');

// echo '<pre>';
// var_dump($_SESSION);
// echo '</pre>';


$sql = 'SELECT * FROM `users` WHERE `id` = ?';
$data = [$_SESSION['47_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

//POST->登録などのフォーム (<form>タグ)
    //$_POST['キー']
//GET ->データを取得するだけ(単純な取得だけはゲット)（<a>タグ）
    //$_GET['キー']

// 1. GETパラメーターを定義
$feed_id = $_GET['feed_id'];
// 2. SQL文定義
//① $sql = 'SELECT *, `users` FROM `feeds` WHERE `id`= ?';
//② $sql = 'SELECT *, `users` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id` = `users`.`id` WHERE `id`= ?';
//③ $sql = 'SELECT *, `users` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id` = `users`.`id` WHERE `feeds`.`id`= ?';
//④$sql = 'SELECT `feeds`.*, `users` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id` = `users`.`id` WHERE `feeds`.`id`= ?';
//⑤$sql = 'SELECT `feeds`.*, `users` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `feeds`.`user_id` = `users`.`id` WHERE `feeds`.`id`= ?';
$sql = 'SELECT `f`.*, `u`.`name`,`u`.`img_name` 
FROM `feeds` AS `f` LEFT JOIN `users` AS `u` 
ON `f`.`user_id` = `u`.`id` WHERE `f`.`id`= ?';

//①SELECT* ⑦<-（全部必要ないので）最後に必要なカラムを指定していく
//①FROM ③テーブ ④AS ⑤カラム
//②LEFT JOIN
//②ON ③テーブル ④AS ⑤カラム
//①WHERE ⑥紐づける

//SQL文を書くときの基本的な考え方
// 1.SQL文
// 2.テーブル
// 3.カラム


$data = [$feed_id];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// 3. 投稿情報を一件取得
$feed = $stmt->fetch(PDO::FETCH_ASSOC);

// echo '<pre>';
// var_dump($feed);
// echo '</pre>';

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Learn SNS</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px;">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-4 col-xs-offset-4">
                <form class="form-group" method="post" action="timeline.php">
                    <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="60">
                    <?php echo $feed['name']; ?><br>
                    <?php echo $feed['created']; ?><br>
                    <textarea name="feed" class="form-control"><?php echo $feed['feed']; ?></textarea>
                    <input type="submit" value="更新" class="btn btn-warning btn-xs">
                </form>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>