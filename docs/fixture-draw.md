# Issue #19：赛事对阵数据和页面

本地更新：`php yii migrate`，然后运行 `php yii seed/fixtures`。导入延续原有 4 场样本与演示记录的比赛键，补充 100 场官方比赛；重复导入不产生副本，不覆盖现有演示比赛。还可以用 `php yii seed/fixtures <path>` 导入原 W04 的 20 列格式，原格式不覆写晋级元数据。

- `/world-cup/matches`：按原有时间顺序分页，可筛选是否完赛。
- `/world-cup/groups`：每组一个对阵矩阵，比分从行球队视角读取，点击可进详情。
- `/world-cup/bracket`：按实际来源连接五轮，季军赛单列。已发布演示赛事另列；可用 `?source=demo` 访问。
- `GET /world-cup/draw-data`：公开 JSON，和页面使用同一查询。

## JSON

顶层为 `edition`、`official`、`demo`。两个来源各自返回 `groups: [{name, matches}]` 与 `knockout: []`。比赛包含 `key`（稳定键）、`stage`、`group`、`home`/`away`（`code`, `name`）、`kickoffAt`（UTC ISO 8601）、`status`、`score` 和 `detailUrl`。小组赛另外包含行球队可由此换算的 `homeOutcome`（`win`、`draw`、`loss` 或 `null`）。未开赛比分为 `null`；完赛 `0:0` 为 `{"home":0,"away":0}`。

淘汰赛包含 `winnerSide`（`home`、`away`、`null`）、`decidedBy`（`regular`、`extra-time`、`penalties`、`null`）、`penalties`（与 `score` 同形或 `null`），以及两路上轮来源 `homeSource`、`awaySource`。例如决赛的 `homeSource` 为 `{"nodeId":"official-2026-101","outcome":"winner"}`，季军赛同一来源的 `outcome` 是 `loser`。`nodeId` 指向另一场比赛的 `key`，不是数据库 ID。关联超出公开届次、同一来源或发布范围时，`nodeId` 留空；缺少整场比赛记录时不会返回虚构节点。上轮来源可为空；完赛无明确胜方时保持待确认。轮次为 `group`、`round-of-32`、`round-of-16`、`quarter-final`、`semi-final`、`final`、`third-place`。

## 数据与兼容性

新增数据来自 [FIFA 官方赛历](https://api.fifa.com/api/v3/calendar/matches?language=en&count=500&idSeason=285023)；原始字段 `Date` / `LocalDate` 核对 UTC 与场馆时区，`PlaceHolderA/B` 提供上轮胜者或负者来源，`Winner` / `ResultType` / `*TeamPenaltyScore` 提供决胜结果。来源可与 [FIFA 官方赛程赛果](https://www.fifa.com/en/tournaments/mens/worldcup/canadamexicousa2026/articles/match-schedule-fixtures-results-teams-stadiums) 对照。首次淘汰赛的对阵直接用官方确认球队，绝不从小组样本反推。

迁移只给 `fixture` 增加六个可空字段，无需新表。仍沿用 W11 的官方赛事只读、演示赛事可编辑；图表不会读取草稿或其他届次，官方和演示之间也不会串线。撤销该迁移只会删掉晋级元数据，已导入的比赛行不会自动删除。

验证：`php tests/fixture-draw.php http://127.0.0.1:8080`。脚本检查官方比赛数量、晋级来源、点球比分、季军赛、延期比分和公开范围。
