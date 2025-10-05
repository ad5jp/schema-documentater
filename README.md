# Schema Writer

データベースの実スキーマを元に、テーブル定義書を Excel 等のファイルとして出力します。

現在は

- データベース・・・MySQL
- 出力先・・・日本語 Excel ファイル

のみに対応しています。

##　使い方

### プロジェクトに追加して使う場合

composer でインストールします。

```
composer require --dev ad5jp/schema-writer
```

プロジェクトディレクトリでコマンドを実行します。

```
./vendor/bin/documentate
```

### 単独で使用する場合

Github よりリポジトリをフォークした後、本リポジトリのディレクトリに入り、実行します。

```
php documentate
```

## オプション

必要なオプションを省略した場合は、対話的に実行されます。

| driver | データベースの種類。省略時は mysql |
| format | 出力フォーマット。省略時は excel_jp |
| host | データベースのホスト名 |
| schema | データベース名 |
| user | データベースのユーザ名 |
| password | データベースユーザのパスワード |
| template | Excel ファイルの雛形のパス。format が excel_* の場合に指定可能 |
| output_dir | Excel ファイルを出力するディレクトリ。format が excel_* の場合に指定可能 |
| output_filename | 出力する Excel ファイルのファイル名。format が excel_* の場合に指定可能 |
