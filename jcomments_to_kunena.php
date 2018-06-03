<?php
// phpcs:ignore

namespace JCommentsToKunenaCli;

\JLoader::registerNamespace('JCommentsToKunenaCli', __DIR__, false, false, 'psr4');

const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/../defines.php'))
{
	require_once dirname(__DIR__) . '/../defines.php';
}

if (!defined('JDEBUG'))
{
	define('JDEBUG', false);
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Load the rest of the framework include files
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else
{
	require_once JPATH_LIBRARIES . '/import.php';
}

require_once JPATH_LIBRARIES . '/cms.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');
JLoader::import('joomla.application.component.helper');
JLoader::import('cms.component.helper');

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Undocumented class
 *
 * @since 1.0.0
 */
class MigrateJComments extends JApplicationCli
{
	/**
	 * JApplicationCli didn't want to run on PHP CGI. I have my way of becoming
	 * VERY convincing. Now obey your true master, you petty class!
	 *
	 * @param   JInputCli   $input Input
	 * @param   JRegistry   $config Config
	 * @param   JDispatcher $dispatcher Dispatcher
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		/**
		// Restore_error_handler();

		// // Set all errors to output the messages to the console, in order to
		// // avoid infinite loops in JError ;)
		// JError::setErrorHandling(E_ERROR, ' asadie');
		// JError::setErrorHandling(E_WARNING, 'echo');
		// JError::setErrorHandling(E_NOTICE, 'echo');
		*/

		$this->__constructColors();

		// Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
		if (array_key_exists('REQUEST_METHOD', $_SERVER))
		{
			die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
		}

		$cgiMode = false;

		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$cgiMode = true;
		}

		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('JInput'))
			{
				if ($cgiMode)
				{
					$query = "";

					if (!empty($_GET))
					{
						foreach ($_GET as $k => $v)
						{
							$query .= " $k";

							if ($v != "")
							{
								$query .= "=$v";
							}
						}
					}

					$query = ltrim($query);
					$argv = explode(' ', $query);
					$argc = count($argv);

					$_SERVER['argv'] = $argv;
				}

				$this->input = new JInputCLI;
			}
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
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
		if (version_compare(JVERSION, '3.4.7', 'eq'))
		{
			JFactory::getSession()->restart();
		}
	}

	/**
	 * Main function
	 *
	 * @return void
	 */
	public function execute()
	{

		// Required by Joomla!
		JLoader::import('joomla.environment.request');

		// Load the language files
		$jlang = JFactory::getLanguage();

		// $jlang->load('com_ars', JPATH_ADMINISTRATOR);

		// Display banner
		$year = gmdate('Y');
		$phpversion = PHP_VERSION;
		$phpenvironment = PHP_SAPI;

		$this->out("Migrating JComments to Kunena", true);
		$this->out(str_repeat('-', 79));
		$this->out("You are using PHP $phpversion ($phpenvironment)");
		$this->out("");

		$safeMmode = true;

		if (function_exists('ini_get'))
		{
			$safeMmode = ini_get('safe_mode');
		}

		if (!$safeMmode && function_exists('set_time_limit'))
		{
			$this->out("Unsetting time limit restrictions");
			$this->out();
			@set_time_limit(0);
		}

		$this->debug = false;
		$this->debug = true;

		$comments = $this->getComments();

		$i = 5;
		foreach ($comments as $key => $comment)
		{
			$comment = $this->getOrCreateCommentForum($comment);
			// $comment->id = $i;
			// $i++;
			// $userId = $this->createUserIfNeeded($comment);

			// $comment->user_id = $userId;

			// $comment = $this->addOrUpdateCommentAsPost($comment);
			$this->out("");
		}

		$this->close(0);
	}

	public function getOrCreateCommentForum($comment)
	{
		// $db = JFactory::getDbo();
		// $query = $db->getQuery(true);
		$support_forum_ids = [
			'en' => 4,
			'uk' => 9,
		];

		switch ($comment->lang)
		{
			case 'uk-UA':
				$lang = 'uk';
				$forumTitle = $comment->forum_title;
				break;

			case 'en-GB':
			default:
				$lang = 'en';
				$forumTitle = $comment->falang_title;
				break;
		}

		$forumTitle = explode(' - ', $forumTitle, 2);
		$forumDescription = ucfirst($forumTitle[1]);
		$forumTitle = trim($forumTitle[0]);
		$comment->parent_forum_id = $support_forum_ids[ $lang ];

		$comment->forum_title = $forumTitle;
		$comment->forum_description = $forumDescription;
		$comment->forum_alias = $lang . '-' . JFilterOutput::stringURLSafe($comment->forum_title);

		$this->out('Proceesing forum.[white]' . $comment->forum_title . '[/] ');
		$forumId = $this->recordExists( 'id', '#__kunena_categories',  [ 'parent_id' => $comment->parent_forum_id, 'name' => $forumTitle ]);

		$this->out('Forum [green]' . $forumTitle . '[/green] in language [yellow]' . $lang . '[/yellow] ' . ($forumId ? '[light_green]Exists with ID = ' . $forumId . '[/]' : '[purple] does not exist[/] '), true, 5);

		if (! $forumId)
		{
			$forumId = $this->forumCreate($comment);
		}
		else
		{
			$this->forumUpdate($forumId, $comment);
		}
		
		$comment->forum_id = $forumId;

		$data = [
			'alias' => $comment->forum_alias,
			'type' => 'catid',
			'item' => $comment->forum_id,
		];

		$this->upsert( '#__kunena_aliases', $data, 'alias');


		return $comment;
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

	public function forumUpdate($forumId, $comment) // phpcs:ignore
	{
		$this->out('Updating forum [white]' . $comment->forum_title . '[/] ', true, 5);

		$timestamp = (int) ( strtotime($comment->date) / (60 * 60 * 60) );

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

	public function createUserIfNeeded($comment) // phpcs:ignore
	{
		$this->out('User ', false);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = 'SELECT id FROM #__users WHERE email = ' . $db->Quote($comment->email);
		$db->setQuery($query, 0, 1);
		$check = $db->loadResult();

		if ($check)
		{
			$comment->user_id = $check;
			$comment->user_id = $this->updateUser($comment->user_id);
			$this->out('[light_cyan]' . $comment->email . "[/] [light_green]exists[/] with ID = [light_blue]" . $comment->user_id . '[/]. Updated user groups.');
		}
		else
		{
			// $comment->user_id = $this->addJoomlaUser( $comment->name, $comment->email, md5(microtime()), $comment->email );
			$comment->user_id = $this->createUser($comment->name, $comment->email, md5(microtime()), $comment->email);
			$this->out('[light_cyan]' . $comment->email . "[/] [light_green]created[/] with ID = [light_blue]" . $comment->user_id . '[/]');
		}

		return $comment->user_id;
	}

	public function addOrUpdateCommentAsPost($comment) // phpcs:ignore
	{
		$this->out('Loking the comment ..,', false);
		$this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($comment, true));

		if (empty(trim($comment->title)))
		{
			$len = 200;

			if (strlen($comment->comment) > $len)
			{
				$pos = strpos($comment->comment, ' ', 200);
				$postTitle = substr($comment->comment, 0, $pos) . '...';
			}
			else
			{
				$postTitle = $comment->comment;
			}
		}
		else
		{
			$postTitle = $comment->title;
		}

		$date = strtotime($comment->date);
		$date = time();

		$post = [
			'id' => $comment->id,
			'parent' => $comment->parent,
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
			'modified_reason' => ''
		];

		$this->upsert('#__kunena_messages', $post);

		$postText = [
			'mesid' => $comment->id,
			'message' => $comment->comment,
		];

		$this->upsert('#__kunena_messages_text', $postText, 'mesid');

		if (empty($comment->parent))
		{
			$topic = [
				'id' => $comment->id,
				'category_id' => $comment->forum_id,
				'subject' => $postTitle,
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
				'first_post_time' => $date,
				'first_post_userid' => $comment->user_id,
				'first_post_message' => $comment->comment,
				'first_post_guest_name' => $comment->name,
				'last_post_id' => '0',
				'last_post_time' => $date,
				'last_post_userid' => '0',
				'last_post_message' => '',
				'last_post_guest_name' => '',
				'rating' => '0',
				'params' => ''
			];
			$this->upsert('#__kunena_topics', $topic);
		}

		// $subscribe = [
		// 	'user_id' => $comment->user_id,
		// 	'topic_id' => $comment->parent,
		// 	'category_id' => $comment->forum_id,
		// 	'posts' => '0',
		// 	'last_post_id' => '0',
		// 	'owner' => '1',
		// 	'favorite' => '0',
		// 	'subscribed' => '1',
		// 	'params' => '',
		// ];

		// $this->insert('#__kunena_user_topics', $subscribe);

	}

	public function getAvailableObjects()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT object_group');
		$query->from($db->quoteName('#__jcomments'));
		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	public function getComments()
	{
		$object_groups = $this->getAvailableObjects();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		foreach ($object_groups as $object_group)
		{
			switch ($object_group)
			{
				case 'com_ars_category':
					$join_table = '#__ars_categories';
					$join_column = 'id';
					$join_select = ['title'];
					$fa_lang_reference_table = 'ars_categories';
					break;
				case 'com_content':
				default:
					$join_table = '#__categories';
					$join_column = 'id';
					$join_select = ['title'];
					$fa_lang_reference_table = 'content';
					break;
			}

			$jc = 'jc';
			$forum = 'forum';
			$falang = 'falang';

			foreach ($join_select as $k => $field)
			{
				$join_select[ $k ] = $forum . '.' . $field . ' AS ' . $forum . '_' . $field;
			}

			$fileds_to_select = implode(',', $join_select);

			$query->select($jc . '.*, ' . $fileds_to_select . ', ' . $falang . '.value AS falang_title');
			$query->from($db->quoteName('#__jcomments') . ' ' . $jc);
			$query->join('LEFT', $db->quoteName($join_table, $forum) . ' ON (' . $db->quoteName($jc . '.object_id') . ' = ' . $db->quoteName($forum . '.id') . ')');
			$query->join('LEFT', $db->quoteName('#__falang_content', $falang) . ' ON (' . $db->quoteName($falang . '.reference_id') . ' = ' . $db->quoteName($jc . '.object_id') . ')');
			$query->where($db->quoteName($jc . '.object_group') . " = " . $db->quote($object_group));
			$query->where($db->quoteName($jc . '.published') . " = " . $db->quote(1));
			$query->where($db->quoteName($falang . '.reference_table') . " = " . $db->quote($fa_lang_reference_table));
			$query->where($db->quoteName($falang . '.reference_field') . " = " . $db->quote('title'));
			$query->order($db->quoteName($jc . '.id'));

			if ($this->debug)
			{
				$query->setLimit('2');
			}

			// $query
			// ->select(array('a.*', 'b.username', 'b.name'))
			// ->from($db->quoteName('#__content', 'a'))
			// ->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')')
			// ->where($db->quoteName('b.username') . ' LIKE \'a%\'')
			// ->order($db->quoteName('a.created') . ' DESC');
			$db->setQuery($query);

			$rows = $db->loadObjectList();

			return $rows;
		}
	}

	public function createUser($name, $username, $password, $email)
	{
		$data = array(
			'username' => $username,
			'name' => $name,
			'email' => $email,
			'password1' => $password,
			'password2' => $password,
			'block' => 0 ,
			"groups" => array("9","2"),
		);

		// $user = new JUser();
		$user = JFactory::getUser(0);

		if (! $user->bind($data))
		{
			$this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
		}

		if (! $user->save())
		{
			$this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
		}

		return $user->id;
	}

	public function updateUser($userId)
	{
		$data = array(
			"groups" => array("9","2"),
		);

		// $user = new JUser();
		$user = JFactory::getUser($userId);

		if (! $user->bind($data))
		{
			$this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
		}

		if (! $user->save())
		{
			$this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
		}

		return $user->id;
	}

	private $foregroundColors = array();

	private $backgroundColors = array();

	public function __constructColors()
	{
		// Set up shell colors
		$this->foregroundColors['black'] = '0;30';
		$this->foregroundColors['dark_gray'] = '1;30';
		$this->foregroundColors['blue'] = '0;34';
		$this->foregroundColors['light_blue'] = '1;34';
		$this->foregroundColors['green'] = '0;32';
		$this->foregroundColors['light_green'] = '1;32';
		$this->foregroundColors['cyan'] = '0;36';
		$this->foregroundColors['light_cyan'] = '1;36';
		$this->foregroundColors['red'] = '0;31';
		$this->foregroundColors['light_red'] = '1;31';
		$this->foregroundColors['purple'] = '0;35';
		$this->foregroundColors['light_purple'] = '1;35';
		$this->foregroundColors['brown'] = '0;33';
		$this->foregroundColors['yellow'] = '1;33';
		$this->foregroundColors['light_gray'] = '0;37';
		$this->foregroundColors['white'] = '1;37';

		$this->backgroundColors['black'] = '40';
		$this->backgroundColors['red'] = '41';
		$this->backgroundColors['green'] = '42';
		$this->backgroundColors['yellow'] = '43';
		$this->backgroundColors['blue'] = '44';
		$this->backgroundColors['magenta'] = '45';
		$this->backgroundColors['cyan'] = '46';
		$this->backgroundColors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foregroundColor = null, $backgroundColor = null) // phpcs:ignore
	{
		$coloredString = "";

		// Check if given foreground color found
		if (isset($this->foregroundColors[$foregroundColor]))
		{
			$coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
		}

		// Check if given background color found
		if (isset($this->backgroundColors[$backgroundColor]))
		{
			$coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
		}

		// Add string and end coloring
		$coloredString .= $string . "\033[0m";

		return $coloredString;
	}

	// Returns all foreground color names
	public function getForegroundColors() // phpcs:ignore
	{
		return array_keys($this->foregroundColors);
	}

	// Returns all background color names
	public function getBackgroundColors() // phpcs:ignore
	{
		return array_keys($this->backgroundColors);
	}

	public function out( $str = '', $br = true, $n = 0 ) // phpcs:ignore
	{
		if (null === $br)
		{
			$br = true;
		}

		if ($n > 0)
		{
			$str = str_repeat(' ', $n) . $str . str_repeat(' ', $n);
		}

		preg_match_all('/\[(.*)\](.*)\[\/.*\]/Ui', $str, $matches);

		foreach ($matches[0] as $k => $value)
		{
			$colors = $matches[1][ $k];
			$colors = explode('|', $colors);
			$f_color = $colors[0];
			$b_color = isset($colors[1]) ? $colors[1] : null;

			$colored = $this->getColoredString($matches[2][$k], $f_color, $b_color);

			$str = str_replace($matches[0][$k], $colored, $str);
		}

		parent::out($str, $br);
	}

	public function recordExists( string $main_key, string $table, array $where  )
	{
		$this->out('Checking [yellow]' . $table . '[/] for [white]' . $main_key . '[/] ', false, 5);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($main_key);

		$query->from($db->quoteName($table));

		foreach ($where as $key => $value) 
		{
			$query->where($db->quoteName($key) . " = " . $db->quote($value));
		}

		$db->setQuery($query);
		$id = $db->loadResult();

		$this->out( '[purple]' . ($id ? 'EXISTS' : 'NOTEXISTS' ) . '[/]' ); 
		return $id;
	}

	/**
	 * Upsert
	 *
	 * @param   string  $table  Table
	 * @param   array   $data   Date
	 * @param   string  $key    Index to search
	 *
	 * @return integer
	 */
	public function upsert( string $table, array $data, string $key = 'id' )
	{
		$this->out('[yellow|light_grey]Upserting started[/] for [white]' . $table . '[/] for key [yellow]' . $key . '[/]');

		if (isset($data[$key]) && !empty($data[$key]))
		{
			$data[$key] = $data[$key];

			$where = [ 
				$key => $data[$key] 
			];

			$exists = $this->recordExists($key, $table, $where );
		}
		else
		{
			$exists = false;
		}

		if ($exists)
		{
			$id = $this->update($table, $data, $key);
		}
		else
		{
			$id = $this->insert($table, $data, $key);
		}

		$this->out('[yellow|light_grey]Upserting finished[/]');
		$this->out();

		return  $id;
	}

	public function insert($table, $data) // phpcs:ignore
	{
		$this->out('[light_gray]Inserting[/] data into [light_purple]' . $table . '[/]', false, 5);

		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Insert columns.
		$columns = array_keys($data);

		// Insert values.
		foreach ($data as $key => $value)
		{
			$data[ $key ] = $db->quote($value);
		}



		$query->insert($db->quoteName($table));
		$query->columns($db->quoteName($columns));
		$query->values(implode(',', $data));

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);

		// $this->out( $db->replacePrefix((string) $query) );

		$db->execute();

		$id = $db->insertid();
		$this->out('[white]Done[/]');

		return $id;
	}

	public function update($table, $data, $key = 'id' ) // phpcs:ignore
	{
		$db = JFactory::getDbo();

		$this->out('[light_gray]Updating[/] data in [light_purple]' . $table . '[/] ...', false, 5);
		$object = (object) $data;

		$db->updateObject($table, $object, $key);
		$id = $object->{$key};
		$this->out('[white]Done[/]');

		return $id;
	}
}


// Instanciate and run the application
$app = JApplicationCli::getInstance('MigrateJComments');
JFactory::$application = $app;
$app->execute();
