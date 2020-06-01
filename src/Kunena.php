<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text as JText;
use \JLoader;

class Kunena extends Base
{
    public function __construct()
    {
        parent::__construct();

        if (defined('KUNENA_LOADED')) {
            return;
        }

        // Manually enable code profiling by setting value to 1
        define('KUNENA_PROFILER', 0);

        // Component name amd database prefix
        define('KUNENA_COMPONENT_NAME', 'com_kunena');
        define('KUNENA_COMPONENT_LOCATION', 'components');
        define('KUNENA_NAME', substr(KUNENA_COMPONENT_NAME, 4));

        // Component paths
        define('KPATH_COMPONENT_RELATIVE', KUNENA_COMPONENT_LOCATION . '/' . KUNENA_COMPONENT_NAME);
        define('KPATH_SITE', JPATH_ROOT . '/' . KPATH_COMPONENT_RELATIVE);
        define('KPATH_ADMIN', JPATH_ADMINISTRATOR . '/' . KPATH_COMPONENT_RELATIVE);
        define('KPATH_MEDIA', JPATH_ROOT . '/media/' . KUNENA_NAME);

        // URLs
        define('KURL_COMPONENT', 'index.php?option=' . KUNENA_COMPONENT_NAME);
        // define('KURL_SITE', \Joomla\CMS\Uri\Uri::Root() . KPATH_COMPONENT_RELATIVE . '/');
        // define('KURL_MEDIA', \Joomla\CMS\Uri\Uri::Root() . 'media/' . KUNENA_NAME . '/');


        // Define Kunena framework path.
        define('KPATH_FRAMEWORK', JPATH_PLATFORM . '/kunena/');

        // Register the Joomla compatibility layer.
        JLoader::registerPrefix('KunenaCompat', KPATH_FRAMEWORK . '/compat/joomla');

        // Register the library base path for Kunena Framework.
        JLoader::registerPrefix('Kunena', KPATH_FRAMEWORK);

        // Give access to all Kunena tables.
        \Joomla\CMS\Table\Table::addIncludePath(KPATH_FRAMEWORK . '/tables');

        // Give access to all Kunena JHtml functions.
        // Joomla\CMS\HTML\HTMLHelper::addIncludePath(KPATH_FRAMEWORK . '/html/html');

        // Give access to all Kunena form fields.
        // \Joomla\CMS\Form\Form::addFieldPath(KPATH_FRAMEWORK . '/form/fields');

        // Register classes where the names have been changed to fit the autoloader rules.
            // JLoader::register('KunenaAccess', KPATH_FRAMEWORK . '/access.php');
            // JLoader::register('KunenaConfig', KPATH_FRAMEWORK . '/config.php');
            // JLoader::register('KunenaController', KPATH_FRAMEWORK . '/controller.php');
            // JLoader::register('KunenaDate', KPATH_FRAMEWORK . '/date.php');
            // JLoader::register('KunenaError', KPATH_FRAMEWORK . '/error.php');
            // JLoader::register('KunenaException', KPATH_FRAMEWORK . '/exception.php');
            // JLoader::register('KunenaFactory', KPATH_FRAMEWORK . '/factory.php');
            // JLoader::register('KunenaInstaller', KPATH_FRAMEWORK . '/installer.php');
            // JLoader::register('KunenaLogin', KPATH_FRAMEWORK . '/login.php');
            // JLoader::register('KunenaModel', KPATH_FRAMEWORK . '/model.php');
            // JLoader::register('KunenaProfiler', KPATH_FRAMEWORK . '/profiler.php');
            // JLoader::register('KunenaSession', KPATH_FRAMEWORK . '/session.php');
            // JLoader::register('KunenaTree', KPATH_FRAMEWORK . '/tree.php');
            // JLoader::register('KunenaView', KPATH_FRAMEWORK . '/view.php');
            JLoader::register('KunenaAvatar', KPATH_FRAMEWORK . '/integration/avatar.php');
            // JLoader::register('KunenaPrivate', KPATH_FRAMEWORK . '/integration/private.php');
            // JLoader::register('KunenaProfile', KPATH_FRAMEWORK . '/integration/profile.php');
            // JLoader::register('KunenaForumAnnouncement', KPATH_FRAMEWORK . '/forum/announcement/announcement.php');
            JLoader::register('KunenaForumCategory', KPATH_FRAMEWORK . '/forum/category/category.php');
            // JLoader::register('KunenaForumCategoryUser', KPATH_FRAMEWORK . '/forum/category/user/user.php');
            JLoader::register('KunenaForumMessage', KPATH_FRAMEWORK . '/forum/message/message.php');
            // JLoader::register('KunenaForumMessageThankyou', KPATH_FRAMEWORK . '/forum/message/thankyou/thankyou.php');
            JLoader::register('KunenaForumTopic', KPATH_FRAMEWORK . '/forum/topic/topic.php');
            // JLoader::register('KunenaForumTopicPoll', KPATH_FRAMEWORK . '/forum/topic/poll/poll.php');
            JLoader::register('KunenaForumTopicUser', KPATH_FRAMEWORK . '/forum/topic/user/user.php');
            // JLoader::register('KunenaForumTopicUserRead', KPATH_FRAMEWORK . '/forum/topic/user/read/read.php');
            // JLoader::register('KunenaForumTopicRate', KPATH_FRAMEWORK . '/forum/topic/rate/rate.php');
            // JLoader::register('KunenaIcons', KPATH_FRAMEWORK . '/icons/icons.php');

        // if (Factory::getApplication()->isClient('site'))
        // {
        //     JLoader::registerPrefix('ComponentKunenaController', KPATH_SITE . '/controller');
        // }

        // Kunena has been initialized
        define('KUNENA_LOADED', 1);


        // // Check if Kunena API exists
        // $api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
        // require_once $api;

        // jimport('kunena.factory');
        // $this->kunena = new Kunena();
        // $this->jcomments = new JComments();
        // $this->user = new User();
    }

    public function forumCreate($comment)
    {
        $this->out('Creatinng forum.[white]' . $comment->forum_title . '[/] ', false);

        $timestamp = strtotime($comment->date) / (60 * 60 * 60);
        $category = [
            'parent_id' => $comment->parent_forum_id,
            'name' => $comment->forum_title,
            'alias' => $comment->forum_alias,
            'icon' => '',
            'icon_id' => '0',
            'locked' => '0',
            'accesstype' => 'joomla.level',
            'access' => '1',
            'pub_access' => '1',
            'pub_recurse' => '1',
            'admin_access' => '8',
            'admin_recurse' => '1',
            'ordering' => $timestamp,
            'published' => '1',
            'channels' => 'THIS',
            'checked_out' => '0',
            'checked_out_time' => '0000-00-00 00:00:00',
            'review' => '0',
            'allow_anonymous' => '0',
            'post_anonymous' => '0',
            'hits' => '0',
            'description' => $comment->forum_description,
            'headerdesc' => '',
            'class_sfx' => '',
            'allow_polls' => '0',
            'topic_ordering' => 'lastpost',
            'iconset' => 'default',
            'numTopics' => '0',
            'numPosts' => '0',
            'last_topic_id' => '0',
            'last_post_id' => '0',
            'last_post_time' => '0',
            'params' => '',
            'allow_ratings' => '0',
        ];

        $id = $this->insert('#__kunena_categories', $category);

        $this->out('[light_green]DONE![/] Id: [light_blue]' . $id . '[/]');

        return $id;
    }

    public function upsert($tableName, $data)
    {

        $this->out('Upserting ' . $tableName . ' [white]' . $data['title'] . '[/] ', false, 5);

        $className = '\\KunenaForum' . $tableName;
        $item = new $className(['id' => $data['id'] ]);
        $item->load();

        $item->bind($data);

        // if (!$item->exists()) {
        //     $item->ordering = 99999;
        // }

        $item->save();

        if ($item->exists()) {
            $this->out('update is [light_green]DONE![/]', true);
        } else {
            $msg = JText::sprintf('Failed to save', $item->id, $item->getError());
            $this->out('[red]'.$msg.'[/]', true, 5);
        }
        $this->out();


        return $item;
    }

    public function addOrUpdateCommentAsPost($comment) // phpcs:ignore
    {
        $topicId = $this->getTopicId($comment);

        if (!$comment->userid) {
            $comment->userid = $this->getUserIdByEmail($comment->email);
        }
        if (!$comment->userid) {
            $comment->userid = $this->addJoomlaUser($comment->name, $comment->email, \uniqid(), $comment->email, $comment->date);
        }
return;
        $post = [
            'id' => $comment->kunena_id,
            'parent' => $comment->kunena_parent,
            'thread' => $parent_id,
            'catid' => $comment->forum_id,
            'name' => $comment->name,
            'userid' => $comment->userid,
            'email' => $comment->email,
            'subject' => $comment->title,
            'time' => strtotime($comment->date),
            'ip' => $comment->ip,
            'topic_emoticon' => '0',
            'locked' => '0',
            'hold' => '0',
            'ordering' => '0',
            'hits' => '0',
            'moved' => '0',
            'modified_by' => null,
            'modified_time' => null,
            'modified_reason' => null,
            'message' => str_replace('<br />', PHP_EOL, $comment->comment),


            'title' => $comment->title,

            // * @property int    $thread
            // * @property int    $catid
            // * @property string $name
            // * @property int    $userid
            // * @property string $email
            // * @property string $subject
            // * @property int    $time
            // * @property string $ip
            // * @property int    $topic_emoticon
            // * @property int    $locked
            // * @property int    $hold
            // * @property int    $ordering
            // * @property int    $hits
            // * @property int    $moved
            // * @property int    $modified_by
            // * @property string $modified_time
            // * @property string $modified_reason
            // * @property string $params
            // * @property string $message
        ];

        $this->upsert('Message', $post);
        // exit;
return;
        $this->upsert('#__kunena_messages', $post);

        $postText = [
            'mesid' => $comment->kunena_id,
            'message' => $comment->comment,
        ];

        $this->upsert('#__kunena_messages_text', $postText, 'mesid');


        // $subscribe = [
        //     'user_id' => $comment->user_id,
        //     'topic_id' => $comment->parent,
        //     'category_id' => $comment->forum_id,
        //     'posts' => '0',
        //     'last_post_id' => '0',
        //     'owner' => '1',
        //     'favorite' => '0',
        //     'subscribed' => '1',
        //     'params' => '',
        // ];

        // $this->insert('#__kunena_user_topics', $subscribe);
    }

    public function addJoomlaUser($name, $username, $password, $email, $date) {
        $_SERVER['HTTP_HOST'] = 'https://gruz.ml';
        jimport('joomla.user.helper');

        $data = array(
            "name"=>$name,
            "username"=>$username,
            "password"=>$password,
            "password2"=>$password,
            "email"=>$email,
            "block"=>0,
            "groups"=>array("1","2"),
            "registerDate" => $date,
        );

        $user = new \JUser;
        //Write to database
        if(!$user->bind($data)) {
            throw new \Exception("Could not bind data. Error: " . $user->getError());
        }
        if (!$user->save()) {
            throw new \Exception("Could not save user. Error: " . $user->getError());
        }

        return $user->id;
    }

    public function getUserIdByEmail($email) {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email') . ' = ' . $db->quote($email));
        $db->setQuery($query);

        if ($id = $db->loadResult())
        {
            $user = \JTable::getInstance('User', 'JTable', array());
            $user->load($id);
            return $user->id;
        }
        else
        {
            // User with specified $email not found.
            return false;
        }
    }

    /*
    public function forumUpdate($forumId, $comment) // phpcs:ignore
    {
        $this->out('Updating forum [white]' . $comment->forum_title . '[/] ', true, 5);

        $timestamp = (int) (strtotime($comment->date) / (60 * 60 * 60));

        $data = [
            'id' => $forumId,
            'description' => $comment->forum_description,
            'ordering' => $timestamp,
        ];

        $id = $this->update('#__kunena_categories', $data, 'id');

        $this->out('Forum [white]' . $comment->forum_title . '[/] ', false, 5);
        $this->out('update is [light_green]DONE![/] Id: [light_blue]' . $id . '[/]', true);
        $this->out();

        return $id;
    }
    */

    public function upsertCategory($post, $findByAlias = false)
    {
        if ( is_object($post)) {
            $post = (array) $post;
        }

        if ($findByAlias) {
            $where = [
                'alias' => $post['alias'],
            ];

            $result = $this->db->recordExists('id', '#__kunena_categories', $where);

            $post['catid'] = (int) $result;
        }

        \KunenaFactory::loadLanguage('com_kunena.controllers', 'admin');

        $categotyExists = !empty($post['catid']) ? true :false;

        $success = false;

        $arr = [];
        if ($categotyExists) {
            $arr = ['id' => $post ['catid']];
        }
        $category = new \KunenaForumCategory($arr);
        $category->load();

        $ignore = [];

        $category->bind($post, $ignore);

        if (!$category->exists()) {
            $category->ordering = 99999;
        }

        $success = $category->save();

        if (!$success) {
            $msg = JText::sprintf('COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $category->getError());
            $this->out('[red]'.$msg.'[/]', true, 5);
        }

        $category->checkin();

        if ($success) {
            if ( $categotyExists ) {
                $msg = JText::sprintf('COM_KUNENA_A_CATEGORY_SAVED', $category->name);
            } else {

                $msg = JText::sprintf('Category `%s` created', $category->name);
            }
            $this->out('[green]' . $msg . '[/]', true, 5);
        }

        $this->out();

        return $category;
    }

    public function getExampleForumArray()
    {
        $arr = [
            'task' => 'save',
            'catid' => '0',
            '1585719b14096891945f186783dda633' => '1',
            'parent_id' => '0',
            'name' => 'en top',
            'alias' => 'en-top',
            'published' => '1',
            'icon' => '',
            'class_sfx' => '',
            'description' => '',
            'headerdesc' => '',
            'topictemplate' => '',
            'accesstype' => 'joomla.level',
            'access' => '1',
            'pub_access' => '1',
            'pub_recurse' => '1',
            'admin_access' => '8',
            'admin_recurse' => '1',
            'locked' => '0',
            'review' => '0',
            'allow_anonymous' => '0',
            'post_anonymous' => '0',
            'allow_polls' => '0',
            'channels' =>  ['THIS'],
            'topic_ordering' => 'lastpost',
            'iconset' => 'default',
            'allow_ratings' => '0',
            'params' => [],
        ];

        return $arr;
    }

    public function getTopicId($comment)
    {
        if (empty($comment->parent)) {
            $topicId = $comment->id;
        } else {
            list($zero, $topicId) = explode(',', $comment->path);
            return $topicId;
        }

        $topic_title = strip_tags($comment->comment);
        $topic_title = substr($topic_title, 0, 150);
        if (\strlen($topic_title) > strlen($comment->comment)) {
            $topic_title .= '...';
        }


        // Try to create topic, may be needed
        $topic = [
            'id' => $topicId,
            'category_id' => $comment->category_id,
            'subject' => $topic_title,
            'icon_id' => '0',
            'label_id' => '0',
            'locked' => '0',
            'hold' => '0',
            'ordering' => '0',
            'posts' => '1',
            'hits' => '0',
            'attachments' => '0',
            'poll_id' => '0',
            'moved_id' => '0',
            'first_post_id' => $comment->id,
            'first_post_time' => strtotime($comment->date),
            'first_post_userid' => $comment->userid,
            'first_post_message' => $comment->comment,
            'first_post_guest_name' => $comment->name,
            'last_post_id' => '0',
            'last_post_time' => strtotime($comment->date),
            'last_post_userid' => '0',
            'last_post_message' => '',
            'last_post_guest_name' => '',
            'rating' => '0',
            'params' => '',

            // Only for debug message
            'title' => $topic_title,
        ];

        $topic = $this->upsert('Topic', $topic);

        return $topicId;
    
    }

}
