<?php
/**
 * Plugin Name: Post to Twitter
 * Description: Automatically posts WordPress posts to Twitter when published.
 * Version: 1.0
 * Author: Durgesh Chander
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
										
require_once __DIR__ . '/includes/ca-bundle/src/CaBundle.php';
require_once __DIR__ . '/includes/abraham/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

class Post_To_Twitter {

    private $options;

    public function __construct() {
							   
        $this->options = get_option('post_to_twitter_settings');

        add_action('save_post', [$this, 'schedule_post_to_twitter'], 10, 3);
        add_action('post_to_twitter', [$this, 'post_to_twitter_event'], 10, 1);

								 
        add_action('admin_menu', [$this, 'add_settings_page']);

							
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function schedule_post_to_twitter($post_id, $post, $update) {
        if ($post->post_status != 'publish' || wp_is_post_revision($post_id)) {
            return;
        }

        if (!wp_next_scheduled('post_to_twitter', [$post_id])) {
            wp_schedule_single_event(time() + 3, 'post_to_twitter', [$post_id]);
        }
    }

    public function post_to_twitter_event($post_id) {
        $post = get_post($post_id);
        $this->post_to_twitter($post_id, $post, false);
    }

    public function post_to_twitter($ID, $post, $update) {
        if ($post->post_status !== 'publish' || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return;
        }

        if (function_exists('get_field') && !get_field('post_to_twitter', $ID)) {
            return;
        }

																					   
        $tweeted_meta_key = '_post_tweeted';
        $tweeted = get_post_meta($ID, $tweeted_meta_key, true);


																							 
        if ($update && $tweeted) {
            return;
        }

									
        $connection = new TwitterOAuth(
            $this->options['consumer_key'],
            $this->options['consumer_secret'],
            $this->options['access_token'],
            $this->options['access_token_secret']
        );

							  
        $title = $post->post_title; //get post title
        $url = get_permalink($ID);
        $excerpt = get_the_excerpt($ID); //get post content
        if (empty($excerpt)) {
            $content = strip_tags($post->post_content);
            $excerpt = wp_trim_words($content, 35, '...');
        } else {
            $excerpt = wp_trim_words($excerpt, 35, '');
        }

        $tweet_content = $title . "\n\n\n" . $excerpt . "\n" . $url;
        if (strlen($tweet_content) > 278) {
            $max_excerpt_length = 278 - strlen($url) - strlen($title) - 4;
            $excerpt = ($max_excerpt_length > 0) ? substr($excerpt, 0, $max_excerpt_length) . '...' : '';
            $message = $title . "\n\n\n" . $excerpt . "\n" . $url;
        } else {
            $message = $tweet_content;
        }

   
  
        $message_convert = mb_detect_encoding($message, 'UTF-8', true)
   
            ? $message
            : mb_convert_encoding($message, 'UTF-8');
	  
   
							 
   
  

        // Upload image to Twitter using API v1.1
        $connection->setApiVersion(1.1);
        $media_ids = [];
        $image_id = get_post_thumbnail_id($ID);
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);

														 
            $temp_file = download_url($image_url);
            if (!is_wp_error($temp_file)) {
											  
                $media = $connection->upload('media/upload', ['media' => $temp_file]);
                if (isset($media->media_id_string)) {
                    $media_ids[] = $media->media_id_string;
                }
                @unlink($temp_file);
            }
        }

        // Post tweet using Twitter API v2
        $connection->setApiVersion(2);
        $parameters = ['text' => $message_convert];
        if (!empty($media_ids)) {
            $parameters['media'] = ['media_ids' => $media_ids];
        }

        $response = $connection->post('tweets', $parameters, ['jsonPayload' => true]);
							
        if ($connection->getLastHttpCode() != 200) {
            error_log('Error posting to Twitter: ' . print_r($response, true));
        } else {
            update_post_meta($ID, $tweeted_meta_key, true);
        }
												  
    }

 
    public function add_settings_page() {
        add_options_page(
            'Post to Twitter Settings',
            'Post to Twitter',
            'manage_options',
            'post-to-twitter-settings',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings() {
        register_setting('post_to_twitter_settings', 'post_to_twitter_settings');

        add_settings_section(
            'twitter_api_section',
            'Twitter API Settings',
            null,
            'post-to-twitter-settings'
        );

        $fields = [
						   
            'consumer_key' => 'Consumer Key',
            'consumer_secret' => 'Consumer Secret',
            'access_token' => 'Access Token',
            'access_token_secret' => 'Access Token Secret'
        ];
		  

        foreach ($fields as $name => $label) {
			add_settings_field(
                $name,
                $label,
                [$this, 'render_input_field'],
                'post-to-twitter-settings',
                'twitter_api_section',
                ['label_for' => $name, 'name' => $name]
            );
        }
						   
						  
																				   
		  
    }

    public function render_input_field($args) {
        $name = $args['name'];
        $value = isset($this->options[$name]) ? esc_attr($this->options[$name]) : '';
        echo "<input type='text' id='{$name}' name='post_to_twitter_settings[{$name}]' value='{$value}' class='regular-text'>";
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Post to Twitter Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('post_to_twitter_settings');
                do_settings_sections('post-to-twitter-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
 
 
}

// Initialize plugin
new Post_To_Twitter();
