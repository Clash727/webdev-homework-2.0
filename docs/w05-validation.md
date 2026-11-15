# W05 管理员登录与后台入口

任务：[Issue #9](https://github.com/kleedaisuki/webdev-homework-2.0/issues/9)。基线：已合并 W01、[W02 契约](contracts.md)。

## 安装与使用

先按 [W01 安装说明](w01-validation.md) 安装依赖、初始化本机配置并连接 MySQL。然后在项目根目录执行：

```text
php yii migrate
php yii account/create-admin
php yii serve 127.0.0.1 --port=8080
```

`account/create-admin` 交互读取用户名、展示名及两次密码。用户名去除首尾空白并转小写，只允许 3–64 位 ASCII 字母、数字或下划线；展示名为 1–80 字符。密码为 8–72 字节，输入时隐藏，不放入命令参数；Windows 使用系统 PowerShell 读取，Linux / WSL 使用 `stty` 关闭终端回显。重复用户名报错，保留原账号与密码。数据库仅保存 Yii 密码哈希和随机的 32 字符 `auth_key`。

浏览器打开 `http://127.0.0.1:8080/admin/login`，使用刚创建的账号登录。成功后以 303 跳转 `/admin`，工作台显示经过 HTML 编码的展示名；顶部“退出登录”为带 CSRF 的 POST 表单，退出后跳回登录页。首页提供管理员登录入口。

所有有效账号均为管理员，独立于课设成员与国家队。当前交付不包含业务编辑功能。

## 交付路径与下游接口

| 层次 | 实际路径 | 职责 |
| --- | --- | --- |
| 数据库 | `migrations/m261115_000001_create_account_table.php` | `account` 表、唯一用户名索引、InnoDB / utf8mb4；支持回滚 |
| Model | `models/Account.php` | ActiveRecord、UTC 时间戳、IdentityInterface、Yii 密码校验 |
| Model | `models/LoginForm.php` | 登录校验、统一错误凭证提示、会话登录 |
| Controller | `controllers/AdminBaseController.php` | 共用 AccessControl，仅允许已登录账号 |
| Controller | `controllers/AdminController.php` | GET `/admin` 工作台 |
| Controller | `controllers/AuthController.php` | GET / POST 登录、仅已登录 POST 退出 |
| View | `views/auth/login.php`、`views/admin/index.php` | 登录表单与工作台 |
| View / 样式 | `views/layouts/main.php`、`web/css/site.css` | 登录、后台与退出导航及表单样式 |
| CLI | `commands/AccountController.php` | 隐藏密码输入、创建管理员、拒绝重复用户名 |
| 配置 | `config/web.php` | `Account` 身份、禁用自动登录、HttpOnly / SameSite=Lax 会话 Cookie |
| 回归验证 | `tests/w05-auth.php` | 临时账号与真实 HTTP 会话的认证验证 |

W10、W11、W12 的后台业务控制器继承 `app\controllers\AdminBaseController`，定义自己的 `VerbFilter` 时合并父类行为：

```php
public function behaviors(): array
{
    return array_merge(parent::behaviors(), [
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => ['update' => ['GET', 'POST']],
        ],
    ]);
}
```

判断当前身份使用 `!Yii::$app->user->isGuest`，读取管理员 ID 使用 `Yii::$app->user->id`。保留 Yii 默认 CSRF 校验，写操作限定 POST，不自动重放登录前提交的表单。登录成功固定进入本站 `/admin`，不采用外部返回地址。退出使用 `user->logout()` 销毁会话。

本单按 W02 的单一管理员身份落地；W03 需求稿中的成员权限、`account.role` 与本单范围不一致，未据此新增角色。后台赛事路径仍遵循 W02 的 `/admin/matches`；数据库与应用时区继续使用 UTC。

## 验证方法

应用 MySQL 迁移、启动本地服务后，另开终端运行：

```text
php tests/w05-auth.php
```

如服务使用其他端口，可将地址作为唯一参数，例如 `php tests/w05-auth.php http://127.0.0.1:8081`。测试 CLI 和 Web 服务必须连接同一个本地开发库。

脚本使用随机用户名和密码建立临时账号，通过真实 HTTP 请求验证认证流程，最后删除该账号；现有管理员保持不变。脚本不输出密码，不需要 Node.js、测试框架或额外 Composer 依赖。

## 本机验证记录

2026-11-15，Windows、PHP 8.2.12、Yii 2.0.55、MySQL 8.4.9，本地服务 `127.0.0.1:8080`。

- 从尚无业务表的本地开发库应用迁移，成功建立 `migration` 和 `account`，用户名具有唯一索引。
- 通过真实交互终端创建本机验收管理员，两次密码输入均只显示掩码；密码与本机配置留在 Git 忽略目录。
- 重复运行创建命令并输入同名账号与不同密码，命令返回失败并提示用户名已存在；原账号保留。
- HTTP 回归通过：错误密码与不存在账号使用同一提示，失败不产生身份，密码不回填。
- 正确密码登录返回 303，忽略外部返回地址；会话 ID 更新，后续请求可恢复管理员身份，展示名经 HTML 编码。
- 访客 GET 与有效 CSRF 的 POST 访问后台均转登录；登录 POST 缺少 CSRF 返回 400。
- 已登录时后台首页 POST 返回 405；GET 退出返回 405，缺少 CSRF 的 POST 退出返回 400，均保留会话。
- 有效 POST 退出返回 303；退出后访问后台和重放退出前的会话均被拦截。公共首页正常，应用日志不含测试密码。
- 浏览器检查登录页布局与中文显示正常，页面截图保存在本机 `.local/w05-login.jpg`。

同机验证记录不替代其他组员独立环境的复现。
