<?php

namespace Opencontent\ProjectInstaller\Composer;

use Composer\Script\CommandEvent;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler
{
    /**
     * @var CommandEvent
     */
    protected $composerEvent;

    protected $io;

    protected $extras;

    protected $fs;

    public static function install( CommandEvent $event )
    {
        $script = new static( $event );
        $script->installSettings();
        $script->installConfigPhp();
    }

    public static function update( CommandEvent $event )
    {
        $script = new static( $event );
        $script->installSettings();
        $script->installConfigPhp();
    }

    protected function __construct( CommandEvent $event )
    {
        $this->composerEvent = $event;
        $this->io = $this->composerEvent->getIO();
        $this->extras = $this->composerEvent->getComposer()->getPackage()->getExtra();
        $this->fs = new Filesystem();
    }

    protected function currentDirectory( $path = null )
    {
        $dir = getcwd();
        return $path ? $dir . $path : $dir;
    }

    protected function documentRootDirectory( $path = null )
    {
        $dir = isset( $this->extras['ezpublish-legacy-dir'] ) ? $this->currentDirectory() . '/' . rtrim( $this->extras['ezpublish-legacy-dir'], '/' ) : $this->currentDirectory() ;
        return $path ? $dir . $path : $dir;
    }

    protected function doSymlink( $original, $target )
    {
        if ( $this->fs->exists( $target ) )
        {
            $this->fs->remove( $target );
        }

        if ( $original != $target )
        {
            $this->fs->symlink( $original, $target );
        }
    }

    protected function installSettings( $question = "Installo settings? (y|n) " )
    {
        if ( $this->io->askConfirmation( $question ) )
        {
            $this->doSymlink(
                $this->currentDirectory( '/settings/override' ),
                $this->documentRootDirectory( '/settings/override' )
            );

            $this->doSymlink(
                $this->currentDirectory( '/settings/siteaccess' ),
                $this->documentRootDirectory( '/settings/siteaccess' )
            );
        }
    }

    protected function installConfigPhp( $question = "Installo config.php? (y|n) " )
    {
        $original = $this->currentDirectory( '/config.php' );
        $target = $this->documentRootDirectory( '/config.php' );
        if ( file_exists( $original ) && $this->io->askConfirmation( $question ) )
        {
            $this->doSymlink( $original, $target );
        }
    }

}
