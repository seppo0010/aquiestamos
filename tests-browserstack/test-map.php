<?php
require_once __DIR__ . '/base.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MapTest extends BaseTest {
	public function setUp() {
		parent::setUp();
		self::$driver->manage()->deleteAllCookies();
		$this->execute('TRUNCATE wp_ae_checkin');
	}

	private function loadMap() {
		self::$driver->get("http://127.0.0.1:8000/");
		self::$driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ae_map .gm-style')));
		$zoomOut = self::$driver->findElement(WebDriverBy::cssSelector('#ae_map div[title="Zoom out"]'));
		for ($i = 0; $i < 20; $i++) {
			$zoomOut->click();
		}
	}

	private function numberOfPins() {
		return count(self::$driver->findElements(WebDriverBy::cssSelector('#ae_map .gmnoprint')));
	}

	public function testCheckinAddsPin() {
		$this->login();
		$this->loadMap();

		$pinsBefore = $this->numberOfPins();

		$loginDialog = self::$driver->findElement(WebDriverBy::id('ae_login'));
		self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'))->click();
		self::$driver->wait()->until(function() use ($pinsBefore) {
			$pinsAfter = $this->numberOfPins();;
			return $pinsBefore + 1 === $pinsAfter;
		});
	}

	public function testPollingAddsPin() {
		$this->loadMap();
		$pinsBefore = $this->numberOfPins();

		$this->execute('INSERT INTO wp_posts (to_ping, pinged, post_date, post_date_gmt, post_modified, post_modified_gmt, post_content, post_title, post_excerpt, post_content_filtered) VALUES (\'\', \'\', NOW(), NOW(), NOW(), NOW(), \'\', \'\', \'\', \'\')');
		$insertId = $this->insertId();
		$this->execute('INSERT INTO wp_ae_checkin (post_id, latitude, longitude) VALUES (' . $insertId . ', 12.34, -56.78)');
		self::$driver->wait()->until(function() use ($pinsBefore) {
			$pinsAfter = $this->numberOfPins();
			return $pinsBefore + 1 === $pinsAfter;
		});
	}
}