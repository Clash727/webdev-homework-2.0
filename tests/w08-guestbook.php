<?php
/** 用途：验证真实 HTTP 留言提交、公开边界、转义与分页，清理本次测试数据。作者：Clash727（W08） */
use app\models\GuestMessage;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');
$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$cookies = [];
$checks = 0;
$marker = 'w08_' . bin2hex(random_bytes(6));
$ownRows = static fn () => GuestMessage::find()->where(['like', 'nickname', $marker]);

function check(bool $condition, string $description): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL ' . $description);
    }
    $checks++;
    echo 'PASS ' . $description . PHP_EOL;
}

function request(string $path, string $method = 'GET', array $data = []): array
{
    global $baseUrl, $cookies;
    $headers = ['Accept: text/html', 'Cookie: ' . implode('; ', $cookies)];
    if ($method !== 'GET') {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $body = file_get_contents($baseUrl . $path, false, stream_context_create(['http' => [
        'method' => $method, 'header' => implode("\r\n", $headers),
        'content' => $method === 'GET' ? '' : http_build_query($data),
        'follow_location' => 0, 'ignore_errors' => true,
    ]]));
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

try {
    $page = request('/guestbook');
    check($page['status'] === 200 && str_contains($page['body'], 'id="guestbook-form"'), 'guest can open form');
    check(str_contains($page['body'], 'action="/guestbook"'), 'form posts to shared external path');
    check(str_contains(request('/world-cup/fans')['body'], 'href="/guestbook"'), 'discussion links to working form');
    $token = csrf($page);
    $nickname = $marker . '_visitor';
    $body = "看好这场比赛。\n<script>alert('test')</script>";
    $start = gmdate('Y-m-d H:i:s');
    $response = request('/guestbook', 'POST', [
        '_csrf' => $token, 'GuestMessage' => [
            'nickname' => ' ' . $nickname . ' ', 'body' => ' ' . $body . ' ',
            'status' => 'approved', 'id' => 4294967295,
            'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00',
        ],
    ]);
    check($response['status'] === 303 && str_ends_with($response['location'], '/guestbook'), 'valid submit uses 303 redirect');
    $pending = GuestMessage::findOne(['nickname' => $nickname]);
    check($pending !== null && $pending->status === GuestMessage::STATUS_PENDING, 'all new messages are pending despite forged status');
    check((int) $pending->id !== 4294967295 && $pending->created_at >= $start
        && $pending->updated_at === $pending->created_at, 'ID and UTC timestamps come from server');
    check($pending->body === $body, 'trim text without interpreting HTML');
    $page = request('/guestbook');
    check(str_contains($page['body'], '留言已提交，待审核。'), 'show clear pending receipt');
    check(!str_contains($page['body'], $nickname), 'pending message remains private on list');
    $token = csrf($page);
    $before = $ownRows()->count();
    $refresh = request('/guestbook');
    check($ownRows()->count() === $before && !str_contains($refresh['body'], '留言已提交，待审核。'), 'refresh neither duplicates submission nor repeats receipt');

    $response = request('/guestbook', 'POST', [
        '_csrf' => $token, 'GuestMessage' => ['nickname' => $nickname, 'body' => " \r\n "],
    ]);
    check($response['status'] === 200 && str_contains($response['body'], '留言不能为空'), 'empty body returns inline validation error');
    check(str_contains($response['body'], 'value="' . $nickname . '"'), 'failed submit keeps nickname');
    check($ownRows()->count() === $before, 'empty body does not insert a row');
    $response = request('/guestbook', 'POST', [
        '_csrf' => $token, 'GuestMessage' => ['nickname' => '', 'body' => $body],
    ]);
    check($response['status'] === 200 && str_contains($response['body'], '&lt;script&gt;'), 'failed submit keeps and escapes body');
    check(!str_contains($response['body'], "<script>alert('test')</script>"), 'form never executes submitted HTML');
    foreach ([
        ['nickname' => str_repeat('名', 81), 'body' => '正文'],
        ['nickname' => $nickname, 'body' => str_repeat('文', 2001)],
    ] as $invalid) {
        check(request('/guestbook', 'POST', ['_csrf' => $token, 'GuestMessage' => $invalid])['status'] === 200
            && $ownRows()->count() === $before, 'reject overlong input without insert');
    }
    check(request('/guestbook', 'POST', ['GuestMessage' => ['nickname' => $nickname, 'body' => '无令牌']])['status'] === 400,
        'CSRF is required');
    check(request('/guestbook', 'PUT', ['_csrf' => $token])['status'] === 405, 'wrong method returns 405');
    check($ownRows()->count() === $before, 'rejected requests leave row count unchanged');

    foreach ([
        ['nickname' => $marker . '_approved<b>', 'body' => "公开内容\n<script>公开</script>", 'status' => 'approved'],
        ['nickname' => $marker . '_rejected', 'body' => '拒绝内容', 'status' => 'rejected'],
    ] as $row) {
        Yii::$app->db->createCommand()->insert('guest_message',
            $row + ['created_at' => $start, 'updated_at' => $start])->execute();
    }
    foreach (['/guestbook', '/world-cup/fans'] as $path) {
        $public = request($path)['body'];
        check(str_contains($public, $marker . '_approved&lt;b&gt;'), $path . ' shows approved nickname as text');
        check(str_contains($public, '&lt;script&gt;公开&lt;/script&gt;') && str_contains($public, '<br'),
            $path . ' escapes body and keeps newline');
        check(!str_contains($public, $nickname) && !str_contains($public, $marker . '_rejected'),
            $path . ' hides pending and rejected messages');
    }

    // Same timestamps deliberately exercise the ID tie-breaker.
    for ($i = 0; $i < 21; $i++) {
        Yii::$app->db->createCommand()->insert('guest_message', [
            'nickname' => $marker . '_page_' . $i, 'body' => '分页检查', 'status' => 'approved',
            'created_at' => '2099-01-01 00:00:00', 'updated_at' => '2099-01-01 00:00:00',
        ])->execute();
    }
    $firstPage = request('/guestbook')['body'];
    preg_match_all('/<article class="fan-message">/', $firstPage, $articles);
    check(count($articles[0]) === 20, 'public page contains twenty approved messages');
    check(strpos($firstPage, $marker . '_page_20') < strpos($firstPage, $marker . '_page_19'), 'newest ID sorts first for equal times');
    check(!str_contains($firstPage, $marker . '_page_0<'), 'twenty-first message goes to next page');
    check(str_contains(request('/guestbook?page=2')['body'], $marker . '_page_0'), 'next page is reachable');
    check(request('/guestbook?page=0')['status'] === 400 && request('/guestbook?page=no')['status'] === 400, 'invalid page returns 400');
    $fans = request('/world-cup/fans')['body'];
    preg_match_all('/<article class="fan-message">/', $fans, $articles);
    check(count($articles[0]) === 6 && !str_contains($fans, $marker . '_page_14<'), 'discussion reuses six newest approved messages');
    echo "{$checks} 项 W08 检查通过。\n";
} finally {
    GuestMessage::deleteAll(['like', 'nickname', $marker]);
}
