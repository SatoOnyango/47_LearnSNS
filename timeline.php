<?php
session_start();
require('dbconnect.php');

// ログインしてない状態でのアクセス禁止
if(!isset($_SESSION['47_LearnSNS']['id']) ){
    header('Location: signin.php');
    exit();
}

$sql = 'SELECT * FROM `users` WHERE `id` = ?';
// $id = $_SESSION['47_LernSNS']['id'];
// $data = $id;
$data = [$_SESSION['47_LearnSNS']['id']];

$stmt = $dbh->prepare($sql);
$stmt->execute($data);

//ログインしているユーザーの情報
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

// echo '<pre>';
// var_dump($_SESSION);
// echo '</pre>';
// echo '<pre>';
// var_dump($signin_user);
// echo '</pre>';

// エラー内容を保持する配列定義
$errors =[];

// 投稿ボタンが押された時(POST送信された時)
if(!empty($_POST)){
    $feed = $_POST['feed'];
    // 投稿の空チェック
    if($feed != ''){
        // 投稿処理
        // 宿題
        // feedsテーブルに値を登録しよう
        // 登録する値は feed, user_id, createdの3つ
        $sql = 'INSERT INTO `feeds`(`feed`,`user_id`,`created`) VALUES(?,?,NOW())';
        $data = [$feed,$signin_user['id']];
        //セッションで取ってきたのではなく、すでに設けられている変数を使う方がベター。
        // $_SESSION['47_LearnSNS']['id']ではなく$signin_user['id']を使う！！
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);

        //なくても大丈夫だけど、今後、下にさらに処理等を書き加えて行ったときに、不具合が起きる可能性があるので、ここで一回タイムラインに戻す処理を行っておく。↓↓↓↓↓↓↓↓↓↓↓
        header('Location: timeline.php');
        exit();
        //headerを書くべき場所を間違えると、timeline.phpに遷移して、また遷移してと更新を繰り返してしまい、
        //常に更新が繰り返され、ページが表示できなくなる。
        //なので、headerを書く位置には注意しなければならない！！！

    }else{
        // バリデーション処理
        $errors['feed'] = 'blank';
    }
}

// 1.投稿情報(ユーザー情報を含む)を全て取得
$sql = 'SELECT `f`.*,`u`.`name`,`u`.`img_name` FROM `feeds`AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id` = `u`. `id` ORDER BY `created` DESC';
//今までは$data = [$email];
//sqlの中に？がないので変数で指定する必要がないいから$dataは使わない

$stmt = $dbh->prepare($sql);
$stmt->execute();

// 投稿情報全てを入れる配列定義
$feeds = [];
while(true){
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    //fetchは一つの行を取り出すこと
    if($record == false){
        break;
    }
    $feeds[] = $record;
}

// echo '<pre>';
// var_dump($feeds);
// echo '</pre>';

// 宿題 8/Nov/2018
// $feedsをもとにHTML内に
// 投稿内容、投稿日時、ユーザー名、ユーザー画像を表示
//
//


?>
<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <!-- 
        include(ファイル名);
        指定したファイルを組み込んで表示
        共通部分の切り出して使いたいページから読み込む
     -->
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
                    <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
                </ul>
            </div>
            <div class="col-xs-9">
                <div class="feed_form thumbnail">
                    <form method="POST" action="">
                        <div class="form-group">
                            <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
                        <?php if(isset($errors['feed']) && $errors['feed'] == 'blank'): ?>
                            <p class="text-danger">投稿を入力してください</p>
                        <?php endif; ?>
                        </div>
                        <input type="submit" value="投稿する" class="btn btn-primary">
                    </form>
                </div>
                <!-- ここにPHPを書いていく（ボタンタグの下だから） -->
                <?php foreach($feeds as $feed): ?>
                    <!-- <?php //echo '<pre>';
                          //echo var_dump($feed);
                          //echo '</pre>' ?> -->
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40px">
                        </div>
                        <div class="col-xs-11">
                            <a href="profile.php" style="color: #7f7f7f;">
                                <?php echo ($feed['name']);?></a>
                            2018-10-14
                        </div>
                    </div>
                    <div class="row feed_content">
                        <div class="col-xs-12">
                            <span style="font-size: 24px;"><?php echo ($feed['feed']); ?></span>
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <button class="btn btn-default">いいね！</button>
                            いいね数：
                            <span class="like-count">10</span>
                            <a href="#collapseComment" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：5</span>
                            <!-- ログインしているユーザーだけ編集できるようにしたい -->
                            <?php if($signin_user['id'] == $feed['user_id']): ?>
                                <!-- //JOIN LEFT 使った時のと似てるなぁ〜 -->
                                <a href="edit.php" class="btn btn-success btn-xs">編集</a>
                                <a onclick="return confirm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                            <?php endif;?>
                        </div>
                        <?php include('comment_view.php'); ?>
                    </div>
                </div>
                <? endforeach ?>
                <div aria-label="Page navigation">
                    <ul class="pager">
                        <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
