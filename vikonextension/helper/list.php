<?php

if (!defined('DOKU_INC'))
{
    die;
}

class helper_plugin_vikonextension_list extends helper_plugin_extension_list
{
    function start_form()
    {
        $this->form .= '<form id="extension__list" accept-charset="utf-8" method="post" action="">';
        $hidden = array(
            'do'     => 'admin',
            'page'   => 'extension',
            'sectok' => getSecurityToken()
        );
        $this->add_hidden($hidden);
        $this->form .= '<div class="list-group">';
    }

    function add_row(helper_plugin_extension_extension $extension, $showinfo = false)
    {
        $this->form .= '<div id="extensionplugin__' . hsc($extension->getID()) . '" class="list-group-item' . ($extension->isEnabled()
                ? ''
                : ' disabled') . '">';

        $this->form .= $this->make_legend($extension, $showinfo);

        $this->form .= '</div>';
    }

    function end_form()
    {
        $this->form .= '</div>';
        $this->form .= '</form>';
    }

    function make_legend(helper_plugin_extension_extension $extension, $showinfo = false)
    {
        $return = '<div>';
        $return .= '<h4 class="list-group-item-heading">';
        $return .= sprintf($this->getLang('extensionby'), hsc($extension->getDisplayName()), $this->make_author($extension));
        $return .= '</h4>';

        $return .= '<div class="media" style="margin-bottom: 15px;">';
        $return .= '<div class="pull-left">';
        $return .= $this->make_screenshot($extension);
        $return .= '</div>';
        $return .= '<div class="media-body">';
        $return .= '<div class="row">';
        $return .= '<div class="col-sm-7">';

        $popularity = $extension->getPopularity();
        if ($popularity !== false && !$extension->isBundled())
        {
            $popularityText = sprintf($this->getLang('popularity'), round($popularity * 100, 2));
            $return .= '<div class="popularity" title="' . $popularityText . '"><div style="width: ' . ($popularity * 100) . '%;"><span class="a11y">' . $popularityText . '</span></div></div>';
        }

        if ($extension->getDescription())
        {
            $return .= '<p class="text-justify">' . hsc($extension->getDescription()) . '</p>';
        }

        $return .= $this->make_linkbar($extension);

        $return .= $this->make_version($extension);

        $return .= '<div class="detail">';
        if ($showinfo)
        {
            $return .= $this->make_info($extension);
        }
        $return .= '</div>';

        $return .= '</div>';
        $return .= '<div class="col-sm-5 text-right">';

        $return .= '<p>';

        $return .= $this->make_homepagelink($extension);

        if ($extension->getBugtrackerURL())
        {
            $return .= ' <a href="' . hsc($extension->getBugtrackerURL()) . '" title="' . hsc($extension->getBugtrackerURL()) . '" class ="btn btn-sm btn-danger">'
                       . '<span class="glyphicon glyphicon-asterisk"></span> '
                       . $this->getLang('bugs_features') . '</a> ';
        }

        if ($showinfo)
        {
            $url = $this->gui->tabURL('');
        }
        else
        {
            $url = $this->gui->tabURL('', array('info' => $extension->getID()));
        }
        $return .= ' <a href="' . $url . '#extensionplugin__' . $extension->getID() . '" class="btn btn-sm btn-info info" title="' . $this->getLang('btn_info') . '" data-extid="' . $extension->getID() . '">'
                   . '<span class="glyphicon glyphicon-info-sign"></span> '
                   . $this->getLang('btn_info') . '</a>';
        $return .= '</p>';

        $actions = $this->make_actions($extension);

        $return .= '<p>' . $actions['return'] . '</p>';

        $return .= '</div>';
        $return .= '</div>';
        $return .= '</div>'; // row

        $return .= '</div>'; // media-body
        $return .= '</div>'; // media

        $return .= $actions['errors'];
        $return .= $this->make_noticearea($extension);

        return $return;
    }

    function make_linkbar(helper_plugin_extension_extension $extension)
    {
        $return = '';
        if ($extension->getTags())
        {
            $return .= '<p>' . $this->getLang('tags') . ' ';
            foreach ($extension->getTags() as $tag)
            {
                $url = $this->gui->tabURL('search', array('q' => 'tag:' . $tag));
                $return .= '<a class="label label-primary" href="' . $url . '">' . hsc($tag) . '</a> ';
            }
            $return .= '</p>';
        }

        return $return;
    }

    function make_version(helper_plugin_extension_extension $extension)
    {
        $return = '';
        if (!$extension->isInstalled() && $extension->getDownloadURL())
        {
            $return .= $this->getLang('available_version') . ' ';
            $return .= ($extension->getLastUpdate()
                ? hsc($extension->getLastUpdate())
                : $this->getLang('unknown'));
        }

        return $return;
    }

    function make_homepagelink(helper_plugin_extension_extension $extension)
    {
        $text = $this->getLang('homepage_link');
        $url  = hsc($extension->getURL());

        return '<a href="' . $url . '" title="' . $url . '" class ="btn btn-sm btn-default"><span class="glyphicon glyphicon-file"></span> ' . $text . '</a> ';
    }

    function make_actions(helper_plugin_extension_extension $extension)
    {
        $return = '';
        $errors = '';

        if ($extension->isInstalled())
        {
            if (($canmod = $extension->canModify()) === true)
            {
                if (!$extension->isProtected())
                {
                    $return .= $this->make_action('uninstall', $extension);
                }
                if ($extension->getDownloadURL())
                {
                    if ($extension->updateAvailable())
                    {
                        $return .= $this->make_action('update', $extension);
                    }
                    else
                    {
                        $return .= $this->make_action('reinstall', $extension);
                    }
                }
            }
            else
            {
                $errors .= '<p class="alert alert-danger">' . $this->getLang($canmod) . '</p>';
            }

            if (!$extension->isProtected() && !$extension->isTemplate())
            { // no enable/disable for templates
                if ($extension->isEnabled())
                {
                    $return .= $this->make_action('disable', $extension);
                }
                else
                {
                    $return .= $this->make_action('enable', $extension);
                }
            }

            if ($extension->isGitControlled())
            {
                $errors .= '<p class="alert alert-danger">' . $this->getLang('git') . '</p>';
            }
        }
        else
        {
            if (($canmod = $extension->canModify()) === true)
            {
                if ($extension->getDownloadURL())
                {
                    $return .= $this->make_action('install', $extension);
                }
            }
            else
            {
                $errors .= '<div class="alert alert-danger">' . $this->getLang($canmod) . '</div>';
            }
        }

        return array(
            'return' => $return,
            'errors' => $errors,
        );
    }

    function make_action($action, $extension)
    {
        $title = '';
        $class = 'btn-default';

        switch ($action)
        {
            case 'install':
                $class = 'btn-success';
                $title = 'title="' . hsc($extension->getDownloadURL()) . '"';
                break;
            case 'reinstall':
                $class = 'btn-warning';
                $title = 'title="' . hsc($extension->getDownloadURL()) . '"';
                break;
            case 'update':
            case 'enable':
                $class = 'btn-success';
                break;
            case 'disable':
                $class = 'btn-warning';
                break;
            case 'uninstall':
                $class = 'btn-danger';
                break;
        }
        $name = 'fn[' . $action . '][' . hsc($extension->getID()) . ']';

        return '<input class="btn btn-sm ' . $class . ' ' . $action . '" name="' . $name . '" type="submit" value="' . $this->getLang('btn_' . $action) . '" ' . $title . ' /> ';
    }

    function make_noticearea(helper_plugin_extension_extension $extension)
    {
        $return               = '';
        $missing_dependencies = $extension->getMissingDependencies();
        if (!empty($missing_dependencies))
        {
            $return .= '<div class="alert alert-damage">' .
                       sprintf($this->getLang('missing_dependency'), '<bdi>' . implode(', ', /*array_map(array($this->helper, 'make_extensionsearchlink'),*/
                                                                                       $missing_dependencies) . '</bdi>') .
                       '</div>';
        }
        if ($extension->isInWrongFolder())
        {
            $return .= '<div class="alert alert-damage">' .
                       sprintf($this->getLang('wrong_folder'), '<bdi>' . hsc($extension->getInstallName()) . '</bdi>', '<bdi>' . hsc($extension->getBase()) . '</bdi>') .
                       '</div>';
        }
        if (($securityissue = $extension->getSecurityIssue()) !== false)
        {
            $return .= '<div class="alert alert-damage">' .
                       sprintf($this->getLang('security_issue'), '<bdi>' . hsc($securityissue) . '</bdi>') .
                       '</div>';
        }
        if (($securitywarning = $extension->getSecurityWarning()) !== false)
        {
            $return .= '<div class="alert alert-warning">' .
                       sprintf($this->getLang('security_warning'), '<bdi>' . hsc($securitywarning) . '</bdi>') .
                       '</div>';
        }
        if ($extension->updateAvailable())
        {
            $return .= '<div class="alert alert-warning">' .
                       sprintf($this->getLang('update_available'), hsc($extension->getLastUpdate())) .
                       '</div>';
        }
        if ($extension->hasDownloadURLChanged())
        {
            $return .= '<div class="alert alert-warning">' .
                       sprintf($this->getLang('url_change'), '<bdi>' . hsc($extension->getDownloadURL()) . '</bdi>', '<bdi>' . hsc($extension->getLastDownloadURL()) . '</bdi>') .
                       '</div>';
        }

        return $return;
    }

    function make_info(helper_plugin_extension_extension $extension)
    {
        $default = $this->getLang('unknown');
        $return  = '<hr/>';
        $return .= '<dl class="dl-horizontal">';

        $return .= '<dt>' . $this->getLang('status') . '</dt>';
        $return .= '<dd>' . $this->make_status($extension) . '</dd>';

        if ($extension->getDonationURL())
        {
            $return .= '<dt>' . $this->getLang('donate') . '</dt>';
            $return .= '<dd>';
            $return .= '<a href="' . $extension->getDonationURL() . '" class="donate">' . $this->getLang('donate_action') . '</a>';
            $return .= '</dd>';
        }

        if (!$extension->isBundled())
        {
            $return .= '<dt>' . $this->getLang('downloadurl') . '</dt>';
            $return .= '<dd><bdi>';
            $return .= ($extension->getDownloadURL()
                ? $this->shortlink($extension->getDownloadURL())
                : $default);
            $return .= '</bdi></dd>';

            $return .= '<dt>' . $this->getLang('repository') . '</dt>';
            $return .= '<dd><bdi>';
            $return .= ($extension->getSourcerepoURL()
                ? $this->shortlink($extension->getSourcerepoURL())
                : $default);
            $return .= '</bdi></dd>';
        }

        if ($extension->isInstalled())
        {
            if ($extension->getInstalledVersion())
            {
                $return .= '<dt>' . $this->getLang('installed_version') . '</dt>';
                $return .= '<dd>';
                $return .= hsc($extension->getInstalledVersion());
                $return .= '</dd>';
            }
            else
            {
                $return .= '<dt>' . $this->getLang('install_date') . '</dt>';
                $return .= '<dd>';
                $return .= ($extension->getUpdateDate()
                    ? hsc($extension->getUpdateDate())
                    : $this->getLang('unknown'));
                $return .= '</dd>';
            }
        }
        if (!$extension->isInstalled() || $extension->updateAvailable())
        {
            $return .= '<dt>' . $this->getLang('available_version') . '</dt>';
            $return .= '<dd>';
            $return .= ($extension->getLastUpdate()
                ? hsc($extension->getLastUpdate())
                : $this->getLang('unknown'));
            $return .= '</dd>';
        }

        if ($extension->getInstallDate())
        {
            $return .= '<dt>' . $this->getLang('installed') . '</dt>';
            $return .= '<dd>';
            $return .= hsc($extension->getInstallDate());
            $return .= '</dd>';
        }

        $return .= '<dt>' . $this->getLang('provides') . '</dt>';
        $return .= '<dd><bdi>';
        $return .= ($extension->getTypes()
            ? hsc(implode(', ', $extension->getTypes()))
            : $default);
        $return .= '</bdi></dd>';

        if (!$extension->isBundled() && $extension->getCompatibleVersions())
        {
            $return .= '<dt>' . $this->getLang('compatible') . '</dt>';
            $return .= '<dd>';
            foreach ($extension->getCompatibleVersions() as $date => $version)
            {
                $return .= '<bdi>' . $version['label'] . ' (' . $date . ')</bdi>, ';
            }
            $return = rtrim($return, ', ');
            $return .= '</dd>';
        }
        if ($extension->getDependencies())
        {
            $return .= '<dt>' . $this->getLang('depends') . '</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getDependencies());
            $return .= '</dd>';
        }

        if ($extension->getSimilarExtensions())
        {
            $return .= '<dt>' . $this->getLang('similar') . '</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getSimilarExtensions());
            $return .= '</dd>';
        }

        if ($extension->getConflicts())
        {
            $return .= '<dt>' . $this->getLang('conflicts') . '</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getConflicts());
            $return .= '</dd>';
        }
        $return .= '</dl>' . DOKU_LF;

        return $return;
    }

    function nothing_found()
    {
        global $lang;
        $this->form .= '<div class="alert alert-info">' . $lang['nothingfound'] . '</div>';
    }
}