<?php
/**
 * 用途：真实 MySQL 与 HTTP 验证团队展示、访问边界与输出安全；自动清理临时数据。
 * 作者：zhixiu-feng
 * 用法：php tests/team-pages.php http://127.0.0.1:8080
 */

use app\models\MemberLink;
use app\models\ProjectMember;
use app\models\ProjectTeam;
use yii\helpers\Html;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
if (!in_array(parse_url($base, PHP_URL_HOST), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('仅允许连接本机验收服务。');
}
$checks = 0;
$http = curl_init();
curl_setopt_array($http, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '', CURLOPT_COOKIEFILE => '']);

function check(bool $condition, string $label): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL ' . $label);
    }
    $checks++;
    echo 'PASS ' . $label . PHP_EOL;
}

function page(string $path, string $method = 'GET', array $data = []): array
{
    global $base, $http;
    curl_setopt($http, CURLOPT_URL, $base . $path);
    curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($http, CURLOPT_CUSTOMREQUEST, $method);
    $body = curl_exec($http);
    if ($body === false) {
        throw new RuntimeException('HTTP connection failed: ' . curl_error($http));
    }
    return [(int) curl_getinfo($http, CURLINFO_RESPONSE_CODE), $body];
}

function saveRecord(yii\db\ActiveRecord $record): void
{
    if (!$record->save()) {
        throw new RuntimeException(implode('; ', $record->getFirstErrors()));
    }
}

$team = ProjectTeam::findPublic()->one();
if ($team === null) {
    throw new RuntimeException('先运行 php yii migrate 与 php yii team-seed。');
}
$memberIds = [];
$otherTeam = null;
$nonce = bin2hex(random_bytes(5));

try {
    [$status, $home] = page('/team');
    check($status === 200 && str_contains($home, Html::encode($team->name)), '团队主页从数据库显示名称');
    check(str_contains($home, nl2br(Html::encode($team->summary), false)), '团队简介正确显示');
    check(page('/')[0] === 200, '世界杯首页保持独立');
    check(page('/css/team.css')[0] === 200, '页面样式从本地加载');

    foreach ($team->members as $member) {
        [$status, $detail] = page('/team/members/' . $member->id);
        check($status === 200 && str_contains($detail, Html::encode($member->display_name))
            && str_contains($detail, Html::encode($member->role)), '成员 ' . $member->id . ' 资料与详情对应');
        check(str_contains($home, '/team/members/' . $member->id), '主页可打开成员 ' . $member->id);
    }

    $a = new ProjectMember(['project_team_id' => $team->id, 'display_name' => '<script>probe-' . $nonce . '</script>',
        'role' => '安全 & 展示', 'bio' => "第一行\n<img src=x onerror=alert(1)>", 'sort_order' => 0]);
    saveRecord($a);
    $memberIds[] = $a->id;
    $b = new ProjectMember(['project_team_id' => $team->id, 'display_name' => '无链接-' . $nonce,
        'role' => '独立资料', 'bio' => '', 'sort_order' => 0]);
    saveRecord($b);
    $memberIds[] = $b->id;

    $otherTeam = new ProjectTeam(['slug' => 'probe-' . $nonce, 'name' => '非公开团队', 'summary' => '隔离数据']);
    saveRecord($otherTeam);
    $outside = new ProjectMember(['project_team_id' => $otherTeam->id, 'display_name' => '非本团队成员', 'role' => '隔离']);
    saveRecord($outside);

    $link = new MemberLink(['project_member_id' => $a->id, 'title' => '仓库 & 代码',
        'url' => 'https://example.com/?a=1&b=2', 'sort_order' => 0]);
    saveRecord($link);
    $second = new MemberLink(['project_member_id' => $a->id, 'title' => '另一个链接', 'url' => 'http://example.org/', 'sort_order' => 0]);
    saveRecord($second);

    $bad = new MemberLink(['project_member_id' => $a->id, 'title' => '不可执行链接', 'url' => 'javascript:alert(1)']);
    check(!$bad->validate() && $bad->hasErrors('url'), '模型拒绝 JavaScript 链接');
    $bad->url = '//example.com';
    check(!$bad->validate() && $bad->hasErrors('url'), '模型拒绝省略协议的链接');
    $bad->url = 'javascript:alert(1)';
    $bad->sort_order = 1;
    $bad->save(false); // 模拟绕过模型写入的历史记录，验证视图仍不会输出危险链接。

    [$status, $detail] = page('/team/members/' . $a->id);
    check($status === 200 && str_contains($detail, Html::encode($a->display_name))
        && !str_contains($detail, $a->display_name), '成员名中的 HTML 只作为文字显示');
    check(str_contains($detail, '&lt;img src=x onerror=alert(1)&gt;') && str_contains($detail, '<br>'), '简介换行保留并转义 HTML');
    check(str_contains($detail, '仓库 &amp; 代码') && str_contains($detail, 'a=1&amp;b=2'), '链接标题与地址安全编码');
    check(!str_contains($detail, 'javascript:') && !str_contains($detail, '不可执行链接'), '历史危险链接不进入公开 HTML');
    check(str_contains($detail, 'rel="noopener noreferrer"'), '新窗口链接隔离 opener');
    check(strpos($detail, '仓库 &amp; 代码') < strpos($detail, '另一个链接'), '同排序链接按 ID 稳定排序');
    [$status, $noLinks] = page('/team/members/' . $b->id);
    check($status === 200 && str_contains($noLinks, Html::encode($b->display_name))
        && !str_contains($noLinks, 'team-member-links'), '无简介和无链接的成员仍正常显示');
    check(str_contains($noLinks, 'href="/team"'), '成员详情可返回团队主页');

    [$status, $home] = page('/team');
    check(strpos($home, Html::encode($a->display_name)) < strpos($home, Html::encode($b->display_name)), '同排序成员按 ID 稳定排序');
    check(!str_contains($home, '非本团队成员'), '主页仅展示当前课设团队成员');
    check(page('/team/members/' . $outside->id)[0] === 404, '其他团队成员直接访问为 404');
    foreach (['0', '-1', 'abc', '4294967296', '999999999999999999999999'] as $id) {
        check(page('/team/members/' . $id)[0] === 404, '非法或超范围 ID ' . $id . ' 为 404');
    }
    $missingId = (int) ProjectMember::find()->max('id') + 1;
    check(page('/team/members/' . $missingId)[0] === 404, '不存在的成员为正常 404');

    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $home, $token);
    check(isset($token[1]), '公开页生成 CSRF token');
    $data = ['_csrf' => html_entity_decode($token[1], ENT_QUOTES, 'UTF-8')];
    check(page('/team', 'POST', $data)[0] === 405, '团队主页拒绝有效 CSRF 的 POST');
    check(page('/team/members/' . $a->id, 'POST', $data)[0] === 405, '成员页拒绝有效 CSRF 的 POST');

    $invalid = new ProjectMember(['project_team_id' => $team->id, 'display_name' => '   ', 'role' => '   ', 'sort_order' => -1]);
    check(!$invalid->validate() && $invalid->hasErrors('display_name') && $invalid->hasErrors('role')
        && $invalid->hasErrors('sort_order'), '空白必填与负排序值校验生效');
    $invalid->project_team_id = 0;
    check(!$invalid->validate() && $invalid->hasErrors('project_team_id'), '无效父键校验生效');

    $before = $team->getAttributes();
    check(Yii::$app->runAction('team-seed') === 0, '重复初始化命令成功');
    check($team->refresh() && $team->getAttributes() === $before && ProjectMember::findOne($a->id) !== null,
        '重复初始化保留现有团队与成员资料');

    $orphanRejected = false;
    try {
        Yii::$app->db->createCommand()->insert(MemberLink::tableName(), [
            'project_member_id' => 0, 'title' => 'orphan', 'url' => 'https://example.com',
            'sort_order' => 0, 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ])->execute();
    } catch (yii\db\IntegrityException $error) {
        $orphanRejected = true;
    }
    check($orphanRejected, '真实 MySQL 外键拒绝孤立链接');
    $outsideId = $outside->id;
    $otherTeam->delete();
    check(ProjectMember::findOne($outsideId) === null, '删除团队级联删除其成员');
    $otherTeam = null;
    $a->delete();
    check(!MemberLink::find()->where(['project_member_id' => $a->id])->exists(), '删除成员级联删除其链接');

    echo "{$checks} 项检查通过。\n";
} finally {
    if ($memberIds !== []) {
        ProjectMember::deleteAll(['id' => $memberIds]);
    }
    if ($otherTeam !== null && !$otherTeam->isNewRecord) {
        $otherTeam->delete();
    }
    curl_close($http);
}
