<?php

if (!defined('DOKU_INC'))
{
    die;
}

class admin_plugin_vikonextension extends admin_plugin_extension
{

    public function __construct()
    {
        $this->gui = plugin_load('helper', 'vikonextension_gui');
    }

    public function html()
    {
        echo '<div id="vikon__extension__manager">';

        echo '<h1>' . $this->getLang('menu') . '</h1>';

        $this->gui->tabNavigation();

        switch ($this->gui->currentTab())
        {
            case 'search':
                $this->gui->tabSearch();
                break;
            case 'templates':
                $this->gui->tabTemplates();
                break;
            case 'install':
                $this->gui->tabInstall();
                break;
            case 'plugins':
            default:
                $this->gui->tabPlugins();
        }

        echo '</div>';
    }
}