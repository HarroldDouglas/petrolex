<?php

namespace Tests;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Configuration Firefox Driver
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new FirefoxOptions)
            ->addArguments([
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1920,1080',
                '--disable-gpu',
                '--disable-web-security',
            ]);

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://127.0.0.1:4444',
            $capabilities
        );
    }

    /**
     * Nettoyer après chaque test
     */
    protected function tearDown(): void
    {
        static::closeAll();
        parent::tearDown();
    }
}
