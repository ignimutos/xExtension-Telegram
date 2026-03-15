# xExtension-Telegram

一个 FreshRSS 扩展：将新抓取到的订阅条目推送到 Telegram Bot，并支持按订阅源过滤、排除过滤以及链接预览开关。

## 功能

- 在 FreshRSS 扩展配置页填写以下参数：
  - `bot_token`
  - `chat_id`
  - `disable_web_page_preview`
  - `filter`
  - `exclude-filter`
- 每当 FreshRSS 抓取到新的未读条目时，自动调用 Telegram Bot API `sendMessage` 推送；若当前 FreshRSS 支持，会优先在 `entry_before_add` 阶段执行，以便读取过滤动作处理后的最终已读状态。
- 推送内容包含：
  - 订阅源名称（如果可用）
  - 文章标题（若标题为空则回退到 GUID）
  - 文章链接
- 消息超过 3500 个字符时会自动截断，并在末尾追加省略号。
- 可配置是否关闭 Telegram 链接预览（默认不关闭；未勾选时不会显式禁用预览，是否最终显示预览取决于 Telegram 对目标链接的解析结果）。
- 可通过 `filter` 与 `exclude-filter` 同时控制推送范围：
  - `filter: *`：推送所有订阅源（默认）
  - `filter: RSS1,RSS2`：仅推送名称命中的订阅源（逗号分隔）
  - `exclude-filter: RSS3,RSS4`：从最终推送范围中排除命中的订阅源
  - 例如：`filter: *` 且 `exclude-filter: nodeseek` 时，除 `nodeseek` 外的订阅源都会推送
- 如果条目在进入推送逻辑时已经是已读状态（例如被过滤动作自动标记为已读），则不会推送。
- 提供扩展 About 页面，用于展示该扩展的简要说明。

## 安装

将本扩展目录放到 FreshRSS 的 `./extensions` 目录下（目录名例如 `xExtension-Telegram`），然后在 FreshRSS 后台启用该扩展。

## 配置

1. 打开 FreshRSS 后台 → 扩展管理 → 本扩展的配置页面。
2. 填写：
   - `bot_token`：从 `@BotFather` 创建机器人后获得
   - `chat_id`：你要接收消息的会话 ID（个人 / 群组 / 频道）
   - `disable_web_page_preview`：是否关闭链接预览（勾选=关闭；不勾选=允许 Telegram 尝试生成预览，是否最终显示由 Telegram 决定）
   - `filter`：订阅源包含过滤规则，默认 `*`；可填多个订阅源名称并用英文逗号分隔
   - `exclude-filter`：订阅源排除过滤规则，默认空；可填多个订阅源名称并用英文逗号分隔
3. 保存后，新抓取的条目将被推送到 Telegram。

## 说明

- 如果未配置 `bot_token` 或 `chat_id`，扩展会跳过推送。
- 已读条目不会推送。
- 如果文章标题为空，会回退使用条目的 GUID 作为标题。
- 推送消息最长为 3500 个字符，超出部分会被截断并追加省略号。
- 当 `filter` 不是 `*` 时，仅当条目所属订阅源名称命中 `filter` 列表才会推送（名称大小写不敏感，精确匹配）。
- 当条目所属订阅源名称命中 `exclude-filter` 列表时，不会推送；该排除规则会在 `filter` 命中后继续生效。
- 未勾选 `disable_web_page_preview` 时，扩展不会向 Telegram 显式传递禁用预览参数；但是否出现预览，仍取决于 Telegram 是否能成功抓取并解析目标链接。
- 网络失败或 Telegram 返回非成功状态时，不会中断 FreshRSS 抓取流程，只记录 warning 日志。
