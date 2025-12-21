# attendance-management-app

## 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```
make init
```



### メール認証
mailtrapというツールを使用しています。<br>
以下のリンクから会員登録をしてください。<br>
[https://mailtrap.io/](https://mailtrap.io/)

SandboxesのAction部分にある設定マーク(歯車マーク)を選択し、<br>
※もしない場合はAddSandboxであらたにSandboxを追加してください<br>
Integrationsを選択して「Code Samples」の「PHP」から 「laravel 7.x and 8.x」を選択し、<br>
.envファイルのMAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピー＆ペーストしてください。<br>
※MAIL_PASSWORDの記述部分はCredentialsのPasswordの部分をコピー＆ペーストしてください。<br>
MAIL_FROM_ADDRESSは任意のメールアドレスを入力してください。


## ER図

![ER図](/AttendanceManagementAppER.png)


## URL
・一般ユーザー会員登録画面：[http://localhost/register](http://localhost/register)

・一般ユーザーログイン画面：[http://localhost/login](http://localhost/login)

・管理者ユーザーログイン画面：[http://localhost/admin/login](http://localhost/admin/login)


## テストアカウント
### 一般アカウント
name: 一般ユーザー１
email: user1@example.com
password: password
-------------------------
name: 一般ユーザー２
email: user2@example.com
password: password
-------------------------
name: 一般ユーザー３
email: user3@example.com
password: password
-------------------------
name: 一般ユーザー４
email: user4@example.com
password: password
-------------------------

### 管理者アカウント
name: 管理者１
email: admin@example.com
password: password
-------------------------

## 使用技術（実行環境）

・PHP 8.1.33

・Laravel 8.83.29

・MySQL 8.0.26

・nginx 1.21.1


## PHPUnitを使用したテストについて
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
php artisan test tests/Feature
```

## LaravelDuskを使用したブラウザテストについて
以下のコマンド：
```


docker-compose exec php bash

```
