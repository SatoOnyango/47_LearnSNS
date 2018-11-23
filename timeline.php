<?php
session_start();
require('dbconnect.php');

// 1ページあたりのページ表示件数
const CONTENT_PER_PAGE = 5;
//下の$page部分へ


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

//ページの指定がある場合
//'page'のキーがあれば(GETで取って来れれば)、その値を取って来る
if(isset($_GET['page'])){
    $page = $_GET['page'];
//ページの指定がない場合
//なければ(ページの指定がない場合＝初期値)$pageにデフォルトで初期値を入れる
}else{
    $page = 1;
}

//constの次にここに来る
// -1などの不正な値を渡された時の対策
$page = max($page, 1);

// feedsテーブルのレコードを取得する
// COUNT() なんレコードあるか集計するSQL関数
$sql = 'SELECT COUNT(*) AS `cnt` FROM `feeds`';
//カラム名は連想配列のキーになる
// `feeds`テーブルに何個レコードがあるか、その数を数える
$stmt = $dbh->prepare($sql);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);
//投稿(feed)の数が入っている
$cnt = $result['cnt'];

// 最後のページ数を取得
// 最後のページ = 取得したページ数 ÷ 1ページあたりのページ数
$last_page = ceil($cnt / CONTENT_PER_PAGE);

// echo '<pre>';
// var_dump($last_page);
// echo '</pre>';

//最後のページより大きい値を渡された際の対策
//例、$page = 100 , $last_page = 3 のとき
//実際にあるページは少ない方の３なので、そっちのページを表示するようにする
$page = min($page, $last_page);

//スキップするレコード = (指定ページ -1 ) * 表示件数
$start = ($page -1) * CONTENT_PER_PAGE;
//前のページまでに表示されたものは不要

if (isset($_GET['search_word'])){
    // (echo '検索が行われた';)
    // 検索を行った場合の遷移
    $sql = 'SELECT `f`.*,`u`.`name`,`u`.`img_name`
            FROM `feeds` AS `f` LEFT JOIN `users` AS `u`
            ON `f`.`user_id` = `u`.`id`
            WHERE `f`.`feed` LIKE "%"?"%"
            ORDER BY `f`.`created` DESC LIMIT '. CONTENT_PER_PAGE . ' OFFSET '. $start;

    $data = [$_GET['search_word']];
    
    // $stmt = $dbh->prepare($sql);
    // $stmt->execute($data);

    // // 投稿情報全てを入れる配列定義
    // $feeds = [];
    // while(true){
    //     $record = $stmt->fetch(PDO::FETCH_ASSOC);
    //     //fetchは一つの行を取り出すこと
    //     if($record == false){
    //         break;
    //     }
    // $feeds[] = $record;
    // }

    // echo '<pre>';
    // var_dump($feeds);
    // echo '</pre>';

} else{
    // その他の遷移
// 1.投稿情報(ユーザー情報を含む)を全て取得
$sql = 'SELECT `f`.*,`u`.`name`,`u`.`img_name` FROM `feeds`AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id` = `u`. `id` ORDER BY `f`.`created` DESC LIMIT ' . CONTENT_PER_PAGE . ' OFFSET '. $start;

//文字列結合
//' OFFSET ? '  OFFSETの前後にスペース入れる（5(=CONTENT_PER_PAGE) OFFSET としたいから）
//LIMIT 数字 OFFSET 数字

//今までは$data = [$email];
//sqlの中に？がないので変数で指定する必要がないいから$dataは使わない

 //
    $data = [];
}


$stmt = $dbh->prepare($sql);
// $stmt->execute();
$stmt->execute($data);


// 投稿情報全てを入れる配列定義
$feeds = [];
while(true){
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    //fetchは一つの行を取り出すこと
    // $record は要するにfeed一件の情報
    if($record == false){
        // レコードが取れなくなったらループを抜ける
        break;
    }

    // 各投稿ごとのコメント一覧を取得
    $comment_sql = 'SELECT `c`.*,`u`.`name`,`u`.`img_name` FROM `comments` AS `c`
                    LEFT JOIN `users` AS `u`
                    ON `c`.`user_id` = `u`.`id`
                    WHERE `c`.`feed_id` = ?';

    $comment_data = [$record['id']];
    $comment_stmt = $dbh->prepare($comment_sql);
    $comment_stmt->execute($comment_data);

    $comments = [];
    while(true){
        $comment = $comment_stmt->fetch(PDO::FETCH_ASSOC);
        if($comment == false){
            break;
        }
        $comments[] = $comment;
    }

//投稿の連想配列に新しくcommentsというキーを追加
$record['comments'] = $comments;

// echo '<pre>';
// var_dump($record);
// echo '</pre>';

//各コメントに対するコメント数を取得
$comment_cnt_sql = 'SELECT COUNT(*) AS `comment_cnt`
                    FROM `comments` WHERE `feed_id` = ?';
$comment_cnt_data = [$record['id']];
$comment_cnt_stmt = $dbh->prepare($comment_cnt_sql);
$comment_cnt_stmt->execute($comment_cnt_data);

$comment_cnt_result = $comment_cnt_stmt->fetch(PDO::FETCH_ASSOC);

// 投稿の連想配列に新しく comment_cnt というキーを追加
$record['comment_cnt'] = $comment_cnt_result['comment_cnt'];


    $feeds[] = $record;
}

// echo '<pre>';
// var_dump($feeds);
// echo '</pre>';

// 宿題 8/Nov/2018
// $feedsをもとにHTML内に
// 投稿内容、投稿日時、ユーザー名、ユーザー画像を表示



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
                            <?php echo $feed['created']; ?>
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
                            <a href="#collapseComment<?php echo $feed['id']; ?>" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <!-- トグルオープンするのはbootstropの機能 -->
                            <span class="comment-count">コメント数：<?php echo $feed['comment_cnt']; ?></span>
                            <!-- ログインしているユーザーだけ編集できるようにしたい -->
                            <?php if($signin_user['id'] == $feed['user_id']): ?>
                                <!-- //JOIN LEFT 使った時のと似てるなぁ〜 -->
                                <a href="edit.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-success btn-xs">編集</a>
                                <a onclick="return confirm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                            <?php endif;?>
                        </div>
                        <?php include('comment_view.php'); ?>
                    </div>
                </div>
                <? endforeach; ?>
                <div aria-label="Page navigation">
                    <ul class="pager">
                        <!-- Newer押せないとき -->
                        <!-- 最初にページより前は禁止 -->
                        <?php if ($page == 1): ?>
                            <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <?php else: ?>
                        <!-- Newer押せるとき -->
                            <li class="previous"><a href="timeline.php?page=<?php echo $page -1; ?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <?php endif; ?>

                        <?php if ($page == $last_page): ?>
                            <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                        <?php else: ?>
                            <li class="next"><a href="timeline.php?page=<?php echo $page +1; ?>">Older <span aria-hidden="true">&rarr;</span></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
