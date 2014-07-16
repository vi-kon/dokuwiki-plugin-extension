<?php

if (!defined('DOKU_INC'))
{
    die;
}

class helper_plugin_vikonextension_gui extends helper_plugin_extension_gui
{
    public function tabNavigation()
    {
        echo '<ul class="nav nav-tabs" role="tablist">';
        foreach ($this->tabs as $tab)
        {
            $url = $this->tabURL($tab);
            if ($this->currentTab() == $tab)
            {
                $class = 'class="active"';
            }
            else
            {
                $class = '';
            }
            echo '<li ' . $class . '><a href="' . $url . '">' . $this->getLang('tab_' . $tab) . '</a></li>';
        }
        echo '</ul>';
    }

    /**
     * display the plugin tab
     */
    public function tabPlugins()
    {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;

        echo '<div class="alert alert-info">';
        echo $this->locale_xhtml('intro_plugins');
        echo '</div>';

        $pluginlist = $plugin_controller->getList('', true);
        sort($pluginlist);
        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('vikonextension_list');
        $list->start_form();
        foreach ($pluginlist as $name)
        {
            $extension->setExtension($name);
            $list->add_row($extension, $extension->getID() == $this->infoFor);
        }
        $list->end_form();
        $list->render();
    }

    public function tabTemplates()
    {
        echo '<div class="alert alert-info">';
        echo $this->locale_xhtml('intro_templates');
        echo '</div>';

        // FIXME do we have a real way?
        $tpllist = glob(DOKU_INC . 'lib/tpl/*', GLOB_ONLYDIR);
        $tpllist = array_map('basename', $tpllist);
        sort($tpllist);

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('vikonextension_list');
        $list->start_form();
        foreach ($tpllist as $name)
        {
            $extension->setExtension("template:$name");
            $list->add_row($extension, $extension->getID() == $this->infoFor);
        }
        $list->end_form();
        $list->render();
    }

    public function tabSearch()
    {
        global $INPUT;
        echo '<div class="alert alert-info">';
        echo $this->locale_xhtml('intro_search');
        echo '</div>';

        echo '<form class="form-horizontal" action="' . $this->tabURL('', array(), '&') . '" method="post">';

        echo '<div class="form-group">';
        echo '<label class="control-label col-sm-2" for="input_q">' . $this->getLang('search_for') . '</label> ';
        echo '<div class="col-sm-10">';
        echo '<div class="input-group">';
        echo '<input id="input_q" class="form-control" type="text" name="q" value="' . $INPUT->str('q') . '" /> ';
        echo '<div class="input-group-btn">';
        echo '<input class="btn btn-primary" type="submit" value="' . $this->getLang('search') . '" />';
        echo '</div>'; // input-group-btn
        echo '</div>'; // input-group
        echo '</div>';
        echo '</div>';

        echo '</form>';

        if (!$INPUT->bool('q'))
        {
            return;
        }

        /* @var helper_plugin_extension_repository $repository FIXME should we use some gloabl instance? */
        $repository = $this->loadHelper('extension_repository');
        $result     = $repository->search($INPUT->str('q'));

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('vikonextension_list');
        $list->start_form();
        if ($result)
        {
            foreach ($result as $name)
            {
                $extension->setExtension($name);
                $list->add_row($extension, $extension->getID() == $this->infoFor);
            }
        }
        else
        {
            $list->nothing_found();
        }
        $list->end_form();
        $list->render();
    }

    /**
     * Display the template tab
     */
    public function tabInstall()
    {
        echo '<div class="alert alert-info">';
        echo $this->locale_xhtml('intro_install');
        echo '</div>';

        echo '<form class="form-horizontal" action="' . $this->tabURL('', array(), '&') . '" enctype="multipart/form-data" method="post">';

        echo '<div class="form-group">';
        echo '<label class="control-label col-sm-2" for="input_url">' . $this->getLang('install_url') . '</label> ';
        echo '<div class="col-sm-10">';
        echo '<input id="input_url" class="form-control" type="text" name="installurl" value="" /> ';
        echo '</div>';
        echo '</div>';

        echo '<div class="form-group">';
        echo '<label class="control-label col-sm-2" for="input_upload">' . $this->getLang('install_upload') . '</label> ';
        echo '<div class="col-sm-10">';
        echo '<input id="input_upload" class="form-control" type="file" name="installfile" value="" /> ';
        echo '</div>';
        echo '</div>';

        echo '<div class="form-group">';
        echo '<div class="col-sm-offset-2 col-sm-10">';
        echo '<input class="btn btn-primary" type="submit" value="' . $this->getLang('btn_install') . '" />';
        echo '</div>';
        echo '</div>';

        echo '</form>';
    }
}