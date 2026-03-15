<?php

class TelegramExtension extends Minz_Extension {
	private const CONF_BOT_TOKEN = 'bot_token';
	private const CONF_CHAT_ID = 'chat_id';
	private const CONF_DISABLE_WEB_PAGE_PREVIEW = 'disable_web_page_preview';
	private const CONF_FILTER = 'filter';
	private const CONF_EXCLUDE_FILTER = 'exclude-filter';
	private const LEGACY_CONF_TARGET = 'target';

	private string $botToken = '';
	private string $chatId = '';
	private bool $disableWebPagePreview = true;
	private string $filter = '*';
	private string $excludeFilter = '';

	public function init(): void {
		$this->registerTranslates();
		$this->registerHook($this->getEntryHookName(), [$this, 'pushEntryToTelegramHook']);
	}

	private function getEntryHookName(): string {
		try {
			if (class_exists('Minz_HookType') && method_exists('Minz_HookType', 'tryFrom') && Minz_HookType::tryFrom('entry_before_add') !== null) {
				return 'entry_before_add';
			}
		} catch (Throwable $_) {
			// Ignore and fallback to the legacy hook.
		}

		return 'entry_before_insert';
	}

	public function handleConfigureAction(): void {
		$this->registerTranslates();

		if (Minz_Request::isPost()) {
			$this->saveConfiguration();
		}

		$this->loadConfigValues();
	}

	private function saveConfiguration(): void {
		$botToken = trim(Minz_Request::paramString('bot_token'));
		$chatId = trim(Minz_Request::paramString('chat_id'));
		$disableWebPagePreview = Minz_Request::paramString('disable_web_page_preview') === '1';
		$filter = trim(Minz_Request::paramString('filter'));
		$excludeFilter = trim(Minz_Request::paramString('exclude-filter'));
		if ($filter === '') {
			$filter = '*';
		}

		$this->setUserConfiguration([
self::CONF_BOT_TOKEN => $botToken,
			self::CONF_CHAT_ID => $chatId,
			self::CONF_DISABLE_WEB_PAGE_PREVIEW => $disableWebPagePreview ? '1' : '0',
			self::CONF_FILTER => $filter,
			self::CONF_EXCLUDE_FILTER => $excludeFilter,
		]);

		$this->botToken = $botToken;
		$this->chatId = $chatId;
		$this->disableWebPagePreview = $disableWebPagePreview;
		$this->filter = $filter;
		$this->excludeFilter = $excludeFilter;
	}

	public function loadConfigValues(): void {
		$this->botToken = '';
		$this->chatId = '';
		$this->disableWebPagePreview = true;
		$this->filter = '*';
		$this->excludeFilter = '';

		$botToken = $this->getUserConfigurationValue(self::CONF_BOT_TOKEN, '');
		if (is_string($botToken)) {
			$this->botToken = trim($botToken);
		}

		$chatId = $this->getUserConfigurationValue(self::CONF_CHAT_ID, '');
		if (is_string($chatId)) {
			$this->chatId = trim($chatId);
		}

		$disableWebPagePreview = $this->getUserConfigurationValue(self::CONF_DISABLE_WEB_PAGE_PREVIEW, '1');
		if (is_bool($disableWebPagePreview)) {
			$this->disableWebPagePreview = $disableWebPagePreview;
		} elseif (is_int($disableWebPagePreview)) {
			$this->disableWebPagePreview = $disableWebPagePreview === 1;
		} elseif (is_string($disableWebPagePreview)) {
			$this->disableWebPagePreview = in_array(strtolower(trim($disableWebPagePreview)), ['1', 'true', 'yes', 'on'], true);
		}

		$filter = $this->getUserConfigurationValue(self::CONF_FILTER, null);
		if (!is_string($filter)) {
			$filter = $this->getUserConfigurationValue(self::LEGACY_CONF_TARGET, '*');
		}
		if (is_string($filter)) {
			$filter = trim($filter);
			$this->filter = $filter === '' ? '*' : $filter;
		}

		$excludeFilter = $this->getUserConfigurationValue(self::CONF_EXCLUDE_FILTER, '');
		if (is_string($excludeFilter)) {
			$this->excludeFilter = trim($excludeFilter);
		}
	}

	public function getBotToken(): string {
		$this->loadConfigValues();
		return $this->botToken;
	}

	public function getChatId(): string {
		$this->loadConfigValues();
		return $this->chatId;
	}

	public function getDisableWebPagePreview(): bool {
		$this->loadConfigValues();
		return $this->disableWebPagePreview;
	}

	public function getFilter(): string {
		$this->loadConfigValues();
		return $this->filter;
	}

	public function getExcludeFilter(): string {
		$this->loadConfigValues();
		return $this->excludeFilter;
	}

	public function pushEntryToTelegramHook(FreshRSS_Entry $entry): FreshRSS_Entry {
		$this->loadConfigValues();

		if ($this->botToken === '' || $this->chatId === '') {
			return $entry;
		}

		if (!$this->shouldPushEntry($entry)) {
			return $entry;
		}

		$message = $this->buildTelegramMessage($entry);
		if ($message === '') {
			return $entry;
		}

		if (!$this->sendTelegramMessage($message)) {
			$this->warnLog('Unable to push entry to Telegram: ' . $entry->title());
		}

		return $entry;
	}

	private function shouldPushEntry(FreshRSS_Entry $entry): bool {
		if ($this->isEntryRead($entry)) {
			return false;
		}

		$filterRules = $this->parseFeedRules($this->filter);
		$excludeRules = $this->parseFeedRules($this->excludeFilter);
		$matchesAllFilters = empty($filterRules) || in_array('*', $filterRules, true);

		if ($matchesAllFilters && empty($excludeRules)) {
			return true;
		}

		$feed = $entry->feed();
		$feedName = '';
		if ($feed !== null) {
			$feedName = trim($feed->name(true));
		}

		$normalizedFeedName = $feedName === '' ? null : $this->stringToLower($feedName);

		if (!$matchesAllFilters) {
			if ($normalizedFeedName === null || !$this->matchesFeedRules($normalizedFeedName, $filterRules)) {
				return false;
			}
		}

		if (empty($excludeRules)) {
			return true;
		}

		if (in_array('*', $excludeRules, true)) {
			return false;
		}

		return $normalizedFeedName === null || !$this->matchesFeedRules($normalizedFeedName, $excludeRules);
	}

	/**
	 * @return list<string>
	 */
	private function parseFeedRules(string $rules): array {
		return array_values(array_filter(array_map('trim', explode(',', $rules)), static function (string $value): bool {
return $value !== '';
		}));
	}

	/**
	 * @param list<string> $rules
	 */
	private function matchesFeedRules(string $normalizedFeedName, array $rules): bool {
		foreach ($rules as $item) {
			if ($item === '*') {
				return true;
			}

			if ($normalizedFeedName === $this->stringToLower($item)) {
				return true;
			}
		}

		return false;
	}

	private function isEntryRead(FreshRSS_Entry $entry): bool {
		try {
			if (method_exists($entry, 'isRead')) {
				return (bool)$entry->isRead();
			}

			if (method_exists($entry, 'is_read')) {
				return (bool)$entry->is_read();
			}
		} catch (Throwable $_) {
			// Ignore and fallback to unread.
		}

		return false;
	}

	private function buildTelegramMessage(FreshRSS_Entry $entry): string {
		$feedName = '';
		$feed = $entry->feed();
		if ($feed !== null) {
			$feedName = $this->decodeEntryText($feed->name(true));
		}

		$title = $this->decodeEntryText($entry->title());
		if ($title === '') {
			$title = $this->decodeEntryText($entry->guid());
		}

		$link = $this->decodeEntryText($entry->link());
		$content = $this->extractEntryContent($entry);
		$maxLength = 3500;

		$message = $this->renderTelegramMessage($feedName, $title, $content, $link);
		if ($this->stringLength($message) <= $maxLength) {
			return $message;
		}

		if ($content !== '') {
			$content = $this->fitMessageField($content, $maxLength, function (string $candidate) use ($feedName, $title, $link): string {
	return $this->renderTelegramMessage($feedName, $title, $candidate, $link);
			});
			$message = $this->renderTelegramMessage($feedName, $title, $content, $link);
		}

		if ($this->stringLength($message) > $maxLength && $title !== '') {
			$title = $this->fitMessageField($title, $maxLength, function (string $candidate) use ($feedName, $link, $content): string {
	return $this->renderTelegramMessage($feedName, $candidate, $content, $link);
			});
			$message = $this->renderTelegramMessage($feedName, $title, $content, $link);
		}

		if ($this->stringLength($message) > $maxLength && $feedName !== '') {
			$feedName = $this->fitMessageField($feedName, $maxLength, function (string $candidate) use ($title, $content, $link): string {
	return $this->renderTelegramMessage($candidate, $title, $content, $link);
			});
			$message = $this->renderTelegramMessage($feedName, $title, $content, $link);
		}

		if ($this->stringLength($message) > $maxLength && $link !== '') {
			$link = '';
			$message = $this->renderTelegramMessage($feedName, $title, $content, $link);
		}

		return $message;
	}

	private function renderTelegramMessage(string $feedName, string $title, string $content, string $link): string {
		$sections = [];

		if ($feedName !== '') {
			$sections[] = $feedName;
		}

		if ($title !== '') {
			$sections[] = $title;
		}

		if ($content !== '') {
			$sections[] = $content;
		}

		if ($link !== '') {
			$sections[] = $link;
		}

		return implode("\n\n", $sections);
	}

	private function extractEntryContent(FreshRSS_Entry $entry): string {
		foreach (['content', 'summary', 'description'] as $method) {
			if (!method_exists($entry, $method)) {
				continue;
			}

			try {
				$value = $entry->{$method}();
			} catch (Throwable $_) {
				continue;
			}

			if (!is_string($value)) {
				continue;
			}

			$content = $this->sanitizeEntryContent($value);
			if ($content !== '') {
				return $content;
			}
		}

		return '';
	}

	private function sanitizeEntryContent(string $content): string {
		$content = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $content) ?? $content;
		$content = preg_replace('/<\s*li\b[^>]*>/i', '• ', $content) ?? $content;
		$content = preg_replace('/<\s*\/\s*li\s*>/i', "\n", $content) ?? $content;
		$content = preg_replace('/<\s*\/\s*(p|div|section|article|h[1-6]|tr)\s*>/i', "\n", $content) ?? $content;
		$content = strip_tags($content);
		$content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$content = preg_replace("/\r\n?/", "\n", $content) ?? $content;
		$content = preg_replace('/[^\S\n]+/u', ' ', $content) ?? $content;
		$content = preg_replace('/\n[ \t]+/u', "\n", $content) ?? $content;
		$content = preg_replace('/\n{3,}/', "\n\n", $content) ?? $content;

		return trim($content);
	}

	private function decodeEntryText(string $text): string {
		return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	}

	private function stringToLower(string $value): string {
		if (function_exists('mb_strtolower')) {
			return mb_strtolower($value, 'UTF-8');
		}

		return strtolower($value);
	}

	private function stringLength(string $value): int {
		if (function_exists('mb_strlen')) {
			return mb_strlen($value, 'UTF-8');
		}

		if (function_exists('iconv_strlen')) {
			$length = iconv_strlen($value, 'UTF-8');
			if ($length !== false) {
				return $length;
			}
		}

		if (preg_match_all('/./us', $value, $matches) !== false) {
			return count($matches[0]);
		}

		return strlen($value);
	}

	private function stringSubstring(string $value, int $start, int $length): string {
		if ($length <= 0) {
			return '';
		}

		if (function_exists('mb_substr')) {
			return mb_substr($value, $start, $length, 'UTF-8');
		}

		if (function_exists('iconv_substr')) {
			$result = iconv_substr($value, $start, $length, 'UTF-8');
			if ($result !== false) {
				return $result;
			}
		}

		if (preg_match_all('/./us', $value, $matches) !== false) {
			return implode('', array_slice($matches[0], $start, $length));
		}

		return substr($value, $start, $length);
	}

	private function fitMessageField(string $value, int $maxLength, callable $render): string {
		if ($value === '') {
			return '';
		}

		$length = $this->stringLength($value);
		$best = '';
		$low = 0;
		$high = $length;

		while ($low <= $high) {
			$mid = intdiv($low + $high, 2);
			$candidate = $mid >= $length ? $value : $this->truncateText($value, $mid);
			$message = $render($candidate);

			if ($this->stringLength($message) <= $maxLength) {
				$best = $candidate;
				$low = $mid + 1;
			} else {
				$high = $mid - 1;
			}
		}

		return $best;
	}

	private function truncateText(string $text, int $maxLength): string {
		if ($maxLength <= 0) {
			return '';
		}

		if ($this->stringLength($text) <= $maxLength) {
			return $text;
		}

		if ($maxLength === 1) {
			return '…';
		}

		return rtrim($this->stringSubstring($text, 0, $maxLength - 1)) . '…';
	}

	private function sendTelegramMessage(string $message): bool {
		$url = 'https://api.telegram.org/bot' . $this->botToken . '/sendMessage';
		$payload = [
			'chat_id' => $this->chatId,
			'text' => $message,
		];

		if ($this->disableWebPagePreview) {
			$payload['disable_web_page_preview'] = 'true';
		}

		$postData = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);

		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			if ($ch === false) {
				return false;
			}

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 8);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

			$response = curl_exec($ch);
			$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if (!is_string($response) || $httpCode < 200 || $httpCode >= 300) {
				return false;
			}

			return $this->telegramResponseOk($response);
		}

		$context = stream_context_create([
'http' => [
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
				'content' => $postData,
				'timeout' => 8,
				'ignore_errors' => true,
			],
		]);

		$response = @file_get_contents($url, false, $context);
		if (!is_string($response)) {
			return false;
		}

		$statusCode = 0;
		if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches) === 1) {
			$statusCode = (int) $matches[1];
		}
		if ($statusCode < 200 || $statusCode >= 300) {
			return false;
		}

		return $this->telegramResponseOk($response);
	}

	private function telegramResponseOk(string $response): bool {
		$data = json_decode($response, true);
		return is_array($data) && !empty($data['ok']);
	}

	private function warnLog(string $message): void {
		try {
			Minz_Log::warning('[' . $this->getName() . '] ' . $message);
		} catch (Throwable $_) {
			// Ignore logging failures.
		}
	}
}
