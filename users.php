<?php
session_start();
require('dbconnect.php');


$sql = 'SELECT * FROM `users` WHERE `id` = ?';
// $id = $_SESSION['47_LernSNS']['id'];
// $data = $id;
$data = [$_SESSION['47_LearnSNS']['id']];

$stmt = $dbh->prepare($sql);
$stmt->execute($data);

//ログインしているユーザーの情報
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);


if(!empty($_GET)){
    $user = $_GET['user'];
    // 投稿の空チェック
    if($users != ''){
        $sql = 'SELECT * FROM `users` WHERE `name`';
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    }
}



?>
<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <?php include('navbar.php'); ?>
    <?php foreach($users as $user): ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-2">
                            <img src="user_profile_img/misae.png" width="80px">
                        </div>
                        <div class="col-xs-10">
                            名前 <a href="profile.php" style="color: #7f7f7f;">野原みさえ</a>
                            <br>
                            2018-10-14 12:34:56からメンバー
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <span class="comment_count">つぶやき数：10</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</body>
<?php include('layouts/footer.php'); ?>
</html>
