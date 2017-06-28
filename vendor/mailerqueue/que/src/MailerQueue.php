<?php

namespace mailerqueue\que;

use Yii;


class MailerQueue extends \yii\swiftmailer\Mailer
{
    public $messageClass = "mailerqueue\que\Message";

    public $key = 'mails';

    public $db = '1';

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function process ()
    {
        $redis = Yii::$app->redis;
        if (empty($redis)) {
            throw new \yii\base\InvalidConfigException('redis not found in config.');
        }
        if ($redis->select($this->db) && $messages = $redis->lrange($this->key, 0, -1)) {
            $messageObj = new Message;
            foreach ($messages as $message) {
                $message = json_decode($message, true);
                //发送失败
                if (empty($message) || !$this->setMessage($messageObj, $message)) {
                    throw new \ServerErrorHttpException('message error');
                }
                //发送成功 ，删除 队列里面的邮件
                if ($messageObj->send()) {
                    $redis->lrem($this->key, -1, json_encode($message));
                }
            }
        }

        return true;
    }

    /**
     * 发送邮件
     * @param $messageObj
     * @param $message
     * @return bool
     */
    public function setMessage ($messageObj, $message)
    {
        if (empty($messageObj)) {
            return false;
        }
        if (!empty($message['from']) && !empty($message['to'])) {
            $messageObj->setFrom($message['from'])->setTo($message['to']);
            if (!empty($message['cc'])) {
                $messageObj->setCc($message['cc']);
            }
            if (!empty($message['bcc'])) {
                $messageObj->setBcc($message['bcc']);
            }
            if (!empty($message['reply_to'])) {
                $messageObj->setReplyTo($message['reply_to']);
            }
            if (!empty($message['charset'])) {
                $messageObj->setCharset($message['charset']);
            }
            if (!empty($message['subject'])) {
                $messageObj->setSubject($message['subject']);
            }
            if (!empty($message['html_body'])) {
                $messageObj->setHtmlBody($message['html_body']);
            }
            if (!empty($message['text_body'])) {
                $messageObj->setTextBody($message['text_body']);
            }

            return $messageObj;
        }

        return false;
    }
}