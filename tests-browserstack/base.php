<?php
require_once __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;

abstract class BaseTest extends BrowserStackTest {
	private $db;
	private $options;

	public function setUp() {
		parent::setUp();

		self::$driver->manage()->deleteAllCookies();

		$this->db = mysql_connect('localhost', 'root', '');
		mysql_select_db('testing', $this->db);
		$this->execute('TRUNCATE wp_ae_checkin');

		$this->options = array();
	}

	public function tearDown() {
		parent::tearDown();
		foreach($this->options as $option => $value) {
			$this->setOption($option, $value);
		}
	}

	protected function escape($term) {
		return mysql_real_escape_string($term, $this->db);
	}

	protected function row($query) {
		return mysql_fetch_object($query);
	}

	protected function execute($sql) {
		$query = mysql_query($sql, $this->db);
		if (!$query) {
			$this->fail(mysql_error($this->db));
		}
		return $query;
	}

	protected function insertId() {
		return mysql_insert_id($this->db);
	}

	protected function login() {
		self::$driver->get("http://127.0.0.1:8000/wp-login.php");
		usleep(1000); // sometimes the first characters are missing... is there a better way to wait?
		self::$driver->findElement(WebDriverBy::id('user_login'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('user_pass'))->sendKeys('aquiestamos');
		self::$driver->findElement(WebDriverBy::id('wp-submit'))->click();
	}

	protected function setOption($option, $newValue) {
		$where = 'WHERE option_name = \'' . $this->escape($option) . '\'';
		$query = $this->execute('SELECT option_value FROM wp_options ' . $where);
		$oldValue = $this->row($query)->option_value;
		$this->assertNotNull($oldValue);
		$this->options[$option] = $oldValue;
		$this->execute('UPDATE wp_options SET option_value = \'' . $this->escape($newValue) . '\' ' . $where);
	}

	protected function numberOfPins() {
		return count(self::$driver->findElements(WebDriverBy::cssSelector('#ae_map .gmnoprint')));
	}

	protected function shouldAddPins($action, $howMany = 1) {
		$pinsBefore = $this->numberOfPins();

		$action();

		self::$driver->wait()->until(function() use ($pinsBefore, $howMany) {
			$pinsAfter = $this->numberOfPins();;
			return $pinsBefore + $howMany === $pinsAfter;
		});
	}
}