<?php
    /*=========================================================
    新規登録画面
    ・ユーザー入力フォーム
    ・メールアドレスフォーム
    ・パスワード入力フォーム
    ・新規登録ボタン
    新規登録ボタンを押すと上記内容をsessionに格納して、confirm.phpへ
    送信
    ※パスワードの半角英数8文字制限の制御以下URL参考に
    https://gist.githubusercontent.com/RustyNail/6a61227c6862e56147df/raw/ab02e94e70c3fe49b9c9129c31af5fede1bdaead/input_attribute
    
    =========================================================*/

    //入力された内容「name,email,password」のエンティティ化処理へ
    require('htmlspecial.php');
    //メールアドレスの重複チェックのため、DB接続
    require('dbconnect.php');
    var_dump($_POST['name']);
    var_dump($_POST['email']);
    var_dump($_POST['password']);
    
    //フォームが送信された値をセッションで保持するため
    //まずはsessionをスタート
    session_start();
    

    //フォームが送信されているかどうかの判定をする為に、!empty($_POST)で
    //確認する
    //この場合「新規登録」ボタンが押された時点で、フォームが送信されたと
    //判定されるので、!empty($_POST)を直訳すると、フォーム送信が空ではない
    //⇒フォームが送信された場合と解釈する
    if (!empty($_POST)) {
        //入力フォームが送信（この場合新規登録画面が押された場合）
        if ($_POST['name'] === ''){
        //エラー変数にブランクをerror配列に代入
            $error['name'] = 'blank';
        }
        //メールアドレスが空欄であれば、ブランク処理へ
        //空欄ではない場合は、重複チェックを行う
        if ($_POST['email'] === ''){
            $error['email'] = 'blank';
        }
        
        //8文字未満であれば、「8文字以上で入力の旨エラー表示」
        //空白であれば、「入力してくださいのエラー表示」
        //※20190811現状、8文字での制限しかかけていない為、半角英数８文字の制御にする
        if (strlen($_POST['password']) < 8){
            $error['password'] = 'length';
        } elseif($_POST['password'] === '') {
            $error['password'] = 'blank';
        }

        /*===========================================================
        　■画像アップロードの記述
          画像ファイルの日本語を省く為正規表現の値を格納
          アップロード画像の名前が英数字とハイフンとアンダーバーかどうか
        　.以下の拡張子がアルファベット大文字小文字が3文字がどうかチェック
        　jpg/gif/pngが指定される為
          ※switch文の拡張子が正常の場合の出力について、記述考える
          ⇒echoしても仕方がないので、どの記述が良いか
        ===========================================================*/
        $pattern = "/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3}$/"; 
        //アップロードされたファイル名を取得
        $filename = $_FILES['image']['name'];
        /*
        画像の名前が「英数-_ではない場合」$error配列にjpn_ngを格納
        直訳すると日本語が含まれていた場合、エラー表示
        */
        if ($filename === ''){
            $error['file'] = 'blank';
        } elseif (!preg_match($pattern,$filename)){
            $error['jpnla'] = 'jpn_ng';
        }

        if (!empty($filename)){
            $image_tmp_path = $_FILES['image']['tmp_name'];
                //ファイルの存在を確認、ない場合はエラー表示処理へ
                if (file_exists($image_tmp_path)){
                //getimagesize関数で画像情報を取得する
                    list($img_width, $img_height, $mime_type, $attr) = getimagesize($image_tmp_path);
                }
                //list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
                switch($mime_type){
                    //jpegの場合
                    case IMAGETYPE_JPEG:
                        //echo  'image_type:jpg';
                        var_dump($mime_type);
                        break;
                    //pngの場合
                    case IMAGETYPE_PNG:
                        //echo 'image_type:png';
                        var_dump($mime_type);
                        break;
                    //gifの場合
                    case IMAGETYPE_GIF:
                        //echo 'image_type:gif';
                        var_dump($mime_type);
                        break;
                    //拡張子が異なる旨のエラー表示をさせる
                    default:
                        $error['image'] = 'type';
                }
        }
        if (isset($_POST)) {
            $_SESSION['check'] = $_POST;
            $sql = 'SELECT COUNT(*) cnt FROM user_data_hed WHERE EMAIL=?';
            $connect_cls = new connect();
            $result_error = $connect_cls->select_duplicate($sql);
            //clsで取得したsqlの結果をエラー配列に代入
            //ただしNULLの場合empty処理で空ではないと判断されるので
            //$error['email'] = $result_error;
            
        }
        //$errorが空、要するにエラーがなかった場合にはconfirm.phpへ
        //$errorが空でなかった場合にはエラーメッセージを表示（上記で記述）
        if (empty($error) && empty($result_error)) {
            //エラーが起こっていないことを確認できた直後にsessionに値を保持
            //sessionでフォームで送られた値を保持するため、
            //sessionに値を格納
            //ここで$_POSTには新規登録の値が入っている
            //ファイルのアップロードについては同時に同じファイル名をアップロードした場合に、ファイル名の重複を防ぐ為、登録時点の時分秒をプラスして変数に保存する
            $image_name = date('YmdHis').$filename;
            $upload_path = 'up_file/';
            //一時アップロードしたファイルをC:\xampp\htdocs\HTML_CSS\up_fileへ$image_nameをプラスして移動
            move_uploaded_file($image_tmp_path, $upload_path . $image_name);
            $_SESSION['check'] = $_POST;
            $_SESSION['check']['image'] = $image_name;
            header('Location: confirm.php');
            exit();
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

    <title>新規登録</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Sign Up</h1>
        </header>
        <form action="" method="POST" enctype="multipart/form-data">
            <!--ユーザー名入力フォーム-->
            <!--入力がない場合はエラー表示-->
            <div class="form-group">
                <label for="Name">ユーザー名</label>
                    <!--ユーザー名が入力されているかどうか確認-->
                    <?php if ($error['name'] === 'blank'): ?>
                        <p class="error">*ユーザー名を入力してください</p>
                    <?php endif; ?>
                <!--ユーザー名inputフォーム-->
                <!--valueにhtmlspecialcharsで文字エンティティ化して、入力された値を保持-->
                <input type="text" name="name" id="Name" class="form-control" placeholder="example: taro yamada" value="<?php echo h($_POST['name']); ?>">
            </div>

            <!--メールアドレス入力フォーム-->
            <!--入力がない場合はエラー表示-->
            <div class="form-group">
                <!--メールアドレスinputフォーム-->
                <label for="email">メールアドレス</label>
                <!--メールアドレスが入力されているかどうか確認-->
                    <?php if ($error['email'] === 'blank'): ?>
                        <p class="error">*メールアドレスを入力してください</p>
                    <?php elseif ($result_error === 'duplicate'): ?>
                        <p class="error">*そのメールアドレスは既に使用されています</p>
                    <?php endif; ?>
                <input type="text" name="email" pattern=".+@.+\..+" title="メールアドレスは、aaa@example.com のような形式で記入してください。" id="email" class="form-control" placeholder="info@example.com" value="<?php echo h($_POST['email']); ?>">
            </div>

            <!--パスワード入力のフォーム-->
            <!--HTML内にphp if文埋め込み-->
            <!--文字列:8文字未満である場合は8文字以上入力エラーメッセージ-->
            <!--文字列:空白である場合は入力してくださいのエラーメッセージ-->
            <div class="form-group">
                <!--パスワードinputフォーム-->
                <label for="password">パスワード</label>
                    <!--「===」⇒値も型も等しい場合-->
                    <?php if ($error['password'] === 'length'): ?>
                        <p class="error">*パスワードは8文字以上で入力してください</p>
                    <?php elseif ($error['password'] === 'blank'): ?>
                        <p class="error">*パスワードを入力してください</p>
                    <?php endif; ?>
                <input type="password" name="password" id="password" class="form-control" placeholder="パスワードは8文字以上で入力してください" minlength="8" value='<?php echo h($_POST['password']); ?>'>
            </div>
            
            <!--画像ファイルアップロードフォーム-->
            <!--入力がない場合はエラー表示-->
            <!--画像ファイルでない場合（jpg,gif,png）エラー表示-->
            <div class="form-group">
                <label for="image">プロフィール画像</label><br>
                    <?php if($error['file'] === 'blank') : ?>
                        <p class="error">*画像ファイルを添付してください</p>
                    <?php elseif($error['jpnla'] === 'jpn_ng') : ?>
                        <p class="error">*画像ファイル名は日本語にはできません。</p>
                    <?php elseif($error['image'] === 'type') : ?>
                        <p class="error">*画像は「jpg」「gif」「png」を指定してください</p>
                    <?php endif ;?>
                <input type="file" name="image" value="<?php echo h($_FILES['image']['name']); ?>">
            </div>
            <!--新規登録submitフォーム-->   
            <div class="form-group">
                <input class="btn btn-info" type="submit" value="Sign Up">
                <!--新規登録の内容をhidden属性でpostする
                正直必要ない項目かもしれないが、明示的にinput name属性を
                postに入れている-->
                <a href="login.php">＜＜ログイン画面に戻る</a>
                <input type="hidden" name="new_record" value="新規登録を押した">
            </div>          
        </form> 

        </div>

        <footer>
            <hr>
        </footer>
    </div>
</body>
</html>