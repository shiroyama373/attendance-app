# Atte（勤怠管理システム）

## 環境構築

### Dockerビルド
1. git clone リンク
2. cd attendance-app
3. docker-compose up -d --build

※ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせて docker-compose.yml ファイルを編集してください。

### Laravel環境構築
1. docker-compose exec app php bash
2. composer install
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

### 全ユーザーをメール認証済みにする
```bash
docker-compose exec db mysql -u root -proot -D attendance_db -e "UPDATE users SET email_verified_at = NOW();"
```

## 使用技術
- PHP 8.2
- Laravel 10.x
- MySQL 8.0

## URL
- 開発環境：http://localhost:8001/
- phpMyAdmin：http://localhost:8080/
- MailHog：http://localhost:8025/

## ER図
![ER図](docs/er-diagram.png)


## テストアカウント
### 管理者
- メール：admin@example.com
- パスワード：password123

### 一般ユーザー
- メール：yamada@example.com
- パスワード：password123