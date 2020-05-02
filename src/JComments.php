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

    public function getComments()
    {
        $object_groups = $this->getAvailableObjects();

        $comments = [];

        foreach ($object_groups as $object_group) {
            switch ($object_group) {
                case 'com_ars_category':
                    // $join_table = '#__ars_categories';
                    // $join_column = 'id';
                    $join_select = ['title'];
                    // $fa_lang_reference_table = 'ars_categories';
                    break;
                case 'com_content':
                default:
                    // $join_table = '#__categories';
                    // $join_column = 'id';
                    $join_select = ['title'];
                    // $fa_lang_reference_table = 'content';
                    break;
            }

            // $jc = 'jc';
            // $forum = 'forum';
            // $falang = 'falang';

            // foreach ($join_select as $k => $field) {
            //     $join_select[ $k ] = $forum . '.' . $field . ' AS ' . $forum . '_' . $field;
            // }

            // $fileds_to_select = implode(',', $join_select);

            $db = \JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('*')
                ->from($db->quoteName('#__jcomments') . ' AS jc ')
                ->join('LEFT', $db->quoteName('#__jcomments_objects', 'objects') . ' ON (`jc`.object_id = `objects`.object_id )')
                ->where('`jc`.`published` = 1')
                ->where('`jc`.lang = `objects`.lang')
                ->where('`jc`.object_group = `objects`.object_group')
                ->order($db->quoteName('jc.id'));
    
            // $query->select($jc . '.*, ' . $fileds_to_select . ', ' . $falang . '.value AS falang_title, ' . $falang . '.language_id , languages.lang_id' );
            // $query->from($db->quoteName('#__jcomments') . ' ' . $jc);
            // $query->join('LEFT', $db->quoteName('#__languages', 'languages') . ' ON (' . $db->quoteName('languages.lang_code') . ' = ' . $db->quoteName($jc . '.lang') . ')');
            // $query->join('LEFT', $db->quoteName($join_table, $forum) . ' ON (' . $db->quoteName($jc . '.object_id') . ' = ' . $db->quoteName($forum . '.id') . ')');
            // $query->join('LEFT', $db->quoteName('#__falang_content', $falang) . ' ON (' . $db->quoteName($falang . '.reference_id') . ' = ' . $db->quoteName($jc . '.object_id') . ')');
            // $query->where($db->quoteName($jc . '.object_group') . " = " . $db->quote($object_group));
            // $query->where($db->quoteName($jc . '.published') . " = " . $db->quote(1));
            // $query->where($db->quoteName($falang . '.reference_table') . " = " . $db->quote($fa_lang_reference_table));
            // $query->where($db->quoteName($falang . '.reference_field') . " = " . $db->quote('title'));
            // // $query->where($db->quoteName($falang . '.language_id') . " = " . $db->quoteName('languages.lang_id'));
            // $query->order($db->quoteName($jc . '.id'));

            if ($this->debug) {
                $query->setLimit('2');
            }

            // if ($object_group !== 'com_ars_category') {

    // $a = $query->__toString();
    // echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
    // print_r($a);
    // echo PHP_EOL . '</pre>' . PHP_EOL;
    // exit;
            // }
            // $query
            // ->select(array('a.*', 'b.username', 'b.name'))
            // ->from($db->quoteName('#__content', 'a'))
            // ->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')')
            // ->where($db->quoteName('b.username') . ' LIKE \'a%\'')
            // ->order($db->quoteName('a.created') . ' DESC');
            $db->setQuery($query);

            $rows = $db->loadObjectList();

            $rows = $this->prepareCommentsAdditionalFields($rows);

            foreach ($rows as $key => $value) {
                $rows[$key] = $this->prepareForumTitleAndAlias($value);

                // if ($value->language_id === $value->lang_id) {
                //     $rows[$key]->kunena_category_name = $value->falang_title;
                // } else {
                //     $rows[$key]->kunena_category_name = $value->forum_title;
                // }
            }

            $comments = array_merge($comments, $rows);
        }

        return $comments;
    }

    public function prepareForumTitleAndAlias($comment)
    {
        // $lang = explode('-', $comment->lang);
        // $lang = $lang[0];
        // $topForumAlias = $lang . '-' . $comment->object_group;
        // $topForumAlias = \JFilterOutput::stringURLSafe($topForumAlias);
        $topForumAlias = \JFilterOutput::stringURLSafe($comment->extensions_title, $comment->lang);
        // $topForumTitle = ucfirst($lang)  . ' ' . ucfirst($comment->object_group);
        $topForumTitle = $comment->extensions_title;

        if (! isset($comment->forum)) {
            $comment->forum = new \stdClass;
        }

        $comment->forum->topForumTitle = $topForumTitle;
        // $comment->forum->alias = $alias;
        $comment->forum->topForumAlias = $topForumAlias;


        // if (!isset($comment->title)) {
        //     return $comment;
        // }
        // if (empty(trim($comment->title))) {
        //     $len = 200;

        //     if (strlen($comment->comment) > $len) {
        //         $pos = strpos($comment->comment, ' ', $len);
        //         $comment->title = substr($comment->comment, 0, $pos) . '...';
        //     } else {
        //         $comment->title = $comment->comment;
        //         foreach (['.', '!' , ',', '?'] as $glue) {
        //             $tmp = explode($glue, trim($comment->comment), 2);

        //             if (count($tmp) > 0 && !empty($tmp[1]) ) {
        //                 $comment->title = $tmp[0] . '.';
        //                 break;
        //             }
        //         }
        //     }
        // }

        // $comment->forum->date = strtotime($comment->date);


        return $comment;
    }
}
