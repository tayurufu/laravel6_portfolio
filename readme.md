#Laravel 6 portfolio

## Features
 
laravel6のポートフォリオです。  
一画面ごとにvueを使用しています。  

* ログイン  
(laravel標準機能とsocialiteを使用したGoogleアカウントログイン)
* 商品登録(商品登録権限ありのユーザーのみ)
* 商品一覧・詳細・購入(決済処理なし)・注文明細メール送信
* アクセス権限変更(admin権限のみ)
 
## Requirement
 
主要ライブラリ
 
* php 7.2
* laravel 6.2
* vue 2.5.17
* spatie/laravel-permission 3.*
* mySql 5.7

## Deployment
AWSで公開しています  
<https://www.tayutayu.work>  
画像の保存先にS3、DBにRDSを使用しています。

CircleCiとAWS Code Deployでテスト後に自動でプロイしています。
