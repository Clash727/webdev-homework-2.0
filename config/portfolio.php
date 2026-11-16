<?php
/**
 * 用途：四位成员的职责与已实现成果展示。
 * 作者：zhixiu-feng
 */

$repository = 'https://github.com/kleedaisuki/webdev-homework-2.0';
return [
    'member-1' => [
        'number' => '01', 'role' => '工程与站点整合',
        'summary' => '搭建运行基线，让独立功能成为一个完整的网站。',
        'scope' => ['工程与管理员身份', '首页、导航与作业下载', '项目展示与整体联调'],
        'works' => [
            ['title' => '管理员登录', 'url' => ['auth/login']],
            ['title' => '工程与运行说明', 'url' => $repository . '/blob/main/README.md'],
        ],
    ],
    'member-2' => [
        'number' => '02', 'role' => '团队展示与数据设计',
        'summary' => '组织团队资料与数据关系，让每一份贡献有迹可循。',
        'scope' => ['数据与路由契约', '团队主页与资料编辑', '设计文档、数据库恢复与验收'],
        'works' => [
            ['title' => '团队成员展示', 'url' => ['team/index']],
            ['title' => '数据结构与接口契约', 'url' => $repository . '/blob/main/docs/contracts.md'],
        ],
    ],
    'member-3' => [
        'number' => '03', 'role' => '赛事浏览与赛果发布',
        'summary' => '把赛程、比分与比赛故事，变成清楚可查的信息。',
        'scope' => ['需求分析与赛事查询', '后台维护与发布赛果', '用户手册、演示与提交清单'],
        'works' => [
            ['title' => '赛程与赛果', 'url' => ['world-cup/index']],
            ['title' => '项目需求文档', 'url' => $repository . '/blob/main/docs/requirements.md'],
        ],
    ],
    'member-4' => [
        'number' => '04', 'role' => '视觉与球迷互动',
        'summary' => '确定页面与赛事素材，让球迷的声音进入网站。',
        'scope' => ['视觉方案与赛事样本', '留言提交与后台审核', '个人作业归档、实现与部署文档'],
        'works' => [
            ['title' => '页面与视觉方案', 'url' => $repository . '/blob/main/docs/ui.md'],
            ['title' => '世界杯赛事样本', 'url' => $repository . '/blob/main/data/seed/world-cup-sample.csv'],
        ],
    ],
];
