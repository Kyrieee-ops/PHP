<?php
/*============================================================================
ログイン画面
・postしたメールアドレス
・パスワード
をselectして登録があれば、ログイン、なければエラー表示
・そもそもフォームが空白であればエラー
・※修正必要
⇒画面を縮めると、かんたんログインボタンの表示が崩れるので訂正
============================================================================*/
    session_start();
    require('dbconnect.php');
    require('htmlspecial.php');
    //ログインボタンの情報格納
    $login = $_POST['login'];
    //ユーザー入力のメールアドレス情報を格納
    $email = $_POST['email'];
    //ユーザー入力のパスワード情報を格納
    $password = $_POST['password'];

    //cookieの情報が空でない場合(要するにset_cookieされている場合)クッキーのemail情報を変数に格納
    if ($_COOKIE['ckie_email'] !== '') {
        $cookie_email = $_COOKIE['ckie_email'];
        var_dump ($cookie_email);
    }
    //ログインボタンを押した場合のチェック処理
    //フォームが空白であれば、エラー
    //DB接続してselectした値がなければエラー
    //DB登録された値であれば、ログイン
    if (!empty($login)){
        //ログインが押された場合にはユーザー入力の値を変数に格納
        $cookie_email = $_POST['email'];
        if ($email === '' && $password === ''){
            $error['email'] = 'blank';
            $error['password'] = 'blank'; 
        } elseif ($email === ''){
            $error['email'] = 'blank'; 
        } elseif ($password === ''){
            $error['password'] = 'blank'; 
        //メールアドレスとパスワードが入力されている場合は
        //DB接続してselectでメールアドレス、パスワード引っ張る
        } else {
            $sql = 'SELECT * FROM user_data_hed WHERE EMAIL = ?';
            $connect_cls = new connect();
            $login_flg = $connect_cls->select_login($sql);
            //入力した内容とDB接続して取得した値が一致していればログイン成功
            
            //DB接続して、すでにハッシュ化されたパスワードと手入力されたパスワードの値が一致した場合、
            //trueを返し、login_flgがtrueであれば、ログイン成功で、ツイート画面へ遷移
            if ($login_flg) {
                //checkboxにチェックを付けている場合にpostされたname属性を変数に格納
                $checkbox_flg = $_POST['save_flg'];
                //checkbox_flgのvalue属性がon(チェックされていれば)
                if ($checkbox_flg === 'on') {
                    //cookie保存期間を7日でセット
                    $timeout = time()+60*60*24*7;
                    //クッキーの情報をセット(名前、ユーザー入力データ、有効期限)
                    //postされた$emailの値を'ckie_email'へ格納
                    setcookie("ckie_email",$email,$timeout);
                    
                }
                header ('Location: twieet.php');
                exit();
                
            //falseであれば、エラーメッセージ
            } else {
                $error['email'] = 'different';
                $error['password'] = 'different';
            }
        }
    }

?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="css/style8.css">

    <title>ログイン画面</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
        </header>
        <form action="" method="POST">
            <!-------------メールアドレスインプット入力欄--------------->
            <div class="form-group">
                <label for="InputId">メールアドレス</label>
                <?php if ($error['email'] === 'blank'): ?>
                    <p class="error">*メールアドレスが入力されていません。</p>
                <?php elseif ($error['email'] === 'different' or $error['password'] === 'different') : ?>
                    <p class="error">*メールアドレスかパスワードが誤っています。</p>
                <?php endif; ?>
                <input type="text" name="email" pattern=".+@.+\..+" title="メールアドレスは、aaa@example.com のような形式で記入してください。" id="InputId" placeholder="info@example.com" class="form-control"　value="<?php echo h($cookie_email); ?>">
            </div>
            <!-------------/メールアドレスインプット入力欄--------------->

            <!-------------パスワードインプット入力欄--------------->
            <div class="form-group">
                <label for="password">パスワード</label>
                <?php if ($error['password'] === 'blank'): ?>
                    <p class="error">*パスワードが入力されていません。</p>
                <?php endif; ?>
                    <input type="password" name="password" id="password"　minlength="8" placeholder="8文字以上で入力" class="form-control">
            </div>
            <!-------------/パスワードインプット入力欄--------------->

            <!-------------cookie情報保存チェックボックス欄--------------->
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="save_flg" value="on">情報を保存する
                </label>
            </div>
            <!-------------/cookie情報保存チェックボックス欄--------------->
        
            <div class="form-group">
                <!--新規登録の場合は新規登録画面のリンクへ飛ぶ-->
                <a class="btn btn-info" href="signup.php" role="button">Sing Up Link</a>
                <!--すでに登録済みの場合はアドレス、パスワードを入力しログイン-->
                <input class="btn btn-info" type="submit" name="login" value="Login"> 
                <!--閲覧用にかんたんログイン機能を搭載チェックはなし-->
                <input class="btn btn-danger" type="submit" name="kantanlogin" value="Easy Login">
            </div>          
        </form> 

        </div>

        <footer>
            <hr>
        </footer>
    </div>
</body>
</html>