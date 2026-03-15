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
		'no_configuration' => 'Veuillez configurer vos paramètres Telegram ci-dessous.',
		'bot_token' => 'Jeton du bot',
		'chat_id' => 'ID du chat',
		'disable_web_page_preview' => 'Aperçu des liens',
		'disable_web_page_preview_label' => 'Désactiver l’aperçu des liens Telegram',
		'disable_web_page_preview_help' => 'Activé par défaut. Quand cette option est activée, l’aperçu de page web Telegram pour l’URL de l’article est explicitement désactivé. Quand elle n’est pas activée, Telegram peut générer un aperçu de page web pour l’URL de l’article.',
		'filter' => 'Filtre des flux',
		'filter_help' => 'Par défaut : * (tous les flux). Vous pouvez saisir plusieurs noms de flux séparés par des virgules. Seuls les noms de flux correspondants seront envoyés.',
		'exclude_filter' => 'Filtre d’exclusion',
		'exclude_filter_help' => 'Optionnel. Saisissez plusieurs noms de flux séparés par des virgules pour ignorer les flux correspondants. Si la valeur est * , tous les flux correspondants seront exclus.',
		'help' => 'Créez un bot via @BotFather et saisissez votre ID de chat cible via @userinfobot (utilisateur / groupe / canal).',
		'about' => array (
			'title' => 'À propos : Fonctions Telegram pour FreshRSS',
			'content' => 'Une page « À propos » personnalisée fournie par l’extension « xExtension-Telegram ».',
		),
	),
);
