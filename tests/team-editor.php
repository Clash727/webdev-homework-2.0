<?php
/**
 * 用途：真实登录、资料编辑、链接归属、原子保存及前台同步检查；结束恢复资料。
 * 作者：zhixiu-feng
 * 用法：php tests/team-editor.php http://127.0.0.1:8080
 */

use app\models\Account;
use app\models\Fixture;
use app\models\MemberLink;
use app\models\ProjectMember;
use app\models\ProjectTeam;
use app\models\ProjectTeamLink;
use yii\helpers\Html;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
if (!in_array(parse_url($base, PHP_URL_HOST), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('仅允许本机验收。');
}
$http = curl_init();
curl_setopt_array($http, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 15]);
$checks = 0;
function verify(bool $condition, string $label): void
{
    global $checks;
    if (!$condition) { throw new RuntimeException('FAIL ' . $label); }
    $checks++;
    echo 'PASS ' . $label . PHP_EOL;
}
function request(string $path, string $method = 'GET', array $data = []): array
{
    global $base, $http;
    curl_setopt_array($http, [CURLOPT_URL => $base . $path,
        CURLOPT_POSTFIELDS => http_build_query($data), CURLOPT_CUSTOMREQUEST => $method]);
    $body = curl_exec($http);
    if ($body === false) { throw new RuntimeException(curl_error($http)); }
    return ['status' => (int) curl_getinfo($http, CURLINFO_RESPONSE_CODE), 'body' => $body];
}
function token(array $response): string
{
    if (!preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response['body'], $match)) {
        throw new RuntimeException('Missing CSRF token.');
    }
    return html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
}
function saveRecord(yii\db\ActiveRecord $record): void
{
    if (!$record->save()) { throw new RuntimeException(implode('; ', $record->getFirstErrors())); }
}

$team = ProjectTeam::findPublic()->one();
$member = $team ? $team->getMembers()->one() : null;
if ($member === null) { throw new RuntimeException('先运行迁移与团队初始化。'); }
$teamBefore = $team->getAttributes();
$memberBefore = $member->getAttributes();
$originalMemberLinks = $member->getLinks()->asArray()->all();
$originalTeamLinks = $team->getResourceLinks()->asArray()->all();
$fixtureBefore = Fixture::find()->orderBy('id')->asArray()->all();
$nonce = bin2hex(random_bytes(6));
$admin = new Account();
$admin->username = 'editor_check_' . $nonce;
$admin->display_name = '临时验收账号';
$admin->auth_key = Yii::$app->security->generateRandomString(32);
$password = Yii::$app->security->generateRandomString(32);
$admin->setPassword($password);
$otherTeam = null;

try {
    saveRecord($admin);
    verify(request('/admin/team')['status'] === 302, '访客不能进入团队编辑');
    verify(request('/admin/members/' . $member->id . '/edit')['status'] === 302, '访客不能进入成员编辑');
    $public = request('/team');
    verify(request('/admin/team', 'POST', ['_csrf' => token($public), 'ProjectTeam' => ['name' => '不应保存']])['status'] === 302,
        '访客有效 CSRF 的 POST 也不能写入');
    $login = request('/admin/login');
    verify(request('/admin/login', 'POST', ['_csrf' => token($login), 'LoginForm' => ['username' => $admin->username, 'password' => $password]])['status'] === 303,
        '复用真实管理员登录');
    $edit = request('/admin/team');
    verify($edit['status'] === 200 && str_contains($edit['body'], 'team-editor'), '团队编辑表单正常展示');
    verify(str_contains(request('/admin')['body'], '/admin/team'), '工作台包含团队管理入口');
    $csrf = token($edit);
    verify(request('/admin/team', 'POST', ['ProjectTeam' => ['name' => '不应保存']])['status'] === 400, '缺 CSRF 不写库');
    $pendingName = '待保存-' . $nonce;
    $add = request('/admin/team', 'POST', ['_csrf' => $csrf, 'intent' => 'add-link',
        'ProjectTeam' => ['name' => $pendingName], 'Links' => [['title' => '尚未保存-' . $nonce, 'url' => 'https://example.com', 'sort_order' => 1]]]);
    verify($add['status'] === 200 && str_contains($add['body'], Html::encode($pendingName))
        && str_contains($add['body'], 'Links[1][title]'), '添加链接行保留所有输入');
    verify(ProjectTeam::findOne($team->id)->name === $teamBefore['name']
        && (int) $team->getResourceLinks()->count() === count($originalTeamLinks), '添加空行不保存资料');

    $newName = '<团队-' . $nonce . '>';
    $summary = "新的团队简介\n<script>只作为文字</script>";
    $saved = request('/admin/team', 'POST', ['_csrf' => $csrf, 'ProjectTeam' => ['name' => $newName, 'summary' => $summary],
        'Links' => [['title' => '资源甲-' . $nonce, 'url' => 'https://example.com/?a=1&b=2', 'sort_order' => 2],
            ['title' => '资源乙-' . $nonce, 'url' => 'http://example.org/', 'sort_order' => 1], ['title' => '', 'url' => '', 'sort_order' => 0]]]);
    verify($saved['status'] === 303, '保存成功使用重定向');
    $feedback = request('/admin/team');
    verify(str_contains($feedback['body'], '团队资料已保存。'), '成功提示可见');
    verify(!str_contains(request('/admin/team')['body'], '团队资料已保存。'), '刷新不重复保存或显示旧提示');
    $public = request('/team');
    verify(str_contains($public['body'], Html::encode($newName)) && str_contains($public['body'], '&lt;script&gt;只作为文字&lt;/script&gt;'), '公开团队页同步资料并编码 HTML');
    verify(str_contains($public['body'], '资源甲-' . $nonce) && str_contains($public['body'], '资源乙-' . $nonce)
        && strpos($public['body'], '资源乙-' . $nonce) < strpos($public['body'], '资源甲-' . $nonce), '团队链接按顺序公开展示');
    verify(str_contains($public['body'], 'a=1&amp;b=2') && !str_contains($public['body'], '<script>只作为文字</script>'), '链接参数与正文不会注入 HTML');
    $first = ProjectTeamLink::findOne(['project_team_id' => $team->id, 'title' => '资源甲-' . $nonce]);
    $second = ProjectTeamLink::findOne(['project_team_id' => $team->id, 'title' => '资源乙-' . $nonce]);
    verify($first !== null && $second !== null, '两个资源链接均入库，空行被忽略');

    $failed = request('/admin/team', 'POST', ['_csrf' => $csrf, 'ProjectTeam' => ['name' => '不得部分保存-' . $nonce],
        'Links' => [['id' => $first->id, 'delete' => 1], ['title' => '错误链接-' . $nonce, 'url' => 'javascript:alert(1)', 'sort_order' => 0]]]);
    verify($failed['status'] === 200 && str_contains($failed['body'], '不得部分保存-' . $nonce) && str_contains($failed['body'], 'javascript:alert(1)'), '失败保留资料及链接输入');
    verify(ProjectTeam::findOne($team->id)->name === $newName && ProjectTeamLink::findOne($first->id) !== null, '一行无效时，资料修改与链接删除全部不落库');
    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'ProjectTeam' => ['name' => '   ']])['status'] === 200
        && ProjectTeam::findOne($team->id)->name === $newName, '空白团队名不能保存');
    $sameLink = ['id' => $first->id, 'title' => '资源甲-' . $nonce, 'url' => 'https://example.com', 'sort_order' => 0];
    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'Links' => [$sameLink, $sameLink]])['status'] === 400, '拒绝重复链接 ID');
    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'ProjectTeam' => ['slug' => 'other-team']])['status'] === 400, '拒绝修改固定团队标识');
    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'Links' => [['title' => ['nested'], 'url' => 'https://example.com']]])['status'] === 400,
        '嵌套异常输入正常拒绝');

    $otherTeam = new ProjectTeam(['slug' => 'probe-' . $nonce, 'name' => '隔离团队', 'summary' => '隔离']);
    saveRecord($otherTeam);
    $outside = new ProjectMember(['project_team_id' => $otherTeam->id, 'display_name' => '隔离成员', 'role' => '隔离']);
    saveRecord($outside);
    $outsideLink = new ProjectTeamLink(['project_team_id' => $otherTeam->id, 'title' => '隔离链接', 'url' => 'https://example.com']);
    saveRecord($outsideLink);
    verify(request('/admin/members/' . $outside->id . '/edit')['status'] === 404, '其他团队成员不能编辑');
    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'Links' => [['id' => $outsideLink->id, 'delete' => 1]]])['status'] === 400
        && ProjectTeamLink::findOne($outsideLink->id) !== null, '伪造跨团队链接删除被拒绝');
    verify(request('/admin/members/4294967296/edit')['status'] === 404, '超范围成员 ID 为 404');
    verify(request('/admin/members/0/edit')['status'] === 404, '非法成员 ID 为 404');

    $memberPath = '/admin/members/' . $member->id . '/edit';
    $memberPage = request($memberPath);
    verify($memberPage['status'] === 200 && str_contains($memberPage['body'], 'member-editor'), '成员编辑表单正常展示');
    $memberName = '<姓名-' . $nonce . '>';
    $memberSaved = request($memberPath, 'POST', ['_csrf' => token($memberPage),
        'ProjectMember' => ['display_name' => $memberName, 'role' => '更新的分工', 'bio' => "个人简介\n第二行", 'sort_order' => 25],
        'Links' => [['title' => '个人链接-' . $nonce, 'url' => 'https://example.com/profile', 'sort_order' => 0]]]);
    verify($memberSaved['status'] === 303, '成员资料与链接一起保存');
    $detail = request('/team/members/' . $member->id);
    verify(str_contains($detail['body'], Html::encode($memberName)) && str_contains($detail['body'], '更新的分工')
        && str_contains($detail['body'], '个人链接-' . $nonce), '访客成员详情立即同步');
    verify(str_contains(request('/project')['body'], Html::encode($memberName))
        && ProjectMember::findOne($member->id)->contribution_key === $memberBefore['contribution_key'], '改名同步项目经历且不改变分工关联');
    verify(!str_contains(request('/team')['body'], '成员姓名暂为占位'), '维护真实姓名后不再显示全员占位说明');
    verify(request($memberPath, 'POST', ['_csrf' => $csrf, 'ProjectMember' => ['contribution_key' => 'member-4']])['status'] === 400, '拒绝修改稳定分工标识');
    verify(request($memberPath, 'POST', ['_csrf' => $csrf, 'ProjectMember' => ['project_team_id' => $otherTeam->id]])['status'] === 400, '拒绝成员跨团队移动');
    $foreignMemberLink = new MemberLink(['project_member_id' => $outside->id, 'title' => '隔离个人链接', 'url' => 'https://example.org']);
    saveRecord($foreignMemberLink);
    verify(request($memberPath, 'POST', ['_csrf' => $csrf, 'Links' => [['id' => $foreignMemberLink->id, 'title' => '篡改', 'url' => 'https://example.com', 'sort_order' => 0]]])['status'] === 400,
        '不能通过链接 ID 改其他成员资料');
    $personal = MemberLink::findOne(['project_member_id' => $member->id, 'title' => '个人链接-' . $nonce]);
    $invalidMember = request($memberPath, 'POST', ['_csrf' => $csrf, 'ProjectMember' => ['display_name' => '不会保存'],
        'Links' => [['id' => $personal->id, 'title' => '个人链接-' . $nonce, 'url' => 'https://example.com', 'sort_order' => -1]]]);
    verify($invalidMember['status'] === 200 && ProjectMember::findOne($member->id)->display_name === $memberName
        && MemberLink::findOne($personal->id)->sort_order === 0, '链接排序无效时成员修改也不保存');

    verify(request('/admin/team', 'POST', ['_csrf' => $csrf, 'Links' => [['id' => $first->id, 'delete' => 1]]])['status'] === 303
        && ProjectTeamLink::findOne($first->id) === null && ProjectTeamLink::findOne($second->id) !== null, '仅明确勾选的链接被删除，遗漏行保留');
    verify(!str_contains(request('/team')['body'], '资源甲-' . $nonce), '前台不再显示已删除资源');
    foreach ($originalMemberLinks as $link) { verify(MemberLink::findOne($link['id'])->getAttributes() === $link, '未提交的原成员链接保留'); }
    foreach ($originalTeamLinks as $link) { verify(ProjectTeamLink::findOne($link['id'])->getAttributes() === $link, '未提交的原团队链接保留'); }
    verify(Fixture::find()->orderBy('id')->asArray()->all() === $fixtureBefore, '团队编辑不改变赛事数据');
    $edit = request('/admin/team');
    verify(request('/admin/team', 'DELETE', ['_csrf' => token($edit)])['status'] === 405, '后台不接受 DELETE 写入');
    verify(request('/admin/logout', 'POST', ['_csrf' => token($edit)])['status'] === 303, '真实会话退出成功');
    verify(request('/admin/team')['status'] === 302 && request($memberPath)['status'] === 302, '退出后两种编辑入口均转登录');
    verify(request($memberPath, 'POST', ['_csrf' => $csrf, 'ProjectMember' => ['display_name' => '退出后不应保存']])['status'] === 302
        && ProjectMember::findOne($member->id)->display_name === $memberName, '退出后的 POST 不能修改资料');
    echo "{$checks} 项后台编辑检查通过。\n";
} finally {
    ProjectTeamLink::deleteAll(['and', ['project_team_id' => $team->id], ['like', 'title', $nonce]]);
    MemberLink::deleteAll(['and', ['project_member_id' => $member->id], ['like', 'title', $nonce]]);
    ProjectTeam::updateAll($teamBefore, ['id' => $team->id]);
    ProjectMember::updateAll($memberBefore, ['id' => $member->id]);
    if ($otherTeam !== null && !$otherTeam->isNewRecord) { $otherTeam->delete(); }
    if (!$admin->isNewRecord) { $admin->delete(); }
    curl_close($http);
}
