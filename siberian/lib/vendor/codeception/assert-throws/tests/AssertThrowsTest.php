<?php

declare(strict_types=1);

use Codeception\AssertThrows;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class AssertThrowsTest extends TestCase
{
    use AssertThrows;

    public function testBasicException()
    {
        $count = Assert::getCount();
        $this->assertThrows(MyException::class, function() {
            throw new MyException();
        });
        $this->assertThrows(MyException::class, function() {
            throw new MyException('with ignored message');
        });
        $this->assertTrue(true);
        $this->assertEquals($count + 3, Assert::getCount());
    }

    public function testExceptionWithMessage()
    {
        $this->assertThrowsWithMessage(MyException::class, 'hello', function() {
            throw new MyException('hello');
        });
    }

    public function testExceptionMessageFails()
    {
        try {
            $this->assertThrowsWithMessage(MyException::class, 'hello', function() {
                throw new MyException('hallo');
            });
        } catch (AssertionFailedError $error) {
            $this->assertEquals(
                "Exception message 'hello' was expected, but 'hallo' was received",
                $error->getMessage()
            );
            return;
        }

        $this->fail('Ups :(');
    }

    public function testExceptionMessageCaseInsensitive()
    {
        $this->assertThrowsWithMessage(
            MyException::class,
            'Message and Expected Message CAN have different case',
            function() {
                throw new MyException('Message and expected message can have different case');
            }
        );
    }

    public function testAssertThrowsWithParams()
    {
        $func = function (string $foo, string $bar): void {
            throw new MyException($foo.$bar);
        };

        $this->assertThrows(
            MyException::class,
            $func,
            'foo',
            'bar'
        );
    }

    public function testAssertThrowsWithMessageWithParams()
    {
        $func = function (string $foo, string $bar): void {
            throw new Exception($foo.$bar);
        };

        $this->assertThrowsWithMessage(
            Exception::class,
            'foobar',
            $func,
            'foo',
            'bar'
        );
    }

    public function testAssertDoesNotThrow()
    {
        $func = function (): void {
            throw new Exception('foo');
        };

        $this->assertDoesNotThrow(RuntimeException::class, $func);
        $this->assertDoesNotThrowWithMessage(RuntimeException::class, 'bar', $func);
        $this->assertDoesNotThrowWithMessage(RuntimeException::class, 'foo', $func);
        $this->assertDoesNotThrow(new RuntimeException(), $func);
        $this->assertDoesNotThrow(new RuntimeException('bar'), $func);
        $this->assertDoesNotThrow(new RuntimeException('foo'), $func);
        $this->assertDoesNotThrowWithMessage(Exception::class, 'bar', $func);
        $this->assertDoesNotThrow(new Exception('bar'), $func);
    }

    public function testAssertDoesNotThrowWithParams()
    {
        $func = function (string $foo, string $bar): void {
            throw new Exception($foo.$bar);
        };

        $this->assertDoesNotThrowWithMessage(Exception::class, 'bar', $func, 'bar');
        $this->assertDoesNotThrowWithMessage(Exception::class, 'foobar', $func, 'bar', 'foo');
        $this->assertDoesNotThrow(RuntimeException::class, $func, 'bar', 'foo');
    }
}

final class MyException extends Exception {

}
