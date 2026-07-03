<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Producer\Producer;

class PublishUserEventToKafka implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $eventType;
    protected $eventData;

    public function __construct($userId, $eventType, $eventData)
    {
        $this->userId = $userId;
        $this->eventType = $eventType;
        $this->eventData = $eventData;
    }

    public function handle(): void
    {
        try {
            $config = new ProducerConfig();
            $config->setBootstrapServer(env('KAFKA_BROKER', 'localhost:9092'));
            $config->setUpdateIntervalMs(1000);

            $producer = new Producer($config);

            $payload = json_encode([
                'user_id' => $this->userId,
                'event_type' => $this->eventType,
                'data' => $this->eventData,
                'timestamp' => now()->toIso8601String(),
            ]);

            $producer->send('user-events', null, $payload);
        } catch (\Exception $e) {
            \Log::error('Kafka publish failed: ' . $e->getMessage());
            $this->release(60); // Retry after 60 seconds
        }
    }
}
