# 团队主页与成员详情

已完成三张课设团队表、公开模型和页面，以及可重复执行的演示资料初始化命令。数据库字段与路由遵循 [数据与路由契约](contracts.md)，页面沿用现有配色与成员卡片布局。

## 使用

按 [环境说明](w01-validation.md) 安装锁定依赖并配置本机 MySQL 后，在项目根目录执行：

```text
php yii migrate --interactive=0
php yii team-seed
php yii serve 127.0.0.1 --port=8080
```

打开 `/team`，通过成员卡片进入 `/team/members/{id}`。初始化命令仅在 `our-team` 尚不存在时，以一个事务创建演示团队、四位占位成员与一条公开链接；已有资料不会被重复运行覆盖。成员名、分工与简介均明确为演示资料，不代表实际同学身份。

页面只读取 `slug=our-team` 的课设团队；成员与个人链接按 `sort_order, id` 升序。成员没有简介或链接时仍正常展示；无成员时显示空状态；缺少团队时返回带初始化提示的 404。不存在、非法、超范围或属于其他团队的成员同样返回 404。

文本按纯文本编码输出并保留换行；链接仅允许完整 HTTP(S) URL，对历史数据也执行协议检查。两个公开页面仅允许 GET。团队资源链接表及后台编辑未包含在本次改动中。

## 主要文件

| 层次 | 文件 |
| --- | --- |
| 模型 | `models/ProjectTeam.php`、`models/ProjectMember.php`、`models/MemberLink.php` |
| 控制器 | `controllers/TeamController.php` |
| 视图 | `views/team/index.php`、`views/team/member.php` |
| 数据 | `migrations/m261116_100001_create_project_team_table.php`、`migrations/m261116_100002_create_project_member_table.php`、`migrations/m261116_100003_create_member_link_table.php` |
| 初始化 | `commands/TeamSeedController.php` |
| 样式 | `web/css/team.css` |

## 验证结果

2026-11-16，Windows / PHP 8.3.36 / Yii 2.0.55 / MySQL 8.0.45。使用独立空库，连接与数据库均为 `utf8mb4`，会话时区 `+00:00`。

- 全部迁移成功，三张团队表回滚后可重新建立；种子首次创建成功，重复运行保留现有资料。
- 空团队 HTTP 404，响应含维护者初始化提示。
- `php tests/team-pages.php http://127.0.0.1:8080` 的 41 项真实 MySQL / HTTP 检查通过，包含成员资料对应、空简介 / 空链接、稳定排序、HTML 编码、危险链接拦截、跨团队访问隔离、GET 方法限制、外键与级联删除。脚本创建的临时数据在结束时清理。
- 已有管理员身份检查与赛事浏览检查通过。
- 浏览器检查四位成员卡片和入口，HTTP 分别验证四位成员详情、返回入口及链接展示；390px 窄屏卡片单列排列，无横向溢出。
- 新增 PHP 文件语法与 Git 空白检查通过。本机连接、随机凭据、依赖和运行文件均未纳入版本控制。

本次本机验收服务使用 `http://127.0.0.1:8086`，上述命令中的 `8080` 可替换为实际端口。前台新增模块、导航与四人展示见 [世界杯主题站说明](world-cup-portal.md)。
