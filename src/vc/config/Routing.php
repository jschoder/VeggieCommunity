<?php

namespace vc\config;

class Routing
{
    public static function getNoLocaleFilter()
    {
        return array(
            '/api/*',
            '/events/picture/*',
            '/forum/thread/picture/*',
            '/groups/picture/*',
            '/mod/real/picture/*',
            '/picture/temp/*',
            '/sitemap.xml',
            '/user/picture/*'
        );
    }

    public static function getNoLocaleRouting()
    {
        return array(
            'api' => array(
                'javascript' => array(
                    'log' => '\vc\controller\api\javascript\LogController'
                )
            ),
            'events' => array(
                'picture' => '\vc\controller\web\PictureController'
            ),
            'forum' => array(
                'thread' => array(
                    'picture' => '\vc\controller\web\PictureController'
                ),
            ),
            'groups' => array(
                'picture' => '\vc\controller\web\PictureController'
            ),
            'mod' => array(
                'real' => array(
                    'picture' => '\vc\controller\web\PictureController'
                )
            ),
            'picture' => array(
                'temp' => '\vc\controller\web\PictureController'
            ),
            'sitemap.xml' => '\vc\controller\web\SitemapController',
            'user' => array(
                'picture' => '\vc\controller\web\PictureController'
            )
        );
    }

    public static function getLocaleRouting()
    {
        return array(
            '404' => '\vc\controller\web\NotFoundController',
            '500' => '\vc\controller\web\InternalServerErrorController',
            'about' => '\vc\controller\web\AboutController',
            'account' => array(
                '#default' => '@redirect::mysite/',
                'activate' => '\vc\controller\web\account\ActivateController',
                'actions' => '\vc\controller\web\account\ActionController',
                'blocked' => '\vc\controller\web\account\BlockedController',
                'changepassword' => '\vc\controller\web\account\ChangePasswordController',
                'confirmterms' => '\vc\controller\web\account\ConfirmTermsController',
                'delete' => '\vc\controller\web\account\DeleteController',
                'privacypolicy' => '\vc\controller\web\account\PrivacyPolicyController',
                'real' => array(
                    '#default' => '\vc\controller\web\account\real\RealController',
                    'create' => '\vc\controller\web\account\real\CreateController',
                    'confirm' => '\vc\controller\web\account\real\ConfirmController',
                ),
                'rememberpassword' => '\vc\controller\web\account\RememberPasswordController',
                'set' => '\vc\controller\web\account\SetController',
                'settings' => '\vc\controller\web\account\SettingsController',
                'signup' => '\vc\controller\web\account\SignupController',
                'status' => '\vc\controller\web\account\StatusController',
                'termsofservice' => '\vc\controller\web\account\TermsOfServiceController'
            ),
            'block' => array(
                'add' => '\vc\controller\web\block\AddController'
            ),
            'chat' => '\vc\controller\web\ChatController',
            'css' => '\vc\controller\web\CssController',
            'debug' => array(
                'on' => '\vc\controller\web\debug\OnController',
                'off' => '\vc\controller\web\debug\OffController'
            ),
            'events' => array(
                '#default' => '\vc\controller\web\event\MyEventsController',
                'add' => array(
                    '#default' => '\vc\controller\web\event\EditController',
                    'group' => '\vc\controller\web\event\EditController',
                ),
                'calendar' => '\vc\controller\web\event\CalendarController',
                'copy' => '\vc\controller\web\event\EditController',
                'delete' => '\vc\controller\web\event\DeleteController',
                'edit' => '\vc\controller\web\event\EditController',
                'invitation' => array(
                    'add' => '\vc\controller\web\event\invitation\AddController'
                ),
                'participate' => '\vc\controller\web\event\ParticipateController',
                'view' => '\vc\controller\web\event\ViewController'
            ),
            'favorite' => array(
                '#default' => '@redirect::favorite/list/',
                'add' => '\vc\controller\web\favorite\AddController',
                'delete' => '\vc\controller\web\favorite\DeleteController',
                'list' => '\vc\controller\web\favorite\ListController'
            ),
            'fb' => array(
                'login' => '\vc\controller\web\fb\LoginController',
                'tokenlogin' => '\vc\controller\web\fb\TokenLoginController'
            ),
            'flag' => array(
                'add' => '\vc\controller\web\flag\AddController',
                'unflag' => '\vc\controller\web\flag\UnflagController',
            ),
            'forum' => array(
                'comment' => array(
                    'add' => '\vc\controller\web\forum\comment\AddController',
                    'edit' => '\vc\controller\web\forum\comment\EditController',
                    'delete' => '\vc\controller\web\forum\comment\DeleteController',
                    'list' => '\vc\controller\web\forum\comment\ListController'
                ),
                'thread' => array(
                    'add' => '\vc\controller\web\forum\thread\AddController',
                    'edit' => '\vc\controller\web\forum\thread\EditController',
                    'delete' => '\vc\controller\web\forum\thread\DeleteController'
                ),
            ),
            'friend' => array(
                '#default' => '@redirect::friend/list/',
                'accept' => '\vc\controller\web\friend\AcceptController',
                'add' => '\vc\controller\web\friend\AddController',
                'delete' => '\vc\controller\web\friend\DeleteController',
                'deny' => '\vc\controller\web\friend\DenyController',
                'inbox' => '@redirect::friend/list/',
                'list' => '\vc\controller\web\friend\ListController'
            ),
            'groups' => array(
                '#default' => '\vc\controller\web\group\MyGroupsController',
                'add' => '\vc\controller\web\group\AddController',
                'delete' => '\vc\controller\web\group\DeleteController',
                'forum' => '\vc\controller\web\group\forum\ForumController',
                'info' => '\vc\controller\web\group\InfoController',
                'invitation' => array(
                    'add' => '\vc\controller\web\group\invitation\AddController',
                    'ignore' => '\vc\controller\web\group\invitation\IgnoreController',
                ),
                'join' => '\vc\controller\web\group\JoinController',
                'leave' => '\vc\controller\web\group\LeaveController',
                'members' => array(
                    'handle' => '\vc\controller\web\group\members\HandleController',
                    'remove' => '\vc\controller\web\group\members\RemoveController',
                    'role' => '\vc\controller\web\group\members\RoleController'
                ),
                'search' => '\vc\controller\web\group\SearchController',
                'settings' => '\vc\controller\web\group\SettingsController'
            ),
            'help' => array(
                '#default' => '@redirect::help/faq/',
                'faq' => '\vc\controller\web\help\FaqController',
                'support' => array(
                    '#default' => '\vc\controller\web\help\SupportController',
                    'history' => '\vc\controller\web\help\HistoryController',
                    'reply' => '\vc\controller\web\help\ReplyController'
                )
            ),
            'imprint' => '@redirect::about/',
            'js' => '\vc\controller\web\JsController',
            'like' => array(
                '#default' => '\vc\controller\web\like\LikeController',
                'list' => '\vc\controller\web\like\ListController'
            ),
            'locked' => '\vc\controller\web\LockedController',
            'login' => '\vc\controller\web\LoginController',
            'logout' => '\vc\controller\web\LogoutController',
            'mod' => array(
                '#default' => '\vc\controller\web\mod\DashboardController',
                'chat' => '\vc\controller\web\mod\ChatController',
                'createactivationtoken' => '\vc\controller\web\mod\CreateActivationTokenController',
                'duplicates' => '\vc\controller\web\mod\DuplicatesController',
                'errors' => '\vc\controller\web\mod\ErrorController',
                'flag' => '\vc\controller\web\mod\FlagController',
                'flushcache' => '\vc\controller\web\mod\FlushCacheController',
                'groups' => '\vc\controller\web\mod\GroupController',
                'messenger' => '\vc\controller\web\mod\MessengerController',
                'metrics' => '\vc\controller\web\mod\MetricsController',
                'pm' => '\vc\controller\web\mod\PmController',
                'pictures' => '\vc\controller\web\mod\PicturesController',
                'real' => '\vc\controller\web\mod\RealController',
                'server' => '\vc\controller\web\mod\ServerController',
                'spam' => '\vc\controller\web\mod\SpamController',
                'suspicions' => '\vc\controller\web\mod\SuspicionsController',
                'switch' => '\vc\controller\web\mod\SwitchUserController',
                'toldafriend' => '\vc\controller\web\mod\ToldAFriendController',
                'tmg' => '\vc\controller\web\mod\TmgController',
                'unsent' => '\vc\controller\web\mod\UnsentController',
                'user' => array(
                    'birthday' => '\vc\controller\web\mod\user\BirthdayController',
                    'block' => '\vc\controller\web\mod\user\BlockController',
                    'comment' => '\vc\controller\web\mod\user\CommentController',
                    'delete' => '\vc\controller\web\mod\user\DeleteController',
                    'deletereasons' => '\vc\controller\web\mod\user\DeleteReasonsController',
                    'giftplus' => '\vc\controller\web\mod\user\GiftPlusController',
                    'watchlist' => '',
                ),
                'tickets' => array(
                    '#default' => '\vc\controller\web\mod\tickets\TicketsController',
                    'reply' => '\vc\controller\web\mod\tickets\ReplyController'
                ),
                'watchlist' => array(
                    'add' => '\vc\controller\web\mod\watchlist\AddController'
                )
            ),
            'mysite' => array(
                '#default' => '\vc\controller\web\mysite\MysiteController',
                'feed' => '\vc\controller\web\mysite\FeedController',
                'hidefeed' => '\vc\controller\web\mysite\HideFeedController',
                'settings' => '@redirect::account/settings/',// Deprecated
                'visitors' => '\vc\controller\web\mysite\VisitorsController'
            ),
            'news' => array(
                'delete' => '\vc\controller\web\news\DeleteController'
            ),
            'notifications' => '\vc\controller\web\NotificationsController',
            'picture' => array(
                'upload' => '\vc\controller\web\picture\UploadController'
            ),
            'plus' => array(
                '#default' => '\vc\controller\web\plus\FeaturesController',
                'book' => '\vc\controller\web\plus\BookController',
                'features' => '@redirect::plus/',
                'history' => '\vc\controller\web\plus\HistoryController',
                'paypal' => array(
                    '#default' => '@redirect::plus/',
                    'checkout' => '\vc\controller\web\plus\paypal\CheckoutController',
                    'confirm' => '\vc\controller\web\plus\paypal\ConfirmController',
                    'plan' => array(
                        'cancel' => '\vc\controller\web\plus\paypal\plan\CancelController',
                        'create' => '\vc\controller\web\plus\paypal\plan\CreateController',
                        'execute' => '\vc\controller\web\plus\paypal\plan\ExecuteController'
                    )
                )
            ),
            'pm' => array(
                '#default' => '\vc\controller\web\pm\PmController',
                'add' => '\vc\controller\web\pm\AddController',
                'delete' => '\vc\controller\web\pm\DeleteController',
                'deleteall' => '\vc\controller\web\pm\DeleteAllController',
                'draft' => array(
                    'add' => '\vc\controller\web\pm\draft\AddController',
                    'delete' => '\vc\controller\web\pm\draft\DeleteController'
                ),
                'flagspam' => '\vc\controller\web\pm\FlagSpamController',
                'messages' => '\vc\controller\web\pm\MessageController',
                'pdfexport' => '\vc\controller\web\pm\PdfExportController',
                'threads' => '\vc\controller\web\pm\ThreadController',

                'history' => '@redirect::pm/',
                'inbox' => '@redirect::pm/',
                'outbox' => '@redirect::pm/',
                'trash' => '@redirect::pm/',
                'view' => '@redirect::pm/'
            ),
            'poll' => array(
                '#default' => '@redirect::mysite/',
                'view' => '\vc\controller\web\poll\ViewController',
                'vote' => '\vc\controller\web\poll\VoteController'
            ),
//            'press' => '\vc\controller\web\PressController',
            'start' => '\vc\controller\web\StartController',
            'subscription' => array(
                'add' => '\vc\controller\web\subscription\AddController',
                'delete' => '\vc\controller\web\subscription\DeleteController'
            ),
            'tellafriend' => '\vc\controller\web\TellAFriendController',
            'updates' => '\vc\controller\web\UpdatesController',
            'undo' => array(
                'block' => '\vc\controller\web\undo\BlockController'
            ),
            'user' => array(
                '#default' => '@redirect::user/search/',
                'edit' => '\vc\controller\web\user\EditController',
                'list' => '\vc\controller\web\user\ListController',
                'matching' => '\vc\controller\web\user\MatchingController',
                'pictures' => array(
                    '#default' => '\vc\controller\web\user\pictures\PicturesController',
                    'simple' => '\vc\controller\web\user\pictures\SimplePicturesController'
                ),
                'result' => array(
                    '#default' => '\vc\controller\web\user\result\ResultController',
                    'save' => '\vc\controller\web\user\result\SaveController',
                    'delete' => '\vc\controller\web\user\result\DeleteController'
                ),
                'search' => array(
                    '#default' => '\vc\controller\web\user\SearchController',
                    'saved' => '\vc\controller\web\user\search\SavedController',
                ),
                'share' => '\vc\controller\web\ShareController',
                'view' => '\vc\controller\web\user\ViewController'
            ),
            // Deprecated urls
            'activity' => array(
                '#default' => '@redirect::mysite/'
            ),
            'edit' => '@redirect::user/edit/',
            'feedback' => '@redirect::help/support/',
            'mailbox' => '@redirect::pm/',
            'payment' => '@redirect::plus/',
            'people' => '@redirect::user/search/',
            'passwordlost' => '@redirect::account/rememberpassword/',
            'press' => '@redirect::about/',
            'privacypolicy' => '@redirect::account/privacypolicy/',
            'profile' => '@redirect::user/view/{0}/',
            'register' => '@redirect::account/signup/',
            'result' => '@redirect::user/result/',
            'search' => '@redirect::user/search/',
            'tags' => '@redirect::user/search/',
            'termsofuse' => '@redirect::account/termsofservice/',
            'unlock' => '@redirect::account/activate/{0}/{1}/'
        );
    }
}
