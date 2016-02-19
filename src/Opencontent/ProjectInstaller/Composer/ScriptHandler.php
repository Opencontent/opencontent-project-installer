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
        $script->removeEzComposerJson();
        $script->removeEzDemo();
    }

    public static function update( CommandEvent $event )
    {
        $script = new static( $event );
        $script->installSettings();
        $script->installConfigPhp();
        $script->removeEzComposerJson();
        $script->removeEzDemo();
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

        if ( $this->fs->exists( $original ) )
        {
            if ( $original != $target )
            {
                $this->fs->symlink( $original, $target );
            }
        }
        else
        {
            $this->io->writeError( "File o cartella $original non trovato!" );
        }
    }

    protected function installSettings( $question = "Installo i settings? (y|n) " )
    {
        if ( $this->io->askConfirmation( $question ) )
        {
            $env = 'dev';

            $env = $this->io->ask( "Quale ambiente vuoi installare? Scegli dev o prod [$env]", $env );
            if ( in_array( $env, array( 'dev', 'prod' ) ) )
            {
                $this->doSymlink(
                    $this->currentDirectory( "/settings_{$env}/override" ),
                    $this->documentRootDirectory( '/settings/override' )
                );

                $this->doSymlink(
                    $this->currentDirectory( "/settings_{$env}/siteaccess" ),
                    $this->documentRootDirectory( '/settings/siteaccess' )
                );
            }
            else
            {
                $this->io->writeError( "Ambiente $env non trovato!" );
            }
        }
    }

    protected function installConfigPhp( $question = "Installo il config.php? (y|n) " )
    {
        $original = $this->currentDirectory( '/config.php' );
        $target = $this->documentRootDirectory( '/config.php' );
        if ( file_exists( $original ) && $this->io->askConfirmation( $question ) )
        {
            $this->doSymlink( $original, $target );
        }
    }

    protected function removeEzComposerJson( $question = "Rimuovo composer.json di ez per sicurezza? (y|n) " )
    {
        $composer = $this->documentRootDirectory( '/composer.json' );
        if ( file_exists( $composer ) && $this->io->askConfirmation( $question ) )
        {
            $this->fs->remove( $composer );
        }
    }

    protected function removeEzDemo( $question = "Rimuovo estensione ezdemo? (y|n) " )
    {
        $ezDemo = $this->documentRootDirectory( '/extension/ezdemo' );
        if ( file_exists( $ezDemo ) && $this->io->askConfirmation( $question ) )
        {
            $this->fs->remove( $ezDemo );
        }
    }
}
