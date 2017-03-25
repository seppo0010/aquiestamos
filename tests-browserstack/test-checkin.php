<?php
require_once __DIR__ . '/base.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class CheckinTest extends BaseTest {
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
		$this->login();

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

	public function testCloseCheckinDialog() {
		$this->setOption('ae_checkin_text', '<a href="#" data-ae-close>Close</a>');
		self::$driver->get("http://127.0.0.1:8000/");
		$checkinDialog = self::$driver->findElement(WebDriverBy::id('ae_checkin'));
		$this->assertTrue($checkinDialog->isDisplayed());
		self::$driver->findElement(WebDriverBy::cssSelector('[data-ae-close]'))->click();
		$this->assertFalse($checkinDialog->isDisplayed());
	}

	public function testCloseLoginDialog() {
		$this->setOption('ae_login_text', '<a href="#" data-ae-close>Close</a>');
		self::$driver->get("http://127.0.0.1:8000/");
		self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'))->click();
		$loginDialog = self::$driver->findElement(WebDriverBy::id('ae_login'));
		$this->assertTrue($loginDialog->isDisplayed());
		self::$driver->findElement(WebDriverBy::cssSelector('[data-ae-close]'))->click();
		$this->assertFalse($loginDialog->isDisplayed());
	}

	public function testCloseThanksDialog() {
		$this->setOption('ae_thanks_text', '<a href="#" data-ae-close>Close</a>');
		$this->login();
		self::$driver->get("http://127.0.0.1:8000/");
		self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'))->click();
		$thanksDialog = self::$driver->findElement(WebDriverBy::id('ae_thanks'));
		$this->assertTrue($thanksDialog->isDisplayed());
		self::$driver->findElement(WebDriverBy::cssSelector('[data-ae-close]'))->click();
		$this->assertFalse($thanksDialog->isDisplayed());
	}
}