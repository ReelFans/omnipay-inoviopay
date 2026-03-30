# omnipay-inoviopay
InovioPay Gateway 向けの Omnipay ドライバです。

[Omnipay](https://github.com/thephpleague/omnipay) は、フレームワーク非依存のマルチゲートウェイ決済ライブラリです。
このパッケージは Omnipay 3 向けの InovioPay サポートを提供します。

[InovioPay](https://www.inoviopay.com/) は、シームレスな統合とグローバルなスケーラビリティを備えた決済ゲートウェイです。

## インストール

Composer でインストールします。

```json
{
    "require": {
        "mvestil/omnipay-inoviopay": "^1.0",
        "league/omnipay": "^3.2"
    }
}
```

アプリケーション側で `league/omnipay` を導入しない場合は、`omnipay/common` 3.x が要求する
HTTPlug 互換の HTTP client 実装を別途用意してください。

この fork は PHP `^8.1` を対象とし、PHP 8.5 での利用を想定しています。

## この Fork の背景

元のパッケージは Omnipay 2.x / PHP 5.x 系の依存関係を前提に作られており、そのままでは現在の運用環境に適合しませんでした。

この fork は、以下の要件を満たすために作成しています。

* Omnipay 3.x で動作すること
* PHP 8.5 を含む新しい PHP バージョンで動作すること
* 既存アプリケーションの呼び出しコードとの互換性をできるだけ維持すること

## この Fork で追加した独自実装

この fork では、公開 API (`purchase`, `completePurchase`, `refund`, `void`) は維持したまま、
内部の通信処理を Omnipay 3 向けに更新しています。

主な追加・変更点は以下です。

* Omnipay 3 / `omnipay/common` 3.x に対応した HTTP リクエスト処理
* PHP `^8.1` 対応
* gateway 内での厳密な JSON レスポンス解析
* `Response::getResponseCode()` の追加
  * InovioPay の `SERVICE_RESPONSE` を返します

`getResponseCode()` を追加した理由は、既存アプリケーション側で raw response を直接読むのではなく、
Omnipay の response object から InovioPay 固有のエラーコードを参照しているためです。

## 基本機能

このパッケージは REST API 経由で以下のトランザクションを提供します。

* 購入
* 返金
* 取消
* 3D Secure 購入

一般的な Omnipay の使い方については、[Omnipay](https://github.com/thephpleague/omnipay) 本体も参照してください。

## 使い方

### Gateway を生成する

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('InovioPay');
$gateway->initialize([
    'reqUsername' => 'api-user',
    'reqPassword' => 'api-password',
    'siteId' => '12345',
    'merchAcctId' => '67890',
    'productId' => '11111',
    'testMode' => false,
]);
```

### カード情報を使った都度決済

```php
use Omnipay\Common\CreditCard;

$card = new CreditCard([
    'firstName' => 'Taro',
    'lastName' => 'Yamada',
    'number' => '4242424242424242',
    'expiryMonth' => '01',
    'expiryYear' => '2032',
    'cvv' => '123',
    'email' => 'customer@example.com',
    'billingCountry' => 'JP',
]);

$response = $gateway->purchase([
    'amount' => '1000.00',
    'currency' => 'JPY',
    'card' => $card,
    'transactionId' => 'ORDER-10001',
    'clientIp' => '203.0.113.10',
])->send();

if ($response->isSuccessful()) {
    $transactionReference = $response->getTransactionReference();
    $customerToken = $response->getCustomerReference();
    $cardToken = $response->getCardReference();
} else {
    $responseCode = $response->getResponseCode();
    $message = $response->getMessage();
}
```

### トークン決済

```php
$response = $gateway->purchase([
    'amount' => '1000.00',
    'currency' => 'JPY',
    'cardReference' => $cardToken,
    'customerReference' => $customerToken,
    'transactionId' => 'ORDER-10002',
    'clientIp' => '203.0.113.10',
    'isAutoRebill' => 1,
])->send();
```

### Response Object で使える主なメソッド

この gateway は通常の Omnipay response object を返します。既存アプリケーションでは以下のメソッドを利用できます。

* `isSuccessful()`
* `isRedirect()`
* `getMessage()`
* `getTransactionReference()`
* `getCustomerReference()`
* `getCardReference()`
* `getResponseCode()`

`getResponseCode()` は InovioPay の `SERVICE_RESPONSE` を返します。
gateway 固有の失敗コードをアプリケーション側でマッピングしている場合に利用してください。

## 注意点

カード決済と token 決済の両方に対応しています。
token 決済を行うには、`customer id (cust_id)` と `payment id (pmt_id)` を渡す必要があります。
これらの値は、最初のカード決済成功時の response から取得できます。

現時点では単一商品の購入のみ対応しています。複数商品は未対応です。

このパッケージを利用するには、API 認証情報に加えて `Product Id (li_prod_id_1)` を request body に含める必要があります。
`Product Id` は InovioPay 管理画面で `Variable Price Product` を作成することで取得できます。

## テストモード

API のエンドポイントは `https://api.inoviopay.com/payment/pmt_service.cfm` です。

## 認証情報

InovioPay Payments API を呼び出すには、以下の値が必要です。

* `reqUsername`
* `reqPassword`
* `siteId`
* `merchAcctId`

これらは InovioPay の管理画面で確認できます。

## テスト

現時点では自動テストは含まれていません。

## サポート

Omnipay 全般に関する問題は、[Stack Overflow](http://stackoverflow.com/) の
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) も参照してください。

Omnipay 本体のリリース情報や議論については、
[mailing list](https://groups.google.com/forum/#!forum/omnipay) も利用できます。
