<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

class User extends Base
{
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
        $user = \JFactory::getUser(0);

        if (! $user->bind($data)) {
            $this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
        }

        if (! $user->save()) {
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
        $user = \JFactory::getUser($userId);

        if (! $user->bind($data)) {
            $this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
        }

        if (! $user->save()) {
            $this->out('Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL . print_r($user->getError(), true));
        }

        return $user->id;
    }

    public function createUserIfNeeded($comment) // phpcs:ignore
    {
        $this->out('User ', false);

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
        $query = 'SELECT id FROM #__users WHERE email = ' . $db->Quote($comment->email);
        $db->setQuery($query, 0, 1);
        $check = $db->loadResult();

        if ($check) {
            $comment->user_id = $check;
            $comment->user_id = $this->updateUser($comment->user_id);
            $this->out('[light_cyan]' . $comment->email . "[/] [light_green]exists[/] with ID = [light_blue]" . $comment->user_id . '[/]. Updated user groups.');
        } else {
            // $comment->user_id = $this->addJoomlaUser( $comment->name, $comment->email, md5(microtime()), $comment->email );
            $comment->user_id = $this->createUser($comment->name, $comment->email, md5(microtime()), $comment->email);
            $this->out('[light_cyan]' . $comment->email . "[/] [light_green]created[/] with ID = [light_blue]" . $comment->user_id . '[/]');
        }

        return $comment->user_id;
    }
}
