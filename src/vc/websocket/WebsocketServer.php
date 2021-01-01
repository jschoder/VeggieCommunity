<?php
namespace vc\websocket;

class WebsocketServer extends AbstractWebsocketServer
{
    private $timeForRestart;
    private $shell;
    private $listeners = array();

    private $logLevel = \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG;

    public function __construct($shell)
    {
        $this->timeForRestart = time() + 86340; // 24 hours - 1 minute
        $this->shell = $shell;
        parent::__construct(
            '0.0.0.0',
            \vc\config\Globals::$websocket[$shell->getServer()]['port'],
            true,
            realpath('../data/pem/' . $shell->getServer() . '.pem'),
            null,
            $shell->getServer() === 'local' // allow self-signed certificates - remove this in production!
        );
    }

    protected function process($user, $message)
    {
        // Cancel the call if the ip is unavailable.
        if (empty($user->ip)) {
            $this->log(
                \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                'Blocking call since ip is missing'
            );
            return;
        }

        $messageJson = json_decode($message);
        if (!empty($messageJson) &&
            isset($messageJson->contextType)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionLevel = $suspicionModel->getSuspicionLevel(
                0,
                $user->ip,
                time() - \vc\config\Globals::SUSPICION_PAST
            );

            if ($suspicionLevel >= \vc\config\Globals::SUSPICION_BLOCK_LEVEL) {
                $this->log(
                    \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                    'Blocking call for ip  ' . $user->ip
                );
                return;
            }

            switch ($messageJson->contextType) {
                case \vc\config\EntityTypes::CHAT:
                    $contextId = $this->getChatContextId($messageJson, $user);
                    break;
                case \vc\config\EntityTypes::GROUP_FORUM:
                    $contextId = $this->getGroupForumContextId($messageJson, $user);
                    break;
                case \vc\config\EntityTypes::EVENT:
                    $contextId = $this->getEventContextId($messageJson, $user);
                    break;
                case \vc\config\EntityTypes::PM:
                case \vc\config\EntityTypes::STATUS:
                    $contextId = $this->getStatusPMContextId($messageJson, $user);
            }
            if ($contextId === null) {
                return;
            }

            if (isset($messageJson->action) && $messageJson->action == 'remove') {
                if (!empty($this->listeners[$messageJson->contextType][$contextId])) {
                    $arrayKey = array_search(
                        $user,
                        $this->listeners[$messageJson->contextType][$contextId],
                        true
                    );
                    if ($arrayKey !== false) {
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
                            'Removing ' . $messageJson->contextType . ' / ' . $contextId . ' / ' . $user->id
                        );
                        unset($this->listeners[$messageJson->contextType][$contextId][$arrayKey]);
                    }
                }
            } else {
                $this->log(
                    \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
                    'Adding ' . $messageJson->contextType . ' / ' . $contextId . ' / ' . $user->id
                );
                if (!array_key_exists($messageJson->contextType, $this->listeners)) {
                    $this->listeners[$messageJson->contextType] = array();
                }
                if (!array_key_exists($contextId, $this->listeners[$messageJson->contextType])) {
                    $this->listeners[$messageJson->contextType][$contextId] = array();
                }
                $this->listeners[$messageJson->contextType][$contextId][] = $user;
            }
        } else {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_WEBSOCKET_REQUEST,
                $user->ip,
                array(
                    'json' => $message
                )
            );
        }
    }

    private function getChatContextId($messageJson, $user)
    {
        // :TODO: secondary - create key for chat users
        if (!isset($messageJson->contextId) ||
            $messageJson->contextId === null) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_WEBSOCKET_REQUEST,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        return intval($messageJson->contextId);
    }

    private function getGroupForumContextId($messageJson, $user)
    {
        if (empty($messageJson->contextId)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_WEBSOCKET_REQUEST,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        $groupForumModel = $this->shell->getDbModel('GroupForum');
        $groupForumObject = $groupForumModel->loadObject(array('hash_id' => $messageJson->contextId));
        if ($groupForumObject === null) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        $websocketUserModel = $this->shell->getDbModel('WebsocketUser');
        $userId = $websocketUserModel->getField('user_id', 'websocket_key', $messageJson->key);
        $groupMemberModel = $this->shell->getDbModel('GroupMember');
        $isMember = $groupMemberModel->isMember($groupForumObject->groupId, $userId);
        if ($isMember === null || $isMember === false) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONMEMBER,
                $user->ip,
                array(
                    'forumId' => $groupForumObject->id,
                    'groupId' => $groupForumObject->groupId,
                    'userId' => $userId,
                    'json' => $messageJson
                )
            );
            return null;
        }
        return $groupForumObject->id;
    }

    private function getEventContextId($messageJson, $user)
    {
        if (empty($messageJson->contextId)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_WEBSOCKET_REQUEST,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        $eventModel = $this->shell->getDbModel('Event');
        $contextId = $eventModel->getField('id', 'hash_id', $messageJson->contextId);
        if (empty($contextId)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_EVENT,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        $websocketUserModel = $this->shell->getDbModel('WebsocketUser');
        $userId = $websocketUserModel->getField('user_id', 'websocket_key', $messageJson->key);
        if (!$eventModel->canSeeEvent($userId, $contextId)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                $user->ip,
                array(
                    'eventId' => $contextId,
                    'userId' => $userId,
                    'json' => $messageJson
                )
            );
            return null;
        }

        return $contextId;
    }

    private function getStatusPMContextId($messageJson, $user)
    {
        if (empty($messageJson->key)) {
            $suspicionModel = $this->shell->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $user->id,
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_WEBSOCKET_REQUEST,
                $user->ip,
                array(
                    'json' => $messageJson
                )
            );
            return null;
        }
        $websocketUserModel = $this->shell->getDbModel('WebsocketUser');
        $contextId = $websocketUserModel->getField('user_id', 'websocket_key', $messageJson->key);
        return $contextId;
    }

    protected function connected($user)
    {
        $this->send($user, json_encode('Hello my friend.'));
    }

    protected function closed($user)
    {
        $this->log(
            \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
            'Closing ' . $user->id
        );
        foreach ($this->listeners as $contextType => $contextIdChildren) {
            foreach ($contextIdChildren as $contextId => $userChildren) {
                foreach ($userChildren as $i => $child) {
                    if ($child == $user) {
                        unset($this->listeners[$contextType][$contextId][$i]);
                    }
                }

                // Clean empty array
                if (empty($this->listeners[$contextType][$contextId])) {
                    unset($this->listeners[$contextType][$contextId]);
                }
            }

            // Clean empty array
            if (empty($this->listeners[$contextType])) {
                unset($this->listeners[$contextType]);
            }
        }
    }

    protected function tick()
    {
        parent::tick();
        if (time() >= $this->timeForRestart) {
            $this->log(
                \vc\object\WebsocketServerLog::LOG_LEVEL_INFO,
                'Time for restart reached. Shutting down Websocket Server.'
            );
            $this->stop();
            return;
        }

        $messageModel = $this->shell->getDbModel('WebsocketMessage');
        $list = $messageModel->getList();
        foreach ($list as $context) {
            $contextType = $context[0];
            $contextId = $context[1];

            if (array_key_exists($contextType, $this->listeners) &&
                array_key_exists($contextId, $this->listeners[$contextType])) {
                $this->log(
                    \vc\object\WebsocketServerLog::LOG_LEVEL_INFO,
                    'Send message to ' . $contextType . ' / ' . $contextId . ' (' .
                    count($this->listeners[$contextType][$contextId]) . ')'
                );
                switch ($contextType) {
                    case \vc\config\EntityTypes::CHAT:
                        $contextJsonId = $contextId;
                        break;

                    case \vc\config\EntityTypes::GROUP_FORUM:
                        $groupForumModel = $this->shell->getDbModel('GroupForum');
                        $contextJsonId = $groupForumModel->getField('hash_id', 'id', $contextId);
                        break;

                    case \vc\config\EntityTypes::EVENT:
                        $eventModel = $this->shell->getDbModel('Event');
                        $contextJsonId = $eventModel->getField('id', 'hash_id', $contextId);
                        break;

                    case \vc\config\EntityTypes::PM:
                    case \vc\config\EntityTypes::STATUS:
                        $contextJsonId = null;
                        break;
                }

                foreach ($this->listeners[$contextType][$contextId] as $user) {
                    $message = array($contextType, $contextJsonId);
                    $this->send($user, json_encode($message));
                }
            } else {
                $this->log(
                    \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
                    'No listener for ' . $contextType . ' / ' . $contextId
                );
            }

            // Delete mesage since it is done now
            $messageModel->delete(
                array(
                    'context_type' => intval($contextType),
                    'context_id' => intval($contextId)
                )
            );
        }
    }

    protected function log($type, $message)
    {
        if ($type >= $this->logLevel) {
            switch ($type) {
                case \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG:
                    echo 'DEBUG [' . date('Y-m-d H:i:s') . '] '  . $message . "\n";
                    break;
                case \vc\object\WebsocketServerLog::LOG_LEVEL_INFO:
                    echo 'INFO [' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
                    break;
                case \vc\object\WebsocketServerLog::LOG_LEVEL_WARN:
                    echo 'WARN [' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
                    break;
                case \vc\object\WebsocketServerLog::LOG_LEVEL_ERROR:
                    echo 'ERROR [' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
                    break;
                default:
                    echo $message . "\n";
            }
        }
    }
}
