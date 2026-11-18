# W08 留言提交

按 W02 契约实现站点级留言，页面沿用现有宋体、配色和开放式排版。保留字段标签、错误信息和待审回执，省去次级口号及解释段落。

## 使用

在配置好本地数据库的项目根目录执行 php yii migrate，然后启动 php yii serve 127.0.0.1 --port=8080。

访问 /guestbook，或从世界杯专题的球迷讨论页点击“写留言”。昵称为 1–80 字符，正文为 1–2000 字符，先去除首尾空白。成功提交以 303 返回列表，并提示“留言已提交，待审核。”；刷新不会重新提交。校验失败以 200 重绘错误和原输入。

当前本机预览使用 http://127.0.0.1:8081/guestbook。

## 交付与接口

| 层次 | 路径 |
| --- | --- |
| 迁移 | migrations/m261117_000001_create_guest_message_table.php |
| 模型 | models/GuestMessage.php |
| 控制器 | controllers/GuestbookController.php |
| 视图 | views/guestbook/index.php、views/shared/_guest-message.php |
| 样式 | web/css/guestbook.css |
| 现有专题接入 | models/WorldCupPortal.php、views/world-cup-hub/fans.php |
| 导航状态 | views/layouts/main.php、views/shared/_cup-tabs.php |
| 检查 | tests/w08-guestbook.php、tests/portal-pages.php |

guest_message 包含无符号 ID、nickname、body、status、created_at、updated_at。使用 InnoDB、utf8mb4_unicode_ci；时间由应用按 UTC 写入，公开视图显示北京时间。status 有 pending / approved / rejected 三种取值及数据库 CHECK，并建立状态、创建时间、ID 的联合索引。

表单仅接收 nickname 和 body。状态、ID 和时间不从请求批量赋值；模型新建记录时强制 pending。公共列表只读 GuestMessage::findApproved()，按 created_at DESC、id DESC 排序，每页 20 条。讨论页使用同一查询取最新 6 条。昵称与正文 HTML 编码，保留正文换行。

W12 使用现有 GuestMessage 及上述状态常量实现审核；审核通过后两个公开页面直接读取同一记录。W08 不提供审核或自动批准入口。

## 本机验证

2026-11-17，Windows、PHP 8.2.12、Yii 2.0.55、MySQL 8.4.9。

- 已应用留言迁移。
- php tests/w08-guestbook.php http://127.0.0.1:8081：32 项真实 HTTP / MySQL 检查通过，覆盖提交、待审回执、防重复刷新、失败保留输入、长度限制、CSRF、错误方法、服务端字段控制、公开状态隔离、HTML 转义、排序和分页。测试数据最后清理。
- php tests/portal-pages.php http://127.0.0.1:8081：105 项专题检查通过；同步原脚本对 W13 第五个导航入口的断言，并检查留言页的导航状态。
- 浏览器完成真实表单提交；桌面双列与 390px 手机单列均无横向溢出。页面截图在本机 .local/ 下，界面验收留言已清理。
- PHP 语法与 Git 空白检查通过。
