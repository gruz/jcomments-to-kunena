<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

class JComments extends Base
{
    public function prepareCommentsAdditionalFields($comments) {

        static $availavle_languages = [];

        if (empty($availavle_languages)) {
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('lang_code, title, title_native', 'lang_id');
            $query->from($db->quoteName('#__languages'));
            $query->where($db->quoteName('published') . " = " . $db->quote(1));
            $db->setQuery($query);
            $availavle_languages = $db->loadAssocList('lang_code');
        }

        $jlang = \JFactory::getLanguage();

        $extensions = [
            'com_ars_category' => [
                'en-GB' => 'Extensions & Code',
                'uk-UA' => 'Розширення та код',
            ]
        ];

        foreach ($comments as $key => $value) {
            $comments[$key]->language = (object) $availavle_languages[$value->lang];

            if (!empty($extensions[$value->object_group]) && !empty($extensions[$value->object_group][$value->lang])) {
                $comments[$key]->extensions_title = $extensions[$value->object_group][$value->lang];
            } else {
                $jlang->load($value->object_group, JPATH_ADMINISTRATOR, $value->lang, true);
                $comments[$key]->extensions_title = \JText::_($value->object_group);
            }
        }

        return $comments;

    }

    public function getAvailaleCommentLanguages()
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT object_group, lang');
        $query->from($db->quoteName('#__jcomments'));
        $query->where($db->quoteName('published') . " = " . $db->quote(1));
        $db->setQuery($query);
        $result = $db->loadObjectList();

        $result = $this->prepareCommentsAdditionalFields($result);

        return $result;
    }

    public function getAvailableObjects()
    {
        static $result = null;

        if (empty($result)) {
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('DISTINCT object_group');
            $query->from($db->quoteName('#__jcomments'));
            $db->setQuery($query);

            $result = $db->loadColumn();
        }

        return $result;
    }

    public function getComments($limit = 0)
    {
        $comments = [];

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('
                jc.*, 
                objects.object_group,
                objects.title,
                objects.link
            ')
            ->from($db->quoteName('#__jcomments') . ' AS jc ')
            ->join('LEFT', $db->quoteName('#__jcomments_objects', 'objects') . ' ON (`jc`.object_id = `objects`.object_id )')
            ->where('`jc`.`published` = 1')
            ->where('`jc`.lang = `objects`.lang')
            ->where('`jc`.object_group = `objects`.object_group')
            ->order($db->quoteName('jc.id'));

        $query->setLimit($limit);
        $db->setQuery($query);

        $rows = $db->loadObjectList();
        // echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
        // echo $db->replacePrefix((string) $query);
        // echo PHP_EOL . '</pre>' . PHP_EOL;
        // exit;

        // echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
        // print_r($rows);
        // echo PHP_EOL . '</pre>' . PHP_EOL;
        // exit;
        $rows = $this->prepareCommentsAdditionalFields($rows);

        foreach ($rows as $key => $value) {
            $rows[$key] = $this->prepareForumTitleAndAlias($value);
            $rows[$key]->comment = \str_replace('<br />', PHP_EOL, $rows[$key]->comment);
            // $rows[$key]->id = $rows[$key]->comment_id;
            // unset($rows[$key]->comment_id);
        }

        $comments = array_merge($comments, $rows);

        return $comments;
    }

    public function prepareForumTitleAndAlias($comment)
    {
        $topForumAlias = \JFilterOutput::stringURLSafe($comment->extensions_title, $comment->lang);
        $topForumTitle = $comment->extensions_title;

        if (! isset($comment->forum)) {
            $comment->forum = new \stdClass;
        }

        $comment->forum->topForumTitle = $topForumTitle;
        // $comment->forum->alias = $alias;
        $comment->forum->topForumAlias = $topForumAlias;

        if (isset($comment->title)) {
            if (empty($comment->parent)) {
                $parent_id = $comment->id;
            } else {
                list($zero, $parent_id) = explode(',' , $comment->path);
            }

            $comment->title_alias = \JFilterOutput::stringURLSafe($comment->title . '-' . $comment->object_group . '-' . $parent_id, $comment->lang);
        }

        return $comment;
    }
}
