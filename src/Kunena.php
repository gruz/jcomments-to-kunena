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
            // JLoader::register('KunenaForumTopicUser', KPATH_FRAMEWORK . '/forum/topic/user/user.php');
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
        // $this->out('Loking the comment ..,', false);
        // $this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($comment, true));

        if (empty($comment->kunena_parent)) {
            $topic = [
                'id' => $comment->kunena_id,
                'category_id' => $comment->forum_id,
                'subject' => $comment->topic_title,
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
                'first_post_id' => $comment->kunena_id,
                'first_post_time' => strtotime($comment->date),
                'first_post_userid' => $comment->userid,
                'first_post_message' => $comment->topic_title,
                'first_post_guest_name' => $comment->name,
                'last_post_id' => '0',
                'last_post_time' => strtotime($comment->date),
                'last_post_userid' => '0',
                'last_post_message' => '',
                'last_post_guest_name' => '',
                'rating' => '0',
                'params' => '',

                'title' => $comment->topic_title,
            ];

            $this->upsert('Topic', $topic);
        }
return;
        $post = [
            'id' => $comment->kunena_id,
            'parent' => $comment->kunena_parent,
            'thread' => $comment->parent,
            'catid' => $comment->forum_id,
            'name' => $comment->name,
            'userid' => $comment->user_id,
            'email' => $comment->email,
            'subject' => $postTitle,
            'time' => $date,
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

            * @property int    $thread
            * @property int    $catid
            * @property string $name
            * @property int    $userid
            * @property string $email
            * @property string $subject
            * @property int    $time
            * @property string $ip
            * @property int    $topic_emoticon
            * @property int    $locked
            * @property int    $hold
            * @property int    $ordering
            * @property int    $hits
            * @property int    $moved
            * @property int    $modified_by
            * @property string $modified_time
            * @property string $modified_reason
            * @property string $params
            * @property string $message
        ];

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

    public function upsertForum($post, $findByAlias = false)
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

        // $app        = \JFactory::getApplication();
        $isNew = empty($post ['catid']) ? true :false;

        $success = false;

        $arr = [];
        if (!$isNew) {
            $arr = ['id' => $post ['catid']];
        }
        $category = new \KunenaForumCategory($arr);
        $category->load();

        // $parent = new \KunenaForumCategory(array('id' =>$post['parent_id']));
        // $parent->load();


        $ignore = [];
        // Nobody can change id or statistics
        // $ignore = array('option', 'view', 'task', 'catid', 'id', 'id_last_msg', 'numTopics', 'numPosts', 'time_last_msg', 'aliases', 'aliases_all');

        // User needs to be admin in parent (both new and old) in order to move category, parent_id=0 needs global admin rights

        // if (!$this->me->isAdmin($parent) || ($category->exists() && !$this->me->isAdmin($category->getParent())))
        // {
        //     $ignore             = array_merge($ignore, array('parent_id', 'ordering'));
        //     $post ['parent_id'] = $category->parent_id;
        // }

        $category->bind($post, $ignore);

        if (!$category->exists()) {
            $category->ordering = 99999;
        }

        $success = $category->save();
        // $aliases_all = explode(',', $app->input->getString('aliases_all'));

        // $aliases = $app->input->post->getArray(array('aliases' => ''));

        // if ($aliases_all)
        // {
        //     $aliases = array_diff($aliases_all, $aliases['aliases']);

        //     foreach ($aliases_all as $alias)
        //     {
        //         $category->deleteAlias($alias);
        //     }
        // }

        if (!$success) {
            $msg = JText::sprintf('COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $category->getError());
            $this->out('[red]'.$msg.'[/]', true, 5);
        }

        $category->checkin();

        if ($success) {
            if ( $isNew ) {
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
}
