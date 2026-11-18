<?php
/**
 * 用途：验证四个一级页面、专题导航及已发布数据的公开边界。
 * 作者：zhixiu-feng
 * 用法：php tests/portal-pages.php http://127.0.0.1:8080
 */

use app\models\Fixture;
use app\models\ProjectMember;
use app\models\ProjectTeam;
use app\models\WorldCupPortal;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
if (!in_array(parse_url($base, PHP_URL_HOST), ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('仅允许本机检查。');
}
$http = curl_init();
curl_setopt_array($http, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 15]);
$checks = 0;
function verify(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) { throw new RuntimeException('FAIL ' . $message); }
    $checks++;
    echo 'PASS ' . $message . PHP_EOL;
}
function fetchPage(string $path, string $method = 'GET', array $data = []): array
{
    global $http, $base;
    curl_setopt($http, CURLOPT_URL, $base . $path);
    curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($http, CURLOPT_CUSTOMREQUEST, $method);
    $body = curl_exec($http);
    if ($body === false) { throw new RuntimeException(curl_error($http)); }
    return [(int) curl_getinfo($http, CURLINFO_RESPONSE_CODE), $body];
}
function document(string $html): DOMXPath
{
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    return new DOMXPath($dom);
}
try {
    foreach (['/' => '主页', '/team' => '团队成员', '/project' => '项目经历',
        '/world-cup' => '世界杯专题', '/world-cup/highlights' => '世界杯专题', '/world-cup/fans' => '世界杯专题',
        '/guestbook' => '世界杯专题'] as $path => $active) {
        [$status, $html] = fetchPage($path);
        verify($status === 200, $path . ' 正常打开');
        $dom = document($html);
        $nav = $dom->query('//nav[@aria-label="主导航"]/a');
        verify($nav->length === 5, $path . ' 包含五个一级导航（含作业下载）');
        $current = $dom->query('//nav[@aria-label="主导航"]/a[@aria-current="page"]');
        verify($current->length === 1 && trim($current[0]->textContent) === $active, $path . ' 当前导航正确');
        verify($dom->query('//h1')->length === 1, $path . ' 具有唯一主标题');
        foreach ($dom->query('//link[@rel="stylesheet"] | //script[@src] | //img[@src] | //iframe') as $asset) {
            $url = $asset->getAttribute('href') ?: $asset->getAttribute('src');
            verify(!preg_match('~^(https?:)?//~i', $url), $path . ' 不依赖外部运行资源');
        }
    }
    [$status, $home] = fetchPage('/');
    verify(substr_count($home, 'class="result-card"') === count(WorldCupPortal::recentResults()), '首页复用已发布赛果，最多三条');
    foreach (Fixture::find()->where(['publication_status' => 'draft'])->all() as $draft) {
        verify(!str_contains($home, '/world-cup/matches/' . $draft->id . '"'), '首页不泄漏草稿详情链接');
    }
    [, $teamHtml] = fetchPage('/team');
    verify(document($teamHtml)->query('//li[contains(@class,"team-card")]')->length === 4, '团队主页展示四位成员');
    [, $projectHtml] = fetchPage('/project');
    verify(document($projectHtml)->query('//article[@class="contribution-card"]')->length === 4, '项目经历展示四份分工');
    foreach (ProjectTeam::findPublic()->one()->members as $member) {
        verify(str_contains($projectHtml, yii\helpers\Html::encode($member->display_name)), '项目分工关联成员 ' . $member->id);
        verify($member->contribution !== null, '成员 ' . $member->id . ' 有稳定分工关联');
    }
    [, $clips] = fetchPage('/world-cup/highlights');
    $links = document($clips)->query('//a[contains(@class,"highlight-art")]');
    verify($links->length === 3, '精彩栏目提供三个官方观看入口');
    foreach ($links as $link) {
        verify(str_starts_with($link->getAttribute('href'), 'https://www.fifa.com/en/watch'), '视频入口指向 FIFA');
        verify($link->getAttribute('rel') === 'noopener noreferrer', '外部观看入口安全打开');
    }
    verify(document($clips)->query('//video | //iframe')->length === 0, '不伪装站内视频或加载远程播放器');
    [, $fans] = fetchPage('/world-cup/fans');
    if (!WorldCupPortal::discussion()['canSubmit']) {
        verify(str_contains($fans, '讨论区即将开放') && !str_contains($fans, 'href="/guestbook"'), '留言功能未合入时不提供失效的提交入口');
    }
    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $home, $match);
    foreach (['/', '/project', '/world-cup/highlights', '/world-cup/fans'] as $path) {
        verify(fetchPage($path, 'POST', ['_csrf' => html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')])[0] === 405,
            $path . ' 拒绝带有效 CSRF 的写请求');
    }
    echo "{$checks} 项专题检查通过。\n";
} finally {
    curl_close($http);
}
