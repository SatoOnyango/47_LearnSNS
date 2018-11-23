<div class="collapse" id="collapseComment<?php echo $feed['id']; ?>">
                            <!-- phpを使い echo でid出す。 ＊include でtimeline.php の変数が使える -->
    <div class="col-xs-12" style="margin-top:10px">
        <?php foreach ($feed['comments'] as $comment): ?>
        <!-- $ feed はタイムラインphpで使われている奴が使える -->
        <p style="margin-top: 30px; margin-bottom: 30px">
           <!--  <?php //echo '<pre>';
                  //var_dump($record);
                  //echo '</pre>';
                   ?> -->
            <img src="user_profile_img/<?php echo $comment['img_name']; ?>" width="40" class="img-circle">
            <span style="border-radius: 100px!important; -webkit-appearance:none;background-color:#eff1f3;padding:10px;margin-top:10px;">
                <a href="profile.php"><?php echo $comment['name']; ?></a>
                <?php echo $comment['comment'] ?>
            </span>
        </p>
        <?php endforeach; ?>
        <form method="post" class="form-inline" action="comment.php" role="comment">
            <div class="form-group">
                <!-- サインインしているユーザーの画像を表示したいので $signin_user['img_name']; -->
                <img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="40" class="img-circle">
            </div>
            <div class="form-group">
                <input type="text" name="write_comment" class="form-control" style="width:400px;border-radius: 100px!important; -webkit-appearance:none;" placeholder="コメントを書く">
            </div>
            <input type="hidden" name="feed_id" value="<?php echo $feed['id']; ?>">
            <!-- どの投稿に対してかをhiddenで送信している -->
            <!-- hidden ユーザーの入力が必要ないが、サーバー側では必要な値を送信する時に使用 -->
            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-primary">投稿する</button>
            </div>
        </form>
    </div>
</div>
