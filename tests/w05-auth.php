<?php
/**
 * 用途：使用临时账号回归验证真实 MySQL 与 HTTP 会话认证，结束后删除该账号。
 * 作者：Clash727（W05）
 */

use app\models\Account;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$cookies = [];

function check(bool $passed, string $description): void
{
    if (!$passed) {
        throw new RuntimeException($description);
    }
    echo 'PASS ' . $description . PHP_EOL;
}

function request(string $path, string $method = 'GET', array $data = []): array
{
    global $baseUrl, $cookies;
    $headers = ['Accept: text/html', 'Cookie: ' . implode('; ', $cookies)];
    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $context = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $method === 'POST' ? http_build_query($data) : '',
        'ignore_errors' => true,
        'follow_location' => 0,
    ]]);
    $body = file_get_contents($baseUrl . $path, false, $context);
    $location = '';
    foreach ($http_response_header as $header) {
        if (stripos($header, 'Set-Cookie: ') === 0) {
            $cookie = explode(';', substr($header, 12), 2)[0];
            $cookies[explode('=', $cookie, 2)[0]] = $cookie;
        }
        if (stripos($header, 'Location: ') === 0) {
            $location = substr($header, 10);
        }
    }
    return ['status' => (int) substr($http_response_header[0], 9, 3),
        'body' => $body, 'location' => $location];
}

function csrf(array $response): string
{
    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response['body'], $match);
    return html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
}

$password = Yii::$app->security->generateRandomString(32);
$account = new Account();
$account->username = 'w05_check_' . bin2hex(random_bytes(5));
$account->display_name = '<W05 & 管理员>';
$account->auth_key = Yii::$app->security->generateRandomString(32);
$account->setPassword($password);
check($account->save(), 'create temporary administrator');

try {
    check($account->password_hash !== $password && $account->validatePassword($password), 'store and verify Yii password hash');
    check(Account::findByUsername(' ' . strtoupper($account->username) . ' ')->id === $account->id, 'normalize username');
    check(Account::findIdentity($account->id)->validateAuthKey($account->auth_key), 'restore identity and validate auth key');
    check(!Account::findIdentity($account->id)->validateAuthKey('incorrect'), 'reject incorrect auth key');
    check(Account::findIdentityByAccessToken('unused') === null, 'token login is unavailable');
    $duplicate = new Account($account->getAttributes());
    check(!$duplicate->validate() && $duplicate->hasErrors('username'), 'reject duplicate username');

    $response = request('/admin');
    check($response['status'] === 302 && str_ends_with($response['location'], '/admin/login'), 'guest is redirected from admin');
    $response = request('/admin/login');
    check($response['status'] === 200 && str_contains($response['body'], 'autocomplete="current-password"'), 'render login form');
    $token = csrf($response);
    $initialSession = $cookies['PHPSESSID'];
    $response = request('/admin', 'POST', ['_csrf' => $token]);
    check($response['status'] === 302, 'guest POST with valid CSRF is still denied');
    $response = request('/admin/login', 'POST', ['LoginForm' => ['username' => $account->username, 'password' => $password]]);
    check($response['status'] === 400, 'reject login without CSRF');

    $response = request('/admin/login', 'POST', ['_csrf' => $token,
        'LoginForm' => ['username' => $account->username, 'password' => 'incorrect-password']]);
    check($response['status'] === 200 && str_contains($response['body'], '用户名或密码错误。'), 'reject incorrect password');
    check(!str_contains($response['body'], 'incorrect-password'), 'do not echo password');
    $response = request('/admin/login', 'POST', ['_csrf' => $token,
        'LoginForm' => ['username' => $account->username . '_missing', 'password' => 'incorrect-password']]);
    check($response['status'] === 200 && str_contains($response['body'], '用户名或密码错误。'), 'use same error for missing account');
    check(request('/admin')['status'] === 302, 'failed login does not establish identity');

    $response = request('/admin/login?returnUrl=https%3A%2F%2Fexample.com', 'POST', ['_csrf' => $token,
        'LoginForm' => ['username' => strtoupper($account->username), 'password' => $password]]);
    check($response['status'] === 303 && str_ends_with($response['location'], '/admin'), 'successful login redirects to local admin');
    check($cookies['PHPSESSID'] !== $initialSession, 'regenerate session ID on login');
    $response = request('/admin');
    check($response['status'] === 200 && str_contains($response['body'], '&lt;W05 &amp; 管理员&gt;'), 'restore session identity and escape display name');
    $token = csrf($response);
    check(request('/admin/login')['status'] === 303, 'logged-in login page redirects to admin');
    check(request('/admin', 'POST', ['_csrf' => $token])['status'] === 405, 'admin home only allows GET');
    check(request('/admin/logout')['status'] === 405, 'GET cannot log out');
    check(request('/admin/logout', 'POST')['status'] === 400, 'logout requires CSRF');
    check(request('/admin')['status'] === 200, 'rejected logout keeps session');
    $oldCookies = $cookies;
    $response = request('/admin/logout', 'POST', ['_csrf' => $token]);
    check($response['status'] === 303 && str_ends_with($response['location'], '/admin/login'), 'POST logout redirects to login');
    check(request('/admin')['status'] === 302, 'protected page is denied after logout');
    $cookies = $oldCookies;
    check(request('/admin')['status'] === 302, 'old session cannot be replayed after logout');
    check(request('/')['status'] === 200, 'public home remains available');

    $logFile = dirname(__DIR__) . '/runtime/logs/app.log';
    check(!is_file($logFile) || !str_contains(file_get_contents($logFile), $password), 'password is absent from application logs');
    echo "W05 authentication checks passed.\n";
} finally {
    $account->delete();
}
