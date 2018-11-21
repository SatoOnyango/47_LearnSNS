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

$sql = 'SELECT `name`,`img_name`,`created` FROM `users`';


//今までは$data = [$email];
//sqlの中に？がないので変数で指定する必要がないいから$dataは使わない

$stmt = $dbh->prepare($sql);
$stmt->execute();

// 投稿情報全てを入れる配列定義
$users = [];
while(true){
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    //fetchは一つの行を取り出すこと
    if($record == false){
        break;
    }
    $users[] = $record;
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
                            <img src="user_profile_img/<?php echo $user['img_name']; ?>" width="80px">
                        </div>
                        <div class="col-xs-10">
                            名前 <a href="profile.php" style="color: #7f7f7f;"><?php echo $user['name']; ?></a>
                            <br>
                            <?php echo $user['created']; ?>からメンバー
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