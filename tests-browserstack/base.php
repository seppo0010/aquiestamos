<?php
require_once __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;

abstract class BaseTest extends BrowserStackTest {
	private $db;

	public function setUp() {
		parent::setUp();
		$this->db = mysql_connect('localhost', 'root', '');
		mysql_select_db('testing', $this->db);
	}

	protected function execute($sql) {
		$query = mysql_query($sql, $this->db);
		if (!$query) {
			$this->fail(mysql_error($this->db));
		}
	}

	protected function insertId() {
		return mysql_insert_id($this->db);
	}

	protected function login() {
		self::$driver->get("http://127.0.0.1:8000/wp-login.php");
		self::$driver->findElement(WebDriverBy::id('user_login'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('user_pass'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('wp-submit'))->click();
	}
}