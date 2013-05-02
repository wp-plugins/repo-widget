<?php
/*
Plugin Name: Repo Widget
Plugin URI: http://wordpress.org/extend/plugins/repo-widget
Description: Allows you to insert general GitHub repo information with a short code
Version: 0.7.0
Author: Vladimir Jimenez
Author URI: http://allejo.me/
License: GPL2

Copyright 2013 Vladimir Jimenez (allejo@me.com)
*/

function get_json($url)
{
    $curl_handler = curl_init();
    curl_setopt($curl_handler, CURLOPT_URL, $url);
    curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handler, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($curl_handler, CURLOPT_USERAGENT, 'curl/' . $t_vers['version']);

    $json = curl_exec($curl_handler);

    curl_close($curl_handler);

    return json_decode($json, true);
}

function repo_tag($atts)
{
	wp_register_style('repo-widget-css', plugins_url('style.css',__FILE__ ));
	wp_enqueue_style('repo-widget-css');
	wp_register_script('repo-widget-js', plugins_url('repo-widget.js',__FILE__ ),array('jquery'));
	wp_enqueue_script('repo-widget-js');

	extract( shortcode_atts( array(
		'user' => 'octocat',
		'repo' => 'Hello-World'
	), $atts ) );

	$transient = "rw_" . $user . "_" . $repo;
	$status = get_transient($transient);

	if ($status == true)
	{
		return $status;
	}
	else if (!empty($user) && !empty($repo))
	{
		$repo_location = $user . "/" . $repo; //GitHub api -> user/repo

	    $repo_data = get_json("https://api.github.com/repos/" . $repo_location);
	    $commit_sha = get_json("https://api.github.com/repos/" . $repo_location . "/git/refs/heads/master");
	    $commit = get_json("https://api.github.com/repos/" . $repo_location . "/git/commits/" . $commit_sha['object']['sha']);
	    $date = new DateTime($commit['author']['date']);

	    if (count((array)$repo_data) == 1)
	    {
	    	$error_message = "<div class=\"repo_widget\">
		    <div>
		        <h1 class=\"rw_title\"><a>Message from GitHub</a></h1>
		    </div>
		    <div class=\"rw_description\">
		    	<br>
		        <p class=\"rw_p\">" . $commit['message'] . "</p>
		    </div>
		</div>";
	    	return $error_message;
	    }

	    $date = new DateTime($commit['author']['date']);
		$my_repo_widget = "
		<div class=\"repo_widget\">
		    <div>
		        <h1 class=\"rw_title\"><a href=\"" . $repo_data['html_url'] . "\" target=\"_blank\">" . $repo_data['name'] . "</a></h1>
		    </div>
		    <div class=\"rw_link_container\">
		        <div class=\"rw_links\">
		            <span class=\"rw_active rw_first rw_button\" rel=\"" . $repo_data['clone_url'] . "\">HTTP</span>
		            <span class=\"rw_button\" rel=\"" . $repo_data['git_url'] . "\">SSH</span>
		            <span class=\"rw_button\" rel=\"" . $repo_data['ssh_url'] . "\">GIT Read-Only</span>
		            <input class=\"rw_input\" value=\"" . $repo_data['clone_url'] . "\" readonly onclick=\"this.select()\">
		        </div>
		    </div>
		    <div class=\"rw_description\">
		        <p class=\"rw_p\">" . $repo_data['description'] . "</p>
		    </div>
		    <div class=\"rw_commit\">
		        <span class=\"rw_data\">
		            latest commit <a class=\"rw_hash\" href=\"" . $commit['html_url'] . "\">" . substr($commit['sha'], 0, 10) . "</a>
		            <span class=\"rw_timestamp\">" . $date->format("D M j G:i:s Y") . "</span>
		        </span>
		        <p class=\"rw_message\">" . $commit['message'] . " by <strong>" . $commit['author']['name'] . "</strong></p>
		    </div>
		</div>";

		set_transient($transient, $my_repo_widget, 90);
		return $my_repo_widget;
	}
	else
	{
		return false;
	}
}

add_shortcode( 'repo', 'repo_tag' );
add_action( 'wp_enqueue_scripts', 'repo_tag' );

?>
