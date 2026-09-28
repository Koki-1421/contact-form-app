## 環境構築

### 1. リポジトリをクローン

```bash
git clone https://github.com/Koki-1421/contact-form-app.git
cd contact-form-app
```

### 2. Composerパッケージをインストール

```bash
composer install
```

### 3. `.env` ファイルを作成

```bash
cp .env.example .env
```

`.env` のデータベース接続情報が以下の設定になっていることを確認します。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

`DB_HOST` には `localhost` や `127.0.0.1` ではなく、Dockerコンテナ名である `mysql` を指定します。

### 4. Laravel Sailを起動

```bash
./vendor/bin/sail up -d
```

必要に応じて、Sailを `sail` コマンドで実行できるようにエイリアスを設定します。

bashの場合：

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
```

zshの場合：

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc
```

設定後、シェルを再起動します。

```bash
exec $SHELL
```

### 5. アプリケーションキーを生成

```bash
sail artisan key:generate
```

### 6. データベースのマイグレーションと初期データ投入

```bash
sail artisan migrate --seed
```

既存のデータベースをリセットして初期データを投入する場合は、以下を実行します。

```bash
sail artisan migrate:fresh --seed
```

### 7. フロントエンド依存パッケージをインストール

```bash
sail npm install
```

### 8. Vite開発サーバーを起動

```bash
sail npm run dev
```

Vite開発サーバーは、アプリケーションを利用している間は起動したままにします。

## 使用技術

- PHP 8.2
- Laravel 10.x
- MySQL 8.0
- Nginx
- Docker
- Laravel Sail
- phpMyAdmin
- Vite
- Tailwind CSS 3.4
- Laravel Fortify

## APIエンドポイント

| メソッド | エンドポイント | 概要 |
| --- | --- | --- |
| GET | `/api/v1/contacts` | お問い合わせ一覧取得 |
| GET | `/api/v1/contacts/{contact}` | お問い合わせ詳細取得 |
| POST | `/api/v1/contacts` | お問い合わせ登録 |
| PUT | `/api/v1/contacts/{contact}` | お問い合わせ更新 |
| DELETE | `/api/v1/contacts/{contact}` | お問い合わせ削除 |

APIは認証不要の公開APIとして実装しています。

## 開発環境URL

- アプリケーション: http://localhost
- phpMyAdmin: http://localhost:8080

## テスト

以下のコマンドでテストを実行できます。

```bash
sail artisan test
```

カバレッジを確認する場合は以下を実行します。

```bash
sail artisan test --coverage
```

## コードフォーマット

Laravel Pintを使用しています。

```bash
sail bin pint --test
```

## 作成者

Koki