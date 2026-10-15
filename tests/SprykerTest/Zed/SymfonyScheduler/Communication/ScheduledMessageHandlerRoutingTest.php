<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\Message\SyncCommandMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;

/**
 * Locks the routing invariant the two execution tiers depend on: a scheduled message must resolve to
 * exactly ONE handler. Symfony resolves a message's parents and interfaces alongside its own class, so
 * making the two message types related, or keying the map on their shared interface, would route a
 * synchronous job to both handlers and run it twice - once in the polling loop, once from the queue.
 *
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group ScheduledMessageHandlerRoutingTest
 * Add your own group annotations below this line
 */
class ScheduledMessageHandlerRoutingTest extends Unit
{
    /**
     * @var string
     */
    protected const HANDLER_ASYNC = 'enqueueing';

    /**
     * @var string
     */
    protected const HANDLER_SYNC = 'executing';

    /**
     * @return array<string, array<string>>
     */
    public function messageClassDataProvider(): array
    {
        return [
            'asynchronous job is only enqueued' => [CommandMessage::class, static::HANDLER_ASYNC],
            'synchronous job is only executed' => [SyncCommandMessage::class, static::HANDLER_SYNC],
        ];
    }

    /**
     * @dataProvider messageClassDataProvider
     *
     * @param string $messageClass
     * @param string $expectedHandler
     *
     * @return void
     */
    public function testMessageResolvesToExactlyOneHandler(string $messageClass, string $expectedHandler): void
    {
        // Arrange
        $handlersLocator = new HandlersLocator([
            CommandMessage::class => [$this->createNamedHandler(static::HANDLER_ASYNC)],
            SyncCommandMessage::class => [$this->createNamedHandler(static::HANDLER_SYNC)],
        ]);

        /** @var \Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface $message */
        $message = new $messageClass();
        $message->setCommand('true')->setName('test-job');

        // Act
        $handlers = iterator_to_array($handlersLocator->getHandlers(new Envelope($message)), false);

        // Assert
        $this->assertCount(1, $handlers);
        $this->assertSame($expectedHandler, $handlers[0]->getHandler()(new CommandMessage()));
    }

    public function testSynchronousMessageIsNotRelatedToAsynchronousMessage(): void
    {
        // Assert
        $this->assertNotInstanceOf(CommandMessage::class, new SyncCommandMessage());
        $this->assertNotInstanceOf(SyncCommandMessage::class, new CommandMessage());
        $this->assertInstanceOf(CommandMessageInterface::class, new SyncCommandMessage());
        $this->assertInstanceOf(CommandMessageInterface::class, new CommandMessage());
    }

    protected function createNamedHandler(string $name): callable
    {
        return function (CommandMessageInterface $commandMessage) use ($name): string {
            return $name;
        };
    }
}
