<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Client::class,
            new Client("testidentity", "secret123456")
        );
    }

    public function testCanCreateAChooseUrl(): void
    {
        $client = new Client("testidentity", "secret123456");

        $url = $client->createChooseUrl(
            ["red", "green", "blue"],
            "test-tournament",
            300,
            6000,
            "https://www.example.com/image-",
            ".jpg"
        );

        $this->assertNotEquals($url, "");
        $this->assertStringStartsWith("https://", $url);
    }
}
