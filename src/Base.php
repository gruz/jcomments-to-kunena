<?php
// phpcs:ignore

namespace gruz\JCommentsToKunenaCli;

class Base extends \JApplicationCli
{
    use CliOutput;

    public $debug = true;
    
    public function __construct()
    {
        $this->__initCLIOutput();

        $currentClass = get_class($this);
        $currentClass = explode('\\', $currentClass);
        $currentClass = end($currentClass);

        if ( 'Db' !== $currentClass ) {
            $this->db = new Db();
        }

        // $this->kunena = new Kunena();
        // $this->jcomments = new JComments();
        // $this->user = new User();


        return;
        $classesToLoad = [
            'Kunena',
            'JComments',
            'User',
            'Db',
        ];

echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
print_r($currentClass);
echo PHP_EOL . '</pre>' . PHP_EOL;
        foreach ($classesToLoad as $className) {
            if ($className === $currentClass) {
echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
print_r($className . '= ' . $currentClass);
echo PHP_EOL . '</pre>' . PHP_EOL;
                continue;
            }
            $className = 'gruz\\JCommentsToKunenaCli\\' . $className;
echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
print_r('Loading ....' . $className);
echo PHP_EOL . '</pre>' . PHP_EOL;
            if ( !isset($this->{strtolower($className)}))
            $this->{strtolower($className)} = new $className();
        }

echo PHP_EOL. PHP_EOL;
    }
}
