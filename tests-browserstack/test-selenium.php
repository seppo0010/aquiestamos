<?php
require __DIR__ . '/browserstack.php';

use Facebook\WebDriver\WebDriverBy;

class SingleTest extends BrowserStackTest {
    public function testTitle() {
        self::$driver->get("http://127.0.0.1:8000/");
        $this->assertContains('aquiestamostest', self::$driver->getTitle());
    }
}