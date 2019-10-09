<?php
/*============================================================================
DB接続　class
localhostの場合「127.0.0.1」
passwordは空白

ここの処理でCRUDの処理を行う
・ユーザー新規登録⇒insert　function

============================================================================*/
class connect {
  private static $dsn = "mysql:dbname=bbs; host=127.0.0.1; charset=utf8";
  private static $username = "root";
  private static $password = "";

    public function db() {
        try {
            $db = new PDO(self::$dsn, self::$username, self::$password);
            //20190819追加
            $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
            return $db;

        } catch(PODExeption $e){
            echo ("DB接続エラー:" . $e->getMessage)();
            
            }
        }
    //INSERTのメソッド処理
    public function insert($sql) {
        $stmt = '';
        //セッションにあるパスワード情報をハッシュ化
        $hash_password = password_hash($_SESSION['check']['password'],PASSWORD_BCRYPT);
        $db = $this->db();
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            $_SESSION['check']['name'],
            $_SESSION['check']['email'],
            $hash_password,
            $_SESSION['check']['image']
            ));
        return $stmt;
    } 
    //重複チェックの確認sql
    public function select_duplicate($sql){
        $stmt = '';
        $db = $this->db();
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
          $_SESSION['check']['email']
        ));
        //すでに登録済みの場合は$resultには1以上の値が入るはず
        /*
        【要確認】
        ※$resultの記述を$resul[]の配列に書き換えてもうまくいくか確認する。

        */
        $result = $stmt->fetch();
        if ($result['cnt'] > 0) {
            $error = 'duplicate';
            return $error;
        } 
    }
    //メールアドレスの値をセットし、その結果を返す
    public function select_login($sql){
        $stmt = '';
        $db = $this->db();
        $stmt = $db->prepare($sql);
        
        $stmt->execute(array(
                 $_POST['email']
                ));
        /*
        SQLで一致した値すべてを$resultへ代入しているため、
        複数存在した場合には、$resultの値は複数存在することになる
        fetchして取得したpasswordの値（hash化されたパスワード）が、$_POST['password']と比較して一致していれば、その結果を返す(Trueであれば、ログインさせる、falseであれば、ログインさせない)
        以下でパスワード、ID、ユーザー名、時間をセッションに記録しているのは、
        1.$dbpassword⇒DB登録しているパスワード（ハッシュ化）をユーザー入力したパスワードと照合するため
        2-1.$_SESSION['id']⇒twieet.phpの画面でIDがセットされていることを確認し、セットされていない場合には、強制的にログイン画面に戻す処理をするため
        2-2.また、ログインユーザーと紐づいているセッションIDの人がtwieet.phpでコメント入力してsubmitした場合に、ID、ユーザー名、コメント内容をINSERTするために、ここで$_SESSION['id']にログインユーザーのセッションIDを保存しておく
        2-3.connectクラスのuser_commentメソッドで使用する
        3.$_SESSION['user_name']⇒これについてもIDと一緒で、コメントしたユーザーのユーザ名をDBへINSERTするために,$_SESSION['user_name']へDBから取得したユーザー名を保存
        4.$_SESSION['time']⇒session切れを1時間に設定するため、ログインした時間を$_SESSION['time']に保存
        */
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $dbpassword = $row['PASSWORD'];
            $_SESSION['id'] = $row['ID'];
            $_SESSION['user_name'] = $row['USER_NAME'];
            $_SESSION['time'] = time();
            //tureかfalseの結果を取得
            $auth = password_verify($_POST['password'],$dbpassword);

        if ($auth) {
            $clslogin_flg = true;
        } else {
            $clslogin_flg = false;
        }
        return $clslogin_flg;
        }
    }
    //現在ログインしているユーザーのID情報をセットして、
    //そのIDのユーザー名を掲示板に表示する為のSQL文を発行し、結果を返す
    public function users($sql) {
        $stmt = '';
        $db = $this->db();
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            $_SESSION['id']
        ));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //ここでDBから取得したユーザー名を結果にセットし、返す
            /*$result_id = $row['ID'];
            $result_user = $row['USER_NAME'];*/

           //return $result_user;
           return $row;
        }
    }
    //テキストエリアに入力された内容をINSERT
    //$_SESSION['id']⇒ログイン時のセッションIDをSQLへセット
    //$_SESSION['user_name']⇒ログイン時のユーザー名をSQLへセット
    //$user_comment⇒テキストエリアで入力された値をSQLへセット
    public function user_comment($sql,$bbs_user,$rely_id,$user_comment) {
        $stmt = '';
        $db = $this->db();
        $stmt = $db->prepare($sql);
        /*echo $sql."<br/>";
        echo var_dump($bbs_user);*/
        $stmt->execute(array(
            //※20190818バグ⇒ここの記述をセッションの値をセットするのではなく、DBから取得した値をセットする
            //ひとつの方法として、ここでselectの記述をして、正しい情報をセットするようにする。
            $bbs_user['USER_NAME'],
            $bbs_user['ID'],
            $user_comment,
            $rely_id
    ));
    //return $stmt;
    }
    public function input_login_user($sql,$start_page) {
       //$stmt = '';
       $db = $this->db();
       $stmt = $db->prepare($sql);
       //1つ目の疑問符プレースホルダに、$start_pageの値を、int型でセットして、execute
       $stmt->bindparam(1,$start_page,PDO::PARAM_INT);
       $stmt->execute();
       /*while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
           $result['id'] = $row['ID'];
           $result['user_name'] = $row['USER_NAME'];
           $result['user_comment'] = $row['USER_COMMENT'];
           $result['picture'] = $row['PICTURE'];
           $result['insert_date'] = $row['INS_DATE'];
       }*/
       return $stmt;
    }
    //death_note_dtl　書き込みしたコメントID、ユーザー名を取得
    public function reply($sql,$res) {
        $db = $this->db();
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            $res
        ));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //$user['id'] = $row['COMMENT_ID'];
            //$user['user_name'] = $row['USER_NAME'];
            return $row;
        }
    }
    //death_note_dtlの総コメント数取得メソッド
    public function comment_count($sql) {
        $db = $this->db();
        $stmt = $db->query($sql);
        $result_count = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result_count;
    }
    

}
