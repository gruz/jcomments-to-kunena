<?php

namespace gruz\JCommentsToKunenaCli;

class Db extends Base
{
    public function recordExists(string $main_key, string $table, array $where)
    {
        $this->out('Checking [yellow]' . $table . '[/] for [white]' . $main_key . '[/] ', false, 5);
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($main_key);

        $query->from($db->quoteName($table));

        foreach ($where as $key => $value) {
            $query->where($db->quoteName($key) . " = " . $db->quote($value));
        }

        $db->setQuery($query);
        $id = $db->loadResult();

        $this->out('[purple]' . ($id ? 'EXISTS' : 'NOTEXISTS') . '[/]');
        return $id;
    }

    public function upsert(string $table, array $data, string $key = 'id')
    {
        $this->out('[yellow|light_grey]Upserting started[/] for [white]' . $table . '[/] for key [yellow]' . $key . '[/]');

        if (isset($data[$key]) && !empty($data[$key])) {
            $data[$key] = $data[$key];

            $where = [
                $key => $data[$key]
            ];

            $exists = $this->recordExists($key, $table, $where);
        } else {
            $exists = false;
        }

        if ($exists) {
            $id = $this->update($table, $data, $key);
        } else {
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
        $db = \JFactory::getDbo();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Insert columns.
        $columns = array_keys($data);

        // Insert values.
        foreach ($data as $key => $value) {
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

    public function update($table, $data, $key = 'id') // phpcs:ignore
    {
        $db = \JFactory::getDbo();

        $this->out('[light_gray]Updating[/] data in [light_purple]' . $table . '[/] ...', false, 5);
        $object = (object) $data;

        $db->updateObject($table, $object, $key);
        $id = $object->{$key};
        $this->out('[white]Done[/]');

        return $id;
    }
}
