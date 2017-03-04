<?php
require __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

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

	public function testLoginWithCookieChecksIn() {
		self::$driver->get("http://127.0.0.1:8000/wp-login.php");
		self::$driver->findElement(WebDriverBy::id('user_login'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('user_pass'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('wp-submit'))->click();

		self::$driver->manage()->addCookie([
			'name' => 'ae_checkin_location',
			'value' => rawurlencode(json_encode([
				'lat' => 12.345,
				'lng' => -43.21,
				'zoom' => 10,
			])),
		]);

		self::$driver->get("http://127.0.0.1:8000/");
		$thanks = self::$driver->findElement(WebDriverBy::id('ae_thanks'));
		self::$driver->wait()->until(WebDriverExpectedCondition::visibilityOf($thanks));
		$this->assertNull(self::$driver->manage()->getCookieNamed('ae_checkin_location'));
	}
}