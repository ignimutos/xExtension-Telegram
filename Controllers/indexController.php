<?php

class FreshExtension_Telegram_Controller extends FreshRSS_index_Controller {
	public function aboutAction() {
		Minz_View::prependTitle(_t('ext.telegram.about.title') . ' · ');
	}
}
