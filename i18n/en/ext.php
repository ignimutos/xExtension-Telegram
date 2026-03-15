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
		'no_configuration' => 'Please configure your Telegram settings below.',
		'bot_token' => 'Bot token',
		'chat_id' => 'Chat ID',
		'disable_web_page_preview' => 'Link preview',
		'disable_web_page_preview_label' => 'Disable Telegram link preview',
		'disable_web_page_preview_help' => 'Checked by default. When checked, Telegram webpage previews for the article URL are explicitly disabled. When unchecked, Telegram can generate a webpage preview for the article URL.',
		'filter' => 'Filter feeds',
		'filter_help' => 'Default is * (all feeds). You can enter multiple feed names separated by commas. Only matched feed names will be pushed.',
		'exclude_filter' => 'Exclude filter',
		'exclude_filter_help' => 'Optional. Enter multiple feed names separated by commas to skip matched feeds. When set to * , all matched feeds will be excluded.',
		'help' => 'Create a bot via @BotFather and enter your target chat ID via @userinfobot (user / group / channel).',
		'about' => array (
			'title' => 'About: Telegram Functions for FreshRSS',
			'content' => 'A custom about page provided by the extension “xExtension-Telegram”.'
		),
	),
);
