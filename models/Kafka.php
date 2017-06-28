<?php

namespace app\models;

class Kafka
{
    //缓存代理，Kafa集群中的一台或多台服务器统
    public $broker_list = 'localhost:9092';
    //消息源（feeds of messages）的不同分类
    public $topic = 'topic';
    /**
     *Topic物理上的分组，一个topic可以分为多个partition，
     *每个partition是一个有序的队列。
     *partition中的每条消息都会被分配一个有序的id（offset）。
     */
    public $partition = 0;
    //记录日志文件
    public $logFile = '@app/runtime/logs/kafka/info.log';
    //消息和数据生产者，向Kafka的一个topic发布消息的过程叫做producers。
    public $producer = NULL;
    // 消息和数据消费者，订阅topics并处理其发布的消息的过程叫做consumers。
    public $consumer = NULL;

    public function __construct ()
    {
        if (empty($this->broker_list)) {
            throw new \yii\base\InvalidConfigException('broker not config');
        }

        $rk = new \RdKafka\Producer();
        if (empty($rk)) {
            throw new \yii\base\InvalidConfigException('product error');
        }
        $rk->setLogLevel(LOG_DEBUG);
        if (!$rk->addBrokers($this->broker_list)) {
            throw new \yii\base\InvalidConfigException('product error');
        }
        $this->producer = $rk;

    }


    /**
     * 发送消息 生产者
     * @param array $messages
     */
    public function send ($messages = [], $tic = 'asynclog')
    {
        // 从producer中创建 topic instance :
        $this->topic = $tic;
        $topic       = $this->producer->newTopic($this->topic);
        //发送消息:
        $topic->produce(RD_KAFKA_PARTITION_UA, $this->partition, json_encode($messages));
    }

    /**
     * 消费者
     * @throws \Exception
     */
    public function consumer ($obj, $callback)
    {

        $conf = new \RdKafka\Conf();

        //配置group.id。所有消费者用同样的group.id将消耗不同的分组
        $conf->set('group.id', 0);
        //初始服务器
        $conf->set('metadata.broker.list', $this->broker_list);


        $topicConf = new \RdKafka\TopicConf();
        //设置在没有初始偏移时开始消耗消息的地方
        // 偏移存储或期望偏移超出范围。
        //'最小'：从一开始
        $topicConf->set('auto.offset.reset', 'smallest');
        //设置用于订阅/分配主题的配置
        $conf->setDefaultTopicConf($topicConf);

        $consumer = new \RdKafka\KafkaConsumer($conf);
        // 订阅主题
        $consumer->subscribe([$this->topic]);

        while (true) {
            sleep(1);
            $message = $consumer->consume(0, 120 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    echo $message->payload . '\n';
                    // \Yii::info($message->payload);
                    $obj->$callback($message->payload);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "\n waiting1...\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    public function consumer1 ()
    {

        $rk = new \RdKafka\Consumer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers($this->broker_list);

        $queue  = $rk->newQueue();
        $topic1 = $rk->newTopic('topic1');
        $topic1->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);
        $topic1->consumeQueueStart(1, RD_KAFKA_OFFSET_BEGINNING, $queue);

        $topic2 = $rk->newTopic("topic2");
        $topic2->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);

        // Now, consume from the queue instead of the topics:

        while (true) {
            $message = $queue->consume(120 * 1000);
            echo $message->payload . "\n";
        }
    }

    public function queue ()
    {
        $rk = new \RdKafka\Consumer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers($this->broker_list);
        $queue  = $rk->newQueue();
        $topic1 = $rk->newTopic("topic1");
        $topic1->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);
        $topic1->consumeQueueStart(1, RD_KAFKA_OFFSET_BEGINNING, $queue);

        $topic2 = $rk->newTopic("topic2");
        $topic2->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);
    }


}