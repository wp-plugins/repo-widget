<?php
/*
Plugin Name: Repo Widget
Plugin URI: http://wordpress.org/extend/plugins/repo-widget
Description: Allows you to insert general GitHub repo information with a short code
Version: 0.8.0
Author: Vladimir Jimenez
Author URI: http://allejo.me/
License: GPL2

Copyright 2013 Vladimir Jimenez (allejo@me.com)
*/

function repo_widget_handler($attributes)
{
    wp_register_style('repo-widget-css', plugins_url('style.css', __FILE__ ));
    wp_enqueue_style('repo-widget-css');
    wp_register_script('repo-widget-js', plugins_url('repo-widget.js', __FILE__ ), array('jquery'));
    wp_enqueue_script('repo-widget-js');

    $repo_widget = repo_widget_builder($attributes);

    return $repo_widget;
}

function repo_widget_builder($attributes)
{
    $widget = "";

    extract(shortcode_atts(array(
        'host' => 'github',
        'user' => 'octocat',
        'repo' => 'Hello-World',
        'size' => 'normal',
        'commits' => '1',
        'theme' => 'github',
        'travis' => ''
    ), $attributes));

    if ($theme == "github")
    {
        if ($size == "normal")
        {
            $data = repo_widget_json("github", array('user' => $user, 'repo' => $repo));

            $widget =
                sprintf('<div class="repo-widget github normal">') .
                sprintf('    <div class="header">') .
                sprintf('        <a href="%s">%s</a>', $data['url'], $data['name']);

            if ($travis != '')
            {
                $widget .= sprintf('        <img src="%s">', $travis);
            }

            $widget .=
                sprintf('    </div>') .
                sprintf('    <div class="information">') .
                sprintf('        <div class="links">') .
                sprintf('            <div class="active" rel="%s">HTTP</div>', $data['clone_url']) .
                sprintf('            <div rel="%s">GIT</div>', $data['git_url']) .
                sprintf('            <div rel="%s">SSH</div>', $data['ssh_url']) .
                sprintf('            <input value="%s" readonly onclick="this.select()">', $data['clone_url']) .
                sprintf('        </div>') .
                sprintf('        <div class="description">') .
                sprintf('            <p>%s</p>', $data['description']) .
                sprintf('        </div>') .
                sprintf('    </div>') .
                sprintf('    <ul class="commits">') .
                sprintf('        <li>') .
                sprintf('            <div class="info">') .
                sprintf('                <p class="commit">latest commit <a href="">%s</a></p>', $data['last_commit']['hash']) .
                sprintf('                <p class="timestamp">%s</p>', $data['last_commit']['date']) .
                sprintf('                <div style="clear:both"></div>') .
                sprintf('            </div>') .
                sprintf('            <p class="message">%s by <em>%s</em></p>', $data['last_commit']['message'], $data['last_commit']['author']) .
                sprintf('        </li>') .
                sprintf('    </ul>') .
                sprintf('</div>');
            }
    }

    return $widget;
}

function repo_widget_json($host, $array)
{
    $transient = "repo-widget_" . $array['user'] . "-" . $array['repo'];
	$status = get_transient($transient);

    if ($status)
    {
        return $status;
    }

    if ($host == "github")
    {
        $repo_location = $array['user'] . '/' . $array['repo'];

        $repo_data  = json_decode(file_get_contents("https://api.github.com/repos/" . $repo_location), TRUE);
        $commit_sha = json_decode(file_get_contents("https://api.github.com/repos/" . $repo_location . "/git/refs/heads/master"), TRUE);
        $commit     = json_decode(file_get_contents("https://api.github.com/repos/" . $repo_location . "/git/commits/" . $commit_sha['object']['sha']), TRUE);
        $date       = new DateTime($commit['author']['date']);

        $repo_information = array();
        $repo_information['name'] = $repo_data['name'];
        $repo_information['url'] = $repo_data['html_url'];
        $repo_information['clone_url'] = $repo_data['clone_url'];
        $repo_information['git_url'] = $repo_data['git_url'];
        $repo_information['ssh_url'] = $repo_data['ssh_url'];
        $repo_information['description'] = $repo_data['description'];
        $repo_information['last_commit']['hash'] = substr($commit_sha['object']['sha'], 0, 10);
        $repo_information['last_commit']['url'] = $commit['html_url'];
        $repo_information['last_commit']['date'] = $date->format("D M j G:i:s Y");
        $repo_information['last_commit']['message'] = $commit['message'];
        $repo_information['last_commit']['author'] = $commit['author']['name'];

        set_transient($transient, $repo_information, 90);
        return $repo_information;
    }
}

add_shortcode('repo', 'repo_widget_handler');