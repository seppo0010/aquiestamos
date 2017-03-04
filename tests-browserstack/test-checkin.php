<?php
require __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;

class SingleTest extends BrowserStackTest {
	public function setUp() {
		parent::setUp();
		self::$driver->manage()->deleteAllCookies();
	}

	public function testCheckinPromptsLogin() {
		self::$driver->get("http://127.0.0.1:8000/");
		$loginDialog = self::$driver->findElement(WebDriverBy::id('ae_login'));
		$this->assertFalse($loginDialog->isDisplayed());
		self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'))->click();
		$this->assertTrue($loginDialog->isDisplayed());

		// trying to check in without being logged in sets a cookie
		// so upon login the user gets automatically checked in
		$cookie = self::$driver->manage()->getCookieNamed('ae_checkin_location');
		$this->assertNotNull($cookie);
		$cookieData = json_decode(rawurldecode($cookie['value']));
		$this->assertNotNull($cookieData);
		$this->assertNotNull($cookieData->lat);
		$this->assertNotNull($cookieData->lng);
		$this->assertNotNull($cookieData->zoom);
	}
}