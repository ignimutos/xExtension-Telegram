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
		'no_configuration' => 'Bitte konfigurieren Sie unten Ihre Telegram-Einstellungen.',
		'bot_token' => 'Bot-Token',
		'chat_id' => 'Chat-ID',
		'disable_web_page_preview' => 'Link-Vorschau',
		'disable_web_page_preview_label' => 'Telegram-Linkvorschau deaktivieren',
		'disable_web_page_preview_help' => 'Standardmäßig aktiviert. Wenn aktiviert, wird die Telegram-Linkvorschau für die Artikel-URL explizit deaktiviert. Wenn nicht aktiviert, kann Telegram für die Artikel-URL eine Webseitenvorschau erzeugen.',
		'filter' => 'Feed-Filter',
		'filter_help' => 'Standard ist * (alle Feeds). Sie können mehrere Feed-Namen durch Kommas getrennt eingeben. Nur passende Feed-Namen werden gepusht.',
		'exclude_filter' => 'Ausschlussfilter',
		'exclude_filter_help' => 'Optional. Geben Sie mehrere Feed-Namen durch Kommas getrennt ein, um passende Feeds auszuschließen. Wenn * gesetzt ist, werden alle passenden Feeds ausgeschlossen.',
		'help' => 'Erstellen Sie einen Bot über @BotFather und geben Sie Ihre Ziel-Chat-ID über @userinfobot ein (Benutzer / Gruppe / Kanal).',
		'about' => array (
			'title' => 'Über: Telegram-Funktionen für FreshRSS',
			'content' => 'Eine benutzerdefinierte „Über“-Seite, bereitgestellt von der Erweiterung „xExtension-Telegram“.',
		),
	),
);
