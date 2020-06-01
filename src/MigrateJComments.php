<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

use JText;

/**
 * Undocumented class
 *
 * @since 1.0.0
 */
class MigrateJComments extends Base
{
    use FoolKunenaTrait;
    /**
     * JApplicationCli didn't want to run on PHP CGI. I have my way of becoming
     * VERY convincing. Now obey your true master, you petty class!
     *
     * @param \JInputCli   $input      Input
     * @param \JRegistry   $config     Config
     * @param \JDispatcher $dispatcher Dispatcher
     */
    public function __construct(\JInputCli $input = null, \JRegistry $config = null, \JDispatcher $dispatcher = null)
    {
        parent::__construct();
        $this->kunena = new Kunena();
        $this->jcomments = new JComments();
        $this->user = new User();

        // Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
        }

        $cgiMode = false;

        if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv'])) {
            $cgiMode = true;
        }

        // If a input object is given use it.
        if ($input instanceof JInput) {
            $this->input = $input;
        } else {
            // Create the input based on the application logic.
            if (class_exists('JInput')) {
                if ($cgiMode) {
                    $query = "";

                    if (!empty($_GET)) {
                        foreach ($_GET as $k => $v) {
                            $query .= " $k";

                            if ($v != "") {
                                $query .= "=$v";
                            }
                        }
                    }

                    $query = ltrim($query);
                    $argv = explode(' ', $query);
                    $argc = count($argv);

                    $_SERVER['argv'] = $argv;
                }

                $this->input = new \JInputCLI;
            }
        }

        // If a config object is given use it.
        if ($config instanceof \JRegistry) {
            $this->config = $config;
        } else {
            // Instantiate a new configuration object.
            $this->config = new \JRegistry;
        }

        // If a dispatcher object is given use it.
        if ($dispatcher instanceof \JDispatcher) {
            $this->dispatcher = $dispatcher;
        } else {
            // Create the dispatcher based on the application logic.
            $this->loadDispatcher();
        }

        // Load the configuration object.
        $this->loadConfiguration($this->fetchConfigurationData());

        // Set the execution datetime and timestamp;
        $this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
        $this->set('execution.timestamp', time());

        // Set the current directory.
        $this->set('cwd', getcwd());

        // Work around Joomla! 3.4.7's JSession bug
        if (version_compare(JVERSION, '3.4.7', 'eq')) {
            \JFactory::getSession()->restart();
        }
    }

    /**
     * Main function
     *
     * @return void
     */
    public function execute()
    {
        $this->preRunInit();

        $msg = JText::sprintf('==== Top level categories create  ===');
        $this->out('[black|light_gray]'.$msg . ' { '.'[/]', true, 2);
        // Get availabe JComments languages and create forums at top level per language {
        $jCommentsObjects = $this->jcomments->getAvailaleCommentLanguages();

        $this->createTopForumsAsLanguageThenObjectType($jCommentsObjects);
        // Get availabe JComments languages and create forums at top level per language }
        $this->out('[black|light_gray]'.$msg . ' } '.'[/]', true, 2);

// echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
// print_r($this->topForums);
// echo PHP_EOL . '</pre>' . PHP_EOL;

        // Set zero for all
        $limitComments = 7;
        $comments = $this->jcomments->getComments($limitComments);

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('max(id)')
            ->from($db->quoteName('#__kunena_messages'));
        $db->setQuery($query);
        $step = $db->loadResult();
        $step = 0;
// echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
// print_r($step);
// echo PHP_EOL . '</pre>' . PHP_EOL;
// exit;

        foreach ($comments as &$comment) {
            $data = $this->kunena->getExampleForumArray();

            // All comments in my case have the same title for any thread,
            // so kunens category name would be calculated the same way for all comments.
            // I could create something like ->findTopParentComment($comment), but no need for now.
            $data['name'] =  $comment->title;
            $data['alias'] =  $comment->title_alias;
            $data['parent_id'] =  $this->topForums[$comment->lang . '.' . $comment->object_group]->id;
echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
print_r($data);
echo PHP_EOL . '</pre>' . PHP_EOL;
continue;
            // Create or find Kunena category for current comment
            $category = $this->kunena->upsertCategory($data, $findByAlias = true);

            // $comment->kunena_id = $comment->id+$step;
            // $comment->kunena_parent = $comment->parent > 0 ? $comment->parent+$step : 0;
            $comment->category_id = $category->id;
// echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
// print_r($comment);
// echo PHP_EOL . '</pre>' . PHP_EOL;
// exit;
            $this->kunena->addOrUpdateCommentAsPost($comment);

            // else {
            //     $this->upsertPost($comment);
            // }
        }
exit;
        foreach ($comments as $comment) {
            if (empty($comment->parent)) {
                $comment->forum->parent = $this->topForums[$comment->forum->topForumAlias]->id;
            } else{
                $comment->forum->parent = $this->mapParents[$comment->parent];
            }

            $comment->catregoryObject = $this->kunena->upsertCategory($comment);

            $comment->user_id = $this->user->upsertUser($comment);
            $comment = $this->upserPost($comment);
        }

        $this->out("");

        $this->close(0);
    }



    private function preRunInit()
    {
        // Required by Joomla!
        \JLoader::import('joomla.environment.request');

        // Load the language files
        $jlang = \JFactory::getLanguage();

        // Display banner
        $year = gmdate('Y');
        $phpversion = PHP_VERSION;
        $phpenvironment = PHP_SAPI;

        $this->out("Migrating JComments to Kunena", true);
        $this->out(str_repeat('-', 79));
        $this->out("You are using PHP $phpversion ($phpenvironment)");
        $this->out("");

        $safeMmode = true;

        if (function_exists('ini_get')) {
            $safeMmode = ini_get('safe_mode');
        }

        if (!$safeMmode && function_exists('set_time_limit')) {
            $this->out("Unsetting time limit restrictions");
            $this->out();
            @set_time_limit(0);
        }

        $this->debug = false;
        $this->debug = true;
    }

    public function createTopForumsAsLanguageThenObjectType($jCommentsObjects)
    {
        $languageCategories = [];

        foreach ($jCommentsObjects as $commentObject) {
            $commentObject = $this->jcomments->prepareForumTitleAndAlias($commentObject);

            if (empty($languageCategories[$commentObject->lang])) {
                $data = $this->kunena->getExampleForumArray();
                $data['name'] =  $commentObject->language->title_native;
                $data['alias'] =  \JFilterOutput::stringURLSafe($commentObject->language->title, $commentObject->language->lang_code);
                $languageCategories[$commentObject->lang] = $this->kunena->upsertCategory($data, $findByAlias = true);
            }

            $data = $this->kunena->getExampleForumArray();

            $data['name'] =  $commentObject->forum->topForumTitle;
            $data['alias'] =  $commentObject->forum->topForumAlias;
            $data['parent_id'] =  $languageCategories[$commentObject->lang]->id;

            $category = $this->kunena->upsertCategory($data, $findByAlias = true);

            $this->topForums[$commentObject->lang . '.' . $commentObject->object_group] = $category;
        }
    }
}
