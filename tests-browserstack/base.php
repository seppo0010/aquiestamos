<?php
require_once __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;

abstract class BaseTest extends BrowserStackTest {
	protected function login() {
		self::$driver->get("http://127.0.0.1:8000/wp-login.php");
		self::$driver->findElement(WebDriverBy::id('user_login'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('user_pass'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('wp-submit'))->click();
	}
}