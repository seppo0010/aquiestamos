<?php
require_once __DIR__ . '/base.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MapTest extends BaseTest {
	private function loadMap() {
		self::$driver->get("http://127.0.0.1:8000/");
		self::$driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ae_map .gm-style')));
		self::$driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ae_map div[title="Zoom out"]')));
		$zoomOut = self::$driver->findElement(WebDriverBy::cssSelector('#ae_map div[title="Zoom out"]'));
		for ($i = 0; $i < 20; $i++) {
			$zoomOut->click();
		}
	}

	public function testCheckinAddsPin() {
		$this->login();
		$this->loadMap();

		$this->shouldAddPins(function() {
			$button = self::$driver->findElement(WebDriverBy::cssSelector('[data-checkin]'));
			self::$driver->wait()->until(WebDriverExpectedCondition::visibilityOf($button));
			$button->click();
		});
	}

	public function testPollingAddsPin() {
		$this->loadMap();

		$this->shouldAddPins(function() {
			$this->execute('INSERT INTO wp_posts (to_ping, pinged, post_date, post_date_gmt, post_modified, post_modified_gmt, post_content, post_title, post_excerpt, post_content_filtered) VALUES (\'\', \'\', NOW(), NOW(), NOW(), NOW(), \'\', \'\', \'\', \'\')');
			$insertId = $this->insertId();
			$this->execute('INSERT INTO wp_ae_checkin (post_id, latitude, longitude) VALUES (' . $insertId . ', 12.34, -56.78)');
		});
	}
}