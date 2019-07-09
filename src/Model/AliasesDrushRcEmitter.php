<?php

namespace Pantheon\TerminusAliases\Model;

use Symfony\Component\Filesystem\Filesystem;

class AliasesDrushRcEmitter extends AliasesDrushRcBase
{
    protected $location;

    public function __construct($location, $base_dir)
    {
        $this->location = $location;
        $this->base_dir = $base_dir;
    }

    public function notificationMessage()
    {
        return 'Writing Drush 8 alias file to ' . $this->location;
    }

    public function write(AliasCollection $collection)
    {
        $alias_file_contents = $this->getAliasContents($collection);

        $fs = new Filesystem();
        $fs->mkdir(dirname($this->location));

        file_put_contents($this->location, $alias_file_contents);

        // Add in our directory location to the Drush alias file search path
        $DrushRCEditor = new DrushRcEditor($this->base_dir);
        $drushConfig = $DrushRCEditor->getDrushConfig();
        $drushConfigFiltered = implode(array_filter($drushConfig, array($this, 'filterForPantheon')));
        $drushConfigFiltered .= "\n" . '$options["include"][] = drush_server_home() . "/.drush/pantheon/drush8";';
        $DrushRCEditor->writeDrushConfig($drushConfigFiltered);

    }

    protected function filterForPantheon($line)
    {
        if (strpos($line, 'pantheon')) {
            return false;
        }
        return true;
    }
}
