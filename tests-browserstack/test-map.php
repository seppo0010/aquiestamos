<?php
require_once __DIR__ . '/base.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MapTest extends BaseTest {
	public function setUp() {
		parent::setUp();
		self::$driver->manage()->deleteAllCookies();
	}

	public function testCheckinAddsPin() {
		$this->login();

		self::$driver->get("http://127.0.0.1:8000/");
		self::$driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ae_map .gm-style')));
		$pinsBefore = count(self::$driver->findElements(WebDriverBy::cssSelector('#ae_map .gmnoprint')));

		$loginDialog = self::$driver->findElement(WebDriverBy::id('ae_login'));
		self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'))->click();
		self::$driver->wait()->until(function() use ($pinsBefore) {
			$pinsAfter = count(self::$driver->findElements(WebDriverBy::cssSelector('#ae_map .gmnoprint')));
			return $pinsBefore + 1 === $pinsAfter;
		});
	}
}