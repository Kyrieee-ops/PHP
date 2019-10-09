<?php
/*=========================================================
    登録確認画面
    ・postされてきたユーザー名
    ・メールアドレス
    ・パスワード入力
    を表示し問題なければ「入力内容確認ボタン」で登録
    =========================================================*/

    /*
    新規登録画面で入寮した内容を確認し、DBへ登録
    signup.phpからpostでデータを受け取り
    */
    session_start();
    var_dump($_SESSION['check']);
     //フォーム内容のエンティティ化
     require('htmlspecial.php');

    //ユーザー登録をinsertするため、insertclass読み込む
    //signup.phpで保持したsessionの中身がセットされていない場合は
    require('dbconnect.php');
    //signup.phpでフォーム送信した時にフォームの入力項目を「check」に格納
    //sessionしてcheckの値が格納されていなければ、signup.phpへ強制的に戻す
    //sessionしてcheckの値が入っていれば、各配列の中身を変数へ格納
        if (!isset($_SESSION['check'])){
            header('Location: signup.php');
            exit();
        } else {
            $name = $_SESSION['check']['name'];
            $email = $_SESSION['check']['email'];
            $password = $_SESSION['check']['password'];
            $image_name = $_SESSION['check']['image'];
        }   
        
        /*=========================================================
        ・ユーザー名
        ・メールアドレス
        ・パスワード
        ・画像
          の内容をINSERT(connect_clsで処理)
        =========================================================*/
        if (!empty($_POST)) {
        //「?」にはexecute()で指定した値（sessionに保存されている）を渡す為に記述する
        
            $sql = 'INSERT INTO user_data_hed SET USER_NAME=?, EMAIL=?, PASSWORD=?, PICTURE=?, INS_DATE=NOW()';
            $connect_cls = new connect();
            var_dump($connect_cls);
            /*$items =*/ 
            $connect_cls->insert($sql);
            //DBへの書き込みが完了したら、session削除
            unset($_SESSION['check']);
            header('Location: complete.php');
            exit();
        }
    

?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    
    <link rel="stylesheet" href="css/style8.css">

    <title>入力内容確認</title>
</head>
<body>
    <div class="container">
        <h1>入力内容確認</h1>
        <form action="" method="POST">
        <!--登録ボタンをフPOSTでフォーム送信するために
        hiddenを使用して、隠しボタンとしてHTMLの記述をする
        -->
        
            <dl>
                <dt>ユーザー名:</dt>
                <dd><?php echo h($name); ?></dd>

                <dt>メールアドレス:</dt>
                <dd><?php echo h($email); ?></dd>

                <dt>パスワード:</dt>
                <dd>表示されません</dd>

                <dt>プロフィール画像:</dt>
                <dd> <img src ="<?php echo 'up_file/'.$image_name; ?>"></dd>
            </dl>
        <div class="form-group">
            <!--登録完了へのsubmit-->
            <input class="btn btn-info" type="submit" name="record" value="登録">
            <input type="hidden" name="record" value="登録ボタン押した">
        </div>
        
    </div>
</body>
</html>