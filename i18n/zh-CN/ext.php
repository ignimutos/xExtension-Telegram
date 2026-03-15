<?php

/******************************************************************************/
/* Each entry of that file can be associated with a comment to indicate its   */
/* state. When there is no comment, it means the entry is fully translated.   */
/* The recognized comments are (comment matching is case-insensitive):        */
/*   + TODO: the entry has never been translated.                             */
/*   + DIRTY: the entry has been translated but needs to be updated.          */
/*   + IGNORE: the entry does not need to be translated.                      */
/* When a comment is not recognized, it is discarded.                         */
/******************************************************************************/

return array(
	'telegram' => array(
		'no_configuration' => '请在下方配置你的 Telegram 设置。',
		'bot_token' => 'Bot Token',
		'chat_id' => '聊天 ID',
		'disable_web_page_preview' => '链接预览',
		'disable_web_page_preview_label' => '禁用 Telegram 链接预览',
		'disable_web_page_preview_help' => '默认勾选。勾选后会显式关闭链接预览；取消勾选时会允许 Telegram 尝试为文章链接生成网页预览，是否最终显示取决于 Telegram 对目标链接的解析结果。',
		'filter' => '订阅源过滤',
		'filter_help' => '默认是 *（所有订阅源）。你可以输入多个订阅源名称，并使用英文逗号分隔。只有命中的订阅源会被推送。',
		'exclude_filter' => '排除过滤',
		'exclude_filter_help' => '可选。输入多个订阅源名称并用英文逗号分隔，以跳过命中的订阅源。如果设置为 *，则会排除所有命中的订阅源。',
		'help' => '通过 @BotFather 创建机器人，并通过 @userinfobot 获取目标聊天 ID（用户 / 群组 / 频道）。',
		'about' => array (
			'title' => '关于：FreshRSS Telegram 推送功能',
			'content' => '这是扩展 “xExtension-Telegram” 提供的自定义关于页面。',
		),
	),
);